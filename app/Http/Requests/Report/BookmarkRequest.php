<?php

namespace App\Http\Requests\Masyarakat;

use Illuminate\Foundation\Http\FormRequest;

class BookmarkRequest extends FormRequest
{
    public function authorize()
    {
        // only masyarakat role can access notifications
        return $this->user() && $this->user()->hasRole('masyarakat');
    }

    public function rules()
    {
    }
}
