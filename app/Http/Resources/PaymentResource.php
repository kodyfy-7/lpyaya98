<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'amount'        => (float) $this->amount,
            'status'        => $this->status,
            'paymentMethod' => $this->transaction?->payment_method,
            'initiatedBy'   => $this->createdBy?->name,
            'paidAt'        => $this->paid_at,
            'createdAt'     => $this->created_at,
        ];
    }
}