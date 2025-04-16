<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition()
    {
        $user = User::factory()->create();
        return [
            'id'  => $user->id,
            'nip' => $this->faker->unique()->numerify('1980##########'),
        ];
    }
}
