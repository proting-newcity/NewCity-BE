<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\Masyarakat;
use App\Models\Pemerintah;
use App\Models\KategoriReport;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Report::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'judul' => $this->faker->sentence,
            'deskripsi' => $this->faker->paragraph(2),
            'lokasi' => $this->faker->address,
            'foto' => $this->faker->imageUrl(),
            'status' => json_encode(['open']),
            'id_masyarakat' => Masyarakat::factory(),
            'id_pemerintah' => Pemerintah::factory(),
            'id_kategori' => KategoriReport::factory(),
        ];
    }
}
