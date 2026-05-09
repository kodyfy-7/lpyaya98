<?php

namespace App\Http\Controllers;

use App\Http\Requests\InitiatePaymentRequest;
use App\Http\Resources\PaymentHistoryResource;
use App\Models\Area;
use App\Models\MonthlyDue;
use App\Services\FlutterwavePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private readonly FlutterwavePaymentService $paymentService
    ) {}

    public function initiatePayment(InitiatePaymentRequest $request)
    {
        $user = $request->user()->load('member'); // always from auth, never from input

        Log::info('initiatePayment: request received', [
            'user_id' => $user->id,
            'month' => $request->input('month'),
            'year' => $request->input('year'),
            'amount' => $request->input('amount'),
        ]);

        $result = $this->paymentService->initiatePayment($user, $request->validated());

        $statusCode = $result['status_code'];
        unset($result['status_code']); // don't expose internal field in JSON

        return response()->json($result, $statusCode);
    }

    public function flutterwaveWebhook(Request $request)
    {
        $result = $this->paymentService->handleWebhook($request);
        $statusCode = $result['status_code'];
        unset($result['status_code']);

        return response()->json($result, $statusCode);
    }

    public function summary(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $year         = (int) $request->input('year');
        $currentMonth = now()->month;

        // Total outstanding YTD — sum of (due_amount - paid_amount) for all unpaid/partial dues
        $totalOutstandingYTD = MonthlyDue::where('year', $year)
            ->whereIn('status', ['unpaid', 'partial'])
            ->sum(DB::raw('due_amount - paid_amount'));

        // Total payments made YTD — sum of all paid_amount across all dues
        $totalPaymentYTD = MonthlyDue::where('year', $year)
            ->sum('paid_amount');

        // Outstanding for current month only
        $outstandingCurrentMonth = MonthlyDue::where('year', $year)
            ->where('month', $currentMonth)
            ->whereIn('status', ['unpaid', 'partial'])
            ->sum(DB::raw('due_amount - paid_amount'));

        return response()->json([
            'success' => true,
            'data'    => [
                'year'                      => $year,
                'current_month'             => $currentMonth,
                'total_outstanding_ytd'     => (float) $totalOutstandingYTD,
                'total_payment_ytd'         => (float) $totalPaymentYTD,
                'outstanding_current_month' => (float) $outstandingCurrentMonth,
            ],
        ]);
    }

    public function history(Request $request)
    {
        try {
            $year         = (int) $request->query('year', now()->year);
            $currentMonth = now()->month;
            $perPage      = (int) $request->query('perPage', 5);
            $page         = (int) $request->query('page', 1);
            $search       = $request->query('search');
            $zoneId       = $request->query('zoneId');

            $request->validate([
                'year'    => 'required|integer|min:2000|max:2100',
                'zoneId'  => 'nullable|uuid|exists:zones,id',
                'search'  => 'nullable|string|max:100',
                'perPage' => 'nullable|integer|between:5,100',
            ]);

            $query = Area::query()
                ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
                ->when($zoneId, fn($q) => $q->where('zone_id', $zoneId))
                ->select('areas.id', 'areas.name')
                ->withSum(
                    ['monthlyDues as total_paid_ytd' => fn($q) => $q->where('year', $year)],
                    'paid_amount'
                )
                ->withSum(
                    ['monthlyDues as total_due_ytd' => fn($q) => $q->where('year', $year)],
                    'due_amount'
                )
                ->withSum(
                    [
                        'monthlyDues as current_month_paid' => fn($q) => $q
                            ->where('year', $year)
                            ->where('month', $currentMonth),
                    ],
                    'paid_amount'
                )
                ->withAvg(
                    ['monthlyDues as monthly_expected_dues' => fn($q) => $q->where('year', $year)],
                    'due_amount'
                );

            $total = $query->count();
            $areas = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data'    => PaymentHistoryResource::collection($areas->items()),
                'meta'    => [
                    'total'      => $total,
                    'page'       => $page,
                    'perPage'    => $perPage,
                    'totalPages' => $areas->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('PaymentHistory history error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
