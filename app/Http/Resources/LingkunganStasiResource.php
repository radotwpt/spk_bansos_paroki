<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LingkunganStasiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stasi_id' => $this->stasi_id,
            'nama_lingkungan_stasi' => $this->nama_lingkungan_stasi,
            'kode_lingkungan' => $this->kode_lingkungan,
            'stasi' => $this->whenLoaded('stasi', fn () => [
                'id' => $this->stasi?->id,
                'nama_stasi' => $this->stasi?->nama_stasi,
                'kode_stasi' => $this->stasi?->kode_stasi,
            ]),
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
