<?php
namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class DiskusiStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content' => 'required|string',
        ];
    }
}
