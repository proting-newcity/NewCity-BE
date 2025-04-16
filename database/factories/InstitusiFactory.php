<?php

namespace Database\Factories;

use App\Models\Institusi;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstitusiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Institusi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company(),
        ];
    }
}
