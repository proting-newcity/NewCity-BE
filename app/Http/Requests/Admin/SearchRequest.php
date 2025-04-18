<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'search' => 'required|string',
        ];
    }
}
