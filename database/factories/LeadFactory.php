<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'added_on' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'lead_name' => $this->faker->name(),
            'sales_rep_name' => $this->faker->name(),
            'is_closed' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
