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
            'title' => $this->faker->sentence(2),
            'content' => $this->faker->paragraphs(1, true),
            'foto' => 'berita/default.jpg',
            'tanggal' => $this->faker->date('Y-m-d'),
            'status' => 'published', // or 'draft' if you use multiple statuses
            'id_kategori' => KategoriBerita::factory(), // create related category
            'id_user' => Admin::factory(),
        ];
    }
}
