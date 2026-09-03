<?php

namespace Database\Factories;

use App\Models\Pemerintah;
use App\Models\Institusi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PemerintahFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Pemerintah::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => User::factory(),
            'status' => fake()->boolean(),
            'phone' => fake()->phoneNumber(),
            'institusi_id' => Institusi::factory(),
        ];
    }
}
