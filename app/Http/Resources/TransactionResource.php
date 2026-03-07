<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'amount' => $this->amount,
            'status' => $this->status,
            'paymentMethod' => $this->payment_method,
            'reference' => $this->reference,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
