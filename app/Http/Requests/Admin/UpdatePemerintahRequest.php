<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class UpdatePemerintahRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    public function rules()
    {
        $id = $this->route('id');
        return [
            'name'         => 'nullable|string|max:255',
            'username'     => "nullable|string|max:255|unique:user,username,{$id}",
            'phone'        => 'nullable|string|max:255',
            'password'     => ['nullable', Rules\Password::defaults()],
            'institusi_id' => 'nullable|exists:institusi,id',
            'foto'         => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'status'       => 'nullable|boolean',
        ];
    }
}
