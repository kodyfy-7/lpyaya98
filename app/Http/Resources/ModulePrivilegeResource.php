<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModulePrivilegeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'moduleId' => $this->module_id,
            'module' => new ModuleResource($this->whenLoaded('module')),
            'name' => $this->name,
            'slug' => $this->slug,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
