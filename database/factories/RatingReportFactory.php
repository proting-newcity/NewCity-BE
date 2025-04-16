<?php

namespace Database\Factories;

use App\Models\RatingReport;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RatingReportFactory extends Factory
{
    protected $model = RatingReport::class;

    public function definition(): array
    {
        return [
            'id_report' => Report::factory(),
            'id_user'   => User::factory(),
        ];
    }
}
