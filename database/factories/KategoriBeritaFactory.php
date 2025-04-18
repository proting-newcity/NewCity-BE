<?php

namespace Database\Factories;

use App\Models\KategoriBerita;
use Illuminate\Database\Eloquent\Factories\Factory;

class KategoriBeritaFactory extends Factory
{
    protected $model = KategoriBerita::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true),
            'foto' => 'kategori/default.jpg',
        ];
    }
}
