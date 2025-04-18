<?php

namespace App\Http\Requests\Masyarakat;

use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
{
    public function authorize()
    {
        // only masyarakat role can access notifications
        return $this->user() && $this->user()->hasRole('masyarakat');
    }

    public function rules()
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }
}
