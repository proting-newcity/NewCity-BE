<?php

namespace App\Http\Requests\Berita;

use Illuminate\Foundation\Http\FormRequest;

class StoreBeritaRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'title'       => 'required|string|max:50',
            'content'     => 'required|string',
            'foto'        => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'      => 'required|string|max:50',
            'id_kategori' => 'required|integer|exists:kategori_berita,id',
        ];
    }
}
