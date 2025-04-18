<?php

namespace Database\Factories;

use App\Models\Masyarakat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MasyarakatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Masyarakat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => User::factory(),
            'phone' => $this->faker->phoneNumber,
        ];
    }
}
