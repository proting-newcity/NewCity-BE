<?php
namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('masyarakat');
    }

    public function rules()
    {
        return [
            'judul'         => 'required|string|max:100',
            'deskripsi'     => 'required|string',
            'lokasi'        => 'required|string',
            'foto'          => 'required|image|mimes:jpeg,png,jpg,gif',
            'id_pemerintah' => 'nullable|exists:pemerintah,id',
            'id_kategori'   => 'required|exists:kategori_report,id',
        ];
    }
}
