<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PositionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'level' => $this->level,
            'positionPrivileges' => $this->whenLoaded('positionPrivileges', function () {
                return $this->positionPrivileges->map(fn ($pp) => [
                    'id' => $pp->id,
                    'privilege' => $pp->privilege ? [
                        'id' => $pp->privilege->id,
                        'name' => $pp->privilege->name,
                        'slug' => $pp->privilege->slug,
                        'module' => $pp->privilege->module ? [
                            'id' => $pp->privilege->module->id,
                            'name' => $pp->privilege->module->name,
                            'slug' => $pp->privilege->module->slug,
                        ] : null,
                    ] : null,
                ]);
            }),
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
