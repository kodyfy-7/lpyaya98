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
            'userId' => $this->userId,
            'user' => new UserResource($this->whenLoaded('user')),
            'zoneId' => $this->zoneId,
            'zone' => new ZoneResource($this->whenLoaded('zone')),
            'zonePositionId' => $this->zone_positionId,
            'zonePosition' => new PositionResource($this->whenLoaded('zonePosition')),
            'areaId' => $this->areaId,
            'area' => new AreaResource($this->whenLoaded('area')),
            'areaPositionId' => $this->area_positionId,
            'areaPosition' => new PositionResource($this->whenLoaded('areaPosition')),
            'provinceId' => $this->provinceId,
            'province' => new ProvinceResource($this->whenLoaded('province')),
            'provincePositionId' => $this->province_positionId,
            'provincePosition' => new PositionResource($this->whenLoaded('provincePosition')),
            'parishId' => $this->parishId,
            'parish' => new ParishResource($this->whenLoaded('parish')),
            'parishPositionId' => $this->parish_positionId,
            'parishPosition' => new PositionResource($this->whenLoaded('parishPosition')),
            'departmentId' => $this->departmentId,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
