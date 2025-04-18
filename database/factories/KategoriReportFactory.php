<?php

namespace Database\Factories;

use App\Models\KategoriReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class KategoriReportFactory extends Factory
{
    protected $model = KategoriReport::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true),
        ];
    }
}
