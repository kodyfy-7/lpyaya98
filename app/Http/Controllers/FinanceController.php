<?php

namespace App\Http\Controllers;

use App\Http\Resources\FinanceSummaryResource;
use App\Http\Resources\MonthlyDueResource;
use App\Models\MonthlyDue;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FinanceController extends Controller
{
    /**
     * GET /finance/summary
     *
     * Returns the 3 summary cards:
     * - Outstanding Balance   (sum of due_amount - paid_amount across all months YTD)
     * - Total Months Owed     (count of months with status unpaid or partial)
     * - Last Payment          (most recent successful payment amount + date)
     */
    public function summary(Request $request)
    {
        try {
            $user = $request->user()->load('member.area');

            if (! $user->member) {
                return response()->json(['success' => false, 'message' => 'You are not a member.'], 403);
            }

            $areaId = $user->member->areaId;

            if (! $areaId) {
                return response()->json(['success' => false, 'message' => 'You do not belong to an area.'], 400);
            }

            $year = now()->year;

            Log::info('Finance summary requested', [
                'user_id' => $user->id,
                'area_id' => $areaId,
                'year'    => $year,
            ]);

            // Outstanding balance — sum of remaining across all dues YTD
            $dues = MonthlyDue::where('area_id', $areaId)
                ->where('year', $year)
                ->get(['id', 'due_amount', 'paid_amount', 'status']);

            $outstandingBalance = $dues->sum(fn($due) => max(0, $due->due_amount - $due->paid_amount));

            $totalMonthsOwed = $dues->whereIn('status', ['unpaid', 'partial'])->count();

            // Last successful payment for this area
            $lastPayment = Payment::where('area_id', $areaId)
                ->where('status', 'success')
                ->orderByDesc('created_at')
                ->first(['amount', 'created_at']);

            return response()->json([
                'success' => true,
                'data'    => new FinanceSummaryResource([
                    'outstanding_balance' => $outstandingBalance,
                    'total_months_owed'   => $totalMonthsOwed,
                    'last_payment_amount' => $lastPayment?->amount,
                    'last_payment_date'   => $lastPayment?->created_at,
                    'year'                => $year,
                    'area_name'           => $user->member->area->name ?? null,
                ]),
            ]);
        } catch (\Throwable $e) {
            Log::error('Finance summary error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    /**
     * GET /finance/dues?year=2026
     *
     * Returns all monthly dues for the user's area for the given year,
     * each with their nested payments — grouped by month for the accordion.
     */
    public function dues(Request $request)
    {
        try {
            $request->validate([
                'year' => 'nullable|integer|min:2000|max:2100',
            ]);

            $user = $request->user()->load('member');

            if (! $user->member) {
                return response()->json(['success' => false, 'message' => 'You are not a member.'], 403);
            }

            $areaId = $user->member->areaId;

            if (! $areaId) {
                return response()->json(['success' => false, 'message' => 'You do not belong to an area.'], 400);
            }

            $year = (int) $request->query('year', now()->year);

            Log::info('Finance dues requested', [
                'user_id' => $user->id,
                'area_id' => $areaId,
                'year'    => $year,
            ]);

            $dues = MonthlyDue::where('area_id', $areaId)
                ->where('year', $year)
                ->with([
                    'payments' => fn($q) => $q
                        ->orderByDesc('created_at')
                        ->with([
                            'transaction:id,payment_method',
                            'createdBy:id,name',
                        ]),
                ])
                ->orderByDesc('month')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => MonthlyDueResource::collection($dues),
                'meta'    => [
                    'year'  => $year,
                    'total' => $dues->count(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Finance dues error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }
}