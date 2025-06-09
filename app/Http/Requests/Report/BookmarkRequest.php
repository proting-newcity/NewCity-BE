<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class BookmarkRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->hasRole('masyarakat');
    }

    public function rules()
    {
    }
}
