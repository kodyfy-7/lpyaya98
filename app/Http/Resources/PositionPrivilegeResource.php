<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PositionPrivilegeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'positionId' => $this->position_id,
            'privilegeId' => $this->privilege_id,
            'privilege' => new ModulePrivilegeResource($this->whenLoaded('privilege')),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
