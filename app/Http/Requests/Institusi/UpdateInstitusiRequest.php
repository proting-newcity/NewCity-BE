<?php

namespace App\Http\Requests\Institusi;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstitusiRequest extends FormRequest
{
    public function authorize()
    {
        // Only admin users can update institusi
        return $this->user() && $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
        ];
    }
}
