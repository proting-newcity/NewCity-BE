<?php

namespace Database\Factories;

use App\Models\Diskusi;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiskusiFactory extends Factory
{
    protected $model = Diskusi::class;

    public function definition(): array
    {
        return [
            'content'   => $this->faker->sentence(),
            'id_report' => Report::factory(),
            'id_user'   => User::factory(),
        ];
    }
}
