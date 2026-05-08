<?php

namespace App\Services;

use App\Enums\DueStatus;
use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Models\MonthlyDue;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class FlutterwavePaymentService
{
    private string $baseUrl;

    private array $headers;

    public function __construct()
    {
        $this->baseUrl = config('services.flutterwave.base_url');
        $this->headers = [
            'Authorization' => 'Bearer '.config('services.flutterwave.secret_key'),
            'Content-Type' => 'application/json',
        ];
    }

    public function initiatePayment(User $user, array $validated): array
    {
        // ── Guards ───────────────────────────────────────────────────────────
        if (! $user->member) {
            return $this->error('You are not a member.', 403);
        }

        $areaId = $user->member->areaId;

        if (! $areaId) {
            return $this->error('You do not belong to an area.', 400);
        }

        // ── Resolve the MonthlyDue ───────────────────────────────────────────
        $due = MonthlyDue::find($validated['due_id']);

        if ($due->area_id !== $areaId) {
            Log::warning('initiatePayment: due area_id mismatch', [
                'user_id' => $user->id,
                'due_id' => $due->id,
                'due_area_id' => $due->area_id,
                'user_area' => $areaId,
            ]);

            return $this->error('This due does not belong to your area.', 403);
        }

        if ($due->status === DueStatus::PAID) {
            return $this->error('This period has already been fully paid.', 409);
        }

        // ── Guard: installment must not exceed remaining balance ─────────────
        $remaining = $due->due_amount - $due->paid_amount;

        if ($validated['amount'] > $remaining) {
            return $this->error(
                "Amount exceeds remaining balance of {$due->currency} {$remaining}.",
                400
            );
        }

        // ── Call Flutterwave ─────────────────────────────────────────────────
        $reference = 'TXN-'.Str::uuid();
        $currency = $validated['currency'] ?? 'NGN';

        $response = Http::withHeaders($this->headers)
            ->timeout(15)
            ->retry(2, 500, throw: false)
            ->post("{$this->baseUrl}/payments", [
                'tx_ref' => $reference,
                'amount' => $validated['amount'],
                'currency' => $currency,
                'redirect_url' => config('services.flutterwave.callback_url'),
                'payment_options' => config('services.flutterwave.payment_options'),
                'customer' => [
                    'email' => $user->email,
                    'name' => $user->name,
                ],
                'customizations' => [
                    'title' => config('app.name'),
                ],
            ]);
        if ($response->failed()) {
            return $this->error('Could not reach payment provider. Please try again.', 502);
        }

        $data = $response->json();

        if (($data['status'] ?? '') !== 'success') {
            return $this->error($data['message'] ?? 'Could not initiate payment.', 400);
        }

        // ── Persist atomically ───────────────────────────────────────────────
        try {
            DB::transaction(function () use ($user, $validated, $reference, $currency, $due, &$trans) {
                $trans = Transaction::create([
                    'user_id' => $user->id,
                    'amount' => $validated['amount'],
                    'status' => TransactionStatus::PENDING->value,
                    'payment_method' => PaymentMethod::FLUTTERWAVE->value,
                    'reference' => $reference,
                    'currency' => $currency,
                ]);

                Payment::create([
                    'transaction_id' => $trans->id,
                    'monthly_due_id' => $due->id,
                    'area_id' => $due->area_id,
                    'created_by_id' => $user->id,
                    'amount' => $validated['amount'],
                    'month' => $due->month,
                    'year' => $due->year,
                    'status' => TransactionStatus::PENDING->value,
                ]);
            });
        } catch (Throwable $e) {
            Log::error('initiatePayment: DB transaction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('Payment could not be recorded. Please try again.', 500);
        }

        return [
            'success' => true,
            'status_code' => 200,
            'data' => [
                'authorizationUrl' => $data['data']['link'],
                'reference' => $reference,
                'remaining' => $remaining - $validated['amount'], // optimistic
            ],
        ];
    }

    public function handleWebhook(Request $request): array
    {
        // ── 1. Signature verification ────────────────────────────────────────
        $secretHash = config('services.flutterwave.secret_hash');
        $signature = $request->header('verif-hash');

        if (! $signature || $signature !== $secretHash) {

            // return ['success' => false, 'status_code' => 401, 'message' => 'Unauthorized.'];
        }

        // ── 2. Parse event ───────────────────────────────────────────────────
        $event = $request->input('event');
        $data = $request->input('data');

        if ($event !== 'charge.completed' || ($data['status'] ?? '') !== 'successful') {
            return ['success' => true, 'status_code' => 200];
        }

        // ── 3. Look up transaction ───────────────────────────────────────────
        $reference = $data['tx_ref'];
        $transaction = Transaction::where('reference', $reference)->first();

        if (! $transaction) {
            return ['success' => true, 'status_code' => 200]; // 200 to stop Flutterwave retrying
        }

        // ── 4. Idempotency guard ─────────────────────────────────────────────
        if ($transaction->status === TransactionStatus::SUCCESS->value) {
            return ['success' => true, 'status_code' => 200];
        }

        // ── 5. Verify with Flutterwave ───────────────────────────────────────
        $verification = Http::withHeaders($this->headers)
            ->timeout(15)
            ->retry(2, 500, throw: false)
            ->get("{$this->baseUrl}/transactions/{$data['id']}/verify");

        $verified = $verification->json();
        $verifiedData = $verified['data'] ?? null;

        if (
            ! $verifiedData ||
            ($verifiedData['status'] ?? '') !== 'successful' ||
            ($verifiedData['tx_ref'] ?? '') !== $reference
        ) {
            return ['success' => false, 'status_code' => 200, 'message' => 'Verification failed.'];
        }

        // ── 6. Persist + recalculate due ─────────────────────────────────────
        try {
            DB::transaction(function () use ($transaction, $verifiedData, $reference) {
                $transaction->update([
                    'status' => TransactionStatus::SUCCESS->value,
                    'amount' => $verifiedData['amount'],
                    'currency' => $verifiedData['currency'],
                    'payment_method' => PaymentMethod::FLUTTERWAVE->value,
                ]);

                $payment = Payment::where('transaction_id', $transaction->id)->first();

                if ($payment) {
                    $payment->update(['status' => TransactionStatus::SUCCESS->value]);

                    $due = MonthlyDue::find($payment->monthly_due_id);

                    if ($due) {
                        $paid = Payment::where('monthly_due_id', $due->id)
                            ->where('status', TransactionStatus::SUCCESS->value)
                            ->sum('amount');

                        $due->update([
                            'paid_amount' => $paid,
                            'status' => match (true) {
                                $paid <= 0 => DueStatus::UNPAID->value,
                                $paid >= $due->due_amount => DueStatus::PAID->value,
                                default => DueStatus::PARTIAL->value,
                            },
                        ]);
                    }
                }
            });
        } catch (Throwable $e) {
            Log::error('Flutterwave webhook: DB update failed', [
                'reference' => $reference,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ['success' => false, 'status_code' => 500, 'message' => 'Internal error. Will retry.'];
        }

        return ['success' => true, 'status_code' => 200];
    }

    private function error(string $message, int $statusCode): array
    {
        return ['success' => false, 'status_code' => $statusCode, 'message' => $message];
    }
}
