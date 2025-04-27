<?php

namespace App\Http\Requests\Masyarakat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class UpdateMasyarakatRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->hasRole('masyarakat');
    }

    public function rules()
    {
        $userId = $this->user()?->id;

        return [
            'name' => 'nullable|string|max:255',
            'username' => "nullable|string|max:255|unique:user,username,{$userId}",
            'password' => ['nullable', Rules\Password::defaults()],
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ];
    }

}
