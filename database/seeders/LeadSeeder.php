<?php

namespace Database\Seeders;

use App\Models\Lead;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    public function run()
    {
        $salesReps = [
            'Garry Smith',
            'Peter Parker',
            'Tony Stark',
            'Steve Rogers',
            'Bruce Wayne',
            'Clark Kent',
            'Diana Prince',
            'Barry Allen',
            'Arthur Curry',
            'Victor Stone',
        ];

        foreach ($salesReps as $rep) {
            Lead::factory()
                ->count(100)
                ->create([
                    'sales_rep_name' => $rep,
                ]);
        }
    }
}
