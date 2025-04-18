<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class StorePemerintahRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'name'         => 'required|string',
            'username'     => 'required|string|max:255|unique:user',
            'phone'        => 'required|string',
            'password'     => ['required', Rules\Password::defaults()],
            'institusi_id' => 'nullable|exists:institusi,id',
            'foto'         => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'status'       => 'required|boolean',
        ];
    }
}
