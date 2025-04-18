<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'username'     => ['required', 'string', 'max:255', 'unique:user,username'],
            'password'     => ['required', 'confirmed', Rules\Password::defaults()],
            'role'         => ['nullable', 'string', 'in:masyarakat,pemerintah'],
            'institusi_id' => ['nullable', 'exists:institusi,id', 'required_if:role,pemerintah'],
            'foto'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif'],
        ];
    }
}
