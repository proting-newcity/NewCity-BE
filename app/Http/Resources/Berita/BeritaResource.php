<?php

namespace App\Http\Resources\Berita;

use Illuminate\Http\Resources\Json\JsonResource;

class BeritaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'content'    => $this->content,
            'foto'       => $this->foto,
            'status'     => $this->status,
            'kategori'   => $this->category->name ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

