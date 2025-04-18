<?php

namespace App\Http\Requests\Institusi;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstitusiRequest extends FormRequest
{
    public function authorize()
    {
        // Only admin users can create institusi
        return $this->user() && $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
