<?php

namespace App\Http\Requests\Berita;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBeritaRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'title'       => 'sometimes|required|string|max:100',
            'content'     => 'sometimes|required|string',
            'status'      => 'sometimes|required|string|max:50',
            'foto'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'id_kategori' => 'required|integer|exists:kategori_berita,id',
        ];
    }
}
