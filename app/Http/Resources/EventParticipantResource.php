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
            'eventId' => $this->event_id,
            'name' => $this->name,
            'phoneNumber' => $this->phone_number,
            'zoneId' => $this->zone_id,
            'zone' => new ZoneResource($this->whenLoaded('zone')),
            'areaId' => $this->area_id,
            'area' => new AreaResource($this->whenLoaded('area')),
            'parishId' => $this->parish_id,
            'parish' => new ParishResource($this->whenLoaded('parish')),
            'attended' => $this->attended,
            'registrationApproved' => $this->registration_approved,
            'location' => $this->location,
            'registrationNumber' => $this->registration_number,
            'gender' => $this->gender,
            'email' => $this->email,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
