<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BansosPeriodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_periode' => $this->nama_periode,
            'tahun' => $this->tahun,
            'status_periode' => $this->status_periode,
            'is_locked' => (bool) $this->is_locked,
            'locked_at' => optional($this->locked_at)?->toISOString(),
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
