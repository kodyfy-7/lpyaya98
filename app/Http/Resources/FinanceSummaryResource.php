<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'outstandingBalance' => (float) $this->resource['outstanding_balance'],
            'totalMonthsOwed'    => (int)   $this->resource['total_months_owed'],
            'lastPayment'        => [
                'amount' => (float) ($this->resource['last_payment_amount'] ?? 0),
                'date'   => $this->resource['last_payment_date'],
            ],
            'year'               => (int) $this->resource['year'],
            'areaName'           => $this->resource['area_name'],
        ];
    }
}