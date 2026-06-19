<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InstallmentType;

class InstallmentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InstallmentType::firstOrCreate(
            ['name' => 'رسوم دراسية'],
            ['status' => 'active']
        );
    }
}
