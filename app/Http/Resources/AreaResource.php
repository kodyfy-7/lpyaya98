<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'zoneId' => $this->zone_id,
            'name' => $this->name,
            'status' => $this->status,
            'zone' => new ZoneResource($this->whenLoaded('zone')),
            'parishes' => ParishResource::collection($this->whenLoaded('parishes')),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
