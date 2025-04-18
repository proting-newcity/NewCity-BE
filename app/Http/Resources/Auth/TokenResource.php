<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
{
    public function toArray($request)
    {
        return $this->resource;
    }
}
