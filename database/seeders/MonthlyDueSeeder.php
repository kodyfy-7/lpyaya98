<?php

namespace Database\Seeders;

use App\Enums\DueStatus;
use App\Models\MonthlyDue;
use Illuminate\Database\Seeder;

class MonthlyDueSeeder extends Seeder
{
    public function run(): void
    {
        MonthlyDue::updateOrCreate(
            [
                'area_id' => '7b3e7749-2a36-49f0-b279-458c75871d7b',
                'month'   => 4,
                'year'    => 2026,
            ],
            [
                'due_amount'  => 30000,
                'paid_amount' => 0,
                'status'      => DueStatus::UNPAID,
            ]
        );
    }
}