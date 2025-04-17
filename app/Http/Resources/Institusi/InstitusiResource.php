<?php

namespace App\Http\Resources\Institusi;

use Illuminate\Http\Resources\Json\JsonResource;

class InstitusiResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
