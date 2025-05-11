<?php
namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class AddStatusRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('pemerintah') || $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'status' => 'required|string',
        ];
    }
}
