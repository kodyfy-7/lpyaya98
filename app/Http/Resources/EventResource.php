<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'parentId' => $this->parent_id,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'startTime' => $this->start_time,
            'registrationFee' => $this->registration_fee,
            'location' => $this->location,
            'participants' => EventParticipantResource::collection($this->whenLoaded('participants')),
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
