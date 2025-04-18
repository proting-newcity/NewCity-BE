<?php

namespace App\Http\Resources\Pemerintah;

use Illuminate\Http\Resources\Json\JsonResource;

class PemerintahResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'name'         => $this->user->name,
            'username'     => $this->user->username,
            'phone'        => $this->phone,
            'institusi_id' => $this->institusi_id,
            'status'       => $this->status,
            'foto'         => $this->foto,
        ];
    }
}
