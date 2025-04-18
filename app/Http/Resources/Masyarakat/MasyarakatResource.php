<?php

namespace App\Http\Resources\Masyarakat;

use Illuminate\Http\Resources\Json\JsonResource;

class MasyarakatResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'       => $this->id,
            'name'     => $this->user->name,
            'username' => $this->user->username,
            'phone'    => $this->phone,
        ];
    }
}
