<?php
namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    public function authorize()
    {
        $report = $this->route('id');
        return $this->user()->ownsReport($report);
    }

    public function rules()
    {
        return [
            'judul'         => 'sometimes|required|string|max:100',
            'deskripsi'     => 'sometimes|required|string',
            'lokasi'        => 'sometimes|required|string',
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'id_pemerintah' => 'nullable|exists:pemerintah,id',
            'id_kategori'   => 'sometimes|required|exists:kategori_report,id',
        ];
    }
}
