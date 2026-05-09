<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $outstandingBalance = max(0, ($this->total_due_ytd ?? 0) - ($this->total_paid_ytd ?? 0));

        return [
            'areaId'              => $this->id,
            'name'                => $this->name,
            'outstandingBalance'  => (float) $outstandingBalance,
            'paymentMadeYTD'      => (float) ($this->total_paid_ytd ?? 0),
            'monthlyExpectedDues' => (float) ($this->monthly_expected_dues ?? 0),
            'currentMonthPayment' => (float) ($this->current_month_paid ?? 0),
        ];
    }
}