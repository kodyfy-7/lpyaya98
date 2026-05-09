<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonthlyDueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'month'       => (int) $this->month,
            'monthName'   => \DateTime::createFromFormat('!m', $this->month)->format('F'),
            'year'        => (int) $this->year,
            'dueAmount'   => (float) $this->due_amount,
            'paidAmount'  => (float) $this->paid_amount,
            'remaining'   => (float) max(0, $this->due_amount - $this->paid_amount),
            'status'      => $this->status,
            'payments'    => PaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}