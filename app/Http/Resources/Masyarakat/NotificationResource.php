<?php
namespace App\Http\Resources\Masyarakat;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'foto_profile' => $this->foto_profile,
            'name'         => $this->name,
            'type'         => $this->type,
            'content'      => $this->content,
            'foto'         => $this->foto,
            'tanggal'      => $this->tanggal,
            'id_report'    => $this->id_report,
        ];
    }
}
