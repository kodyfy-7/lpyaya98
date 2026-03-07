<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetUserPasswordsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->update([
            'password' => Hash::make('Password123!'),
        ]);
    }
}
