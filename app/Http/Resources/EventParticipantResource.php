<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'eventId' => $this->eventId,
            'name' => $this->name,
            'phoneNumber' => $this->phoneNumber,
            'zoneId' => $this->zoneId,
            'zone' => new ZoneResource($this->whenLoaded('zone')),
            'areaId' => $this->areaId,
            'area' => new AreaResource($this->whenLoaded('area')),
            'parishId' => $this->parishId,
            'parish' => new ParishResource($this->whenLoaded('parish')),
            'attended' => $this->attended,
            'registrationApproved' => $this->registrationApproved,
            'location' => $this->location,
            'registrationNumber' => $this->registrationNumber,
            'gender' => $this->gender,
            'email' => $this->email,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
