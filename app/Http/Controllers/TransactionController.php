<?php

namespace App\Http\Controllers;

use App\Http\Requests\InitiatePaymentRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('FLW_BASE_URL', 'https://api.flutterwave.com/v3');
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer '.env('FLW_SECRET_KEY'),
            'Content-Type' => 'application/json',
        ];
    }

    public function initiatePayment(InitiatePaymentRequest $request)
    {
        try {
            $user = User::with('member')->find($request->input('userId'));

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your record was not found.',
                ], 404);
            }

            if (! $user->member) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a member.',
                ], 400);
            }

            if (! $user->member->parishId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not beling to a parish.',
                ], 400);
            }

            $reference = 'TXN-'.Str::uuid();

            $response = Http::withHeaders($this->headers())
                ->post("{$this->baseUrl}/payments", [
                    'tx_ref' => $reference,
                    'amount' => $request->input('amount'),
                    'currency' => $request->input('currency', 'NGN'),
                    'redirect_url' => $request->input('callBackUrl'),
                    'payment_options' => 'card,banktransfer,ussd',
                    'customer' => [
                        'email' => $user->email,
                        'name' => $user->name,
                    ],
                    'customizations' => [
                        'title' => config('app.name'),
                    ],
                ]);

            $data = $response->json();

            if ($data['status'] !== 'success') {
                return response()->json([
                    'success' => false,
                    'message' => $data['message'] ?? 'Could not initiate payment.',
                ], 400);
            }

            Transaction::create([
                'user_id' => $user->id,
                'amount' => $request->input('amount'),
                'status' => 'pending',
                'payment_method' => 'flutterwave',
                'reference' => $reference,
                'currency' => $request->input('currency', 'NGN'),
            ]);

            return response()->json([
                'success' => true,
                'authorizationUrl' => $data['data']['link'],
                'reference' => $reference,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function flutterwaveWebhook(Request $request)
    {
        $secretHash = env('FLW_SECRET_HASH');
        $signature = $request->header('verif-hash');

        if (! $signature || $signature !== $secretHash) {
            // return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        try {
            $event = $request->input('event');
            $data = $request->input('data');

            if (
                $event === 'charge.completed' &&
                isset($data['status']) &&
                $data['status'] === 'successful'
            ) {
                $reference = $data['tx_ref'];

                // Find existing transaction
                $transaction = Transaction::where('reference', $reference)->first();

                if (! $transaction) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Transaction not found.',
                    ], 404);
                }

                // Prevent duplicate processing
                if ($transaction->status === 'success') {
                    Log::info('Flutterwave webhook — transaction already processed, skipping', [
                        'reference' => $reference,
                        'transaction_id' => $transaction->id,
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Transaction already processed.',
                    ]);
                }

                $verification = Http::withHeaders($this->headers())
                    ->get("{$this->baseUrl}/transactions/{$data['id']}/verify");

                $verified = $verification->json();

                if (
                    isset($verified['data']) &&
                    $verified['data']['status'] === 'successful' &&
                    $verified['data']['tx_ref'] === $reference
                ) {
                    $transaction->update([
                        'status' => 'success',
                        'amount' => $verified['data']['amount'],
                        'currency' => $verified['data']['currency'],
                        'payment_method' => 'flutterwave',
                    ]);
                } else {
                    Log::warning('Flutterwave verification failed or data mismatch', [
                        'reference' => $reference,
                        'verified_status' => $verified['data']['status'] ?? null,
                        'verified_tx_ref' => $verified['data']['tx_ref'] ?? null,
                        'expected_tx_ref' => $reference,
                        'verification_data' => $verified['data'] ?? null,
                    ]);
                }
            }

            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            Log::error('Flutterwave webhook error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ──────────────────────────────────────────
    // GET /api/admin/transactions
    // ──────────────────────────────────────────
    public function getAllTransactions(Request $request)
    {
        try {
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('perPage', 25);
            $search = $request->query('search');
            $sort = $request->query('sort', 'created_at:desc');
            $status = $request->query('status');

            [$sortColumn, $sortDirection] = array_pad(explode(':', $sort), 2, 'desc');

            $query = Transaction::query()->with('user');

            if ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                });
            }

            if ($status) {
                $query->where('status', $status);
            }

            $total = $query->count();
            $transactions = $query->orderBy($sortColumn, $sortDirection)
                ->paginate($perPage, ['*'], 'page', $page);

            if ($transactions->isEmpty()) {
                return response()->json(['success' => true, 'data' => []]);
            }

            return response()->json([
                'success' => true,
                'data' => TransactionResource::collection($transactions->items()),
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'perPage' => $perPage,
                    'totalPages' => $transactions->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
