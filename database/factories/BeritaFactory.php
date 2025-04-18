<?php

namespace Database\Factories;

use App\Models\Berita;
use App\Models\Admin;
use App\Models\KategoriBerita;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Berita>
 */
class BeritaFactory extends Factory
{
    protected $model = Berita::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(1),
            'content' => $this->faker->sentence(10, true),
            'foto' => 'berita/default.jpg',
            'tanggal' => $this->faker->date('Y-m-d'),
            'status' => 'published',
            'id_kategori' => KategoriBerita::factory(),
            'id_user' => Admin::factory(),
        ];
    }
}
