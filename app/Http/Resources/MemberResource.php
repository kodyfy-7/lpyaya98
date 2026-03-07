<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'zoneId' => $this->zone_id,
            'zone' => new ZoneResource($this->whenLoaded('zone')),
            'zonePositionId' => $this->zone_position_id,
            'zonePosition' => new PositionResource($this->whenLoaded('zonePosition')),
            'areaId' => $this->area_id,
            'area' => new AreaResource($this->whenLoaded('area')),
            'areaPositionId' => $this->area_position_id,
            'areaPosition' => new PositionResource($this->whenLoaded('areaPosition')),
            'provinceId' => $this->province_id,
            'province' => new ProvinceResource($this->whenLoaded('province')),
            'provincePositionId' => $this->province_position_id,
            'provincePosition' => new PositionResource($this->whenLoaded('provincePosition')),
            'parishId' => $this->parish_id,
            'parish' => new ParishResource($this->whenLoaded('parish')),
            'parishPositionId' => $this->parish_position_id,
            'parishPosition' => new PositionResource($this->whenLoaded('parishPosition')),
            'departmentId' => $this->department_id,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
