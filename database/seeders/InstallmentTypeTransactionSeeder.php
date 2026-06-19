<?php

namespace Database\Seeders;

use App\Models\TransactionType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class InstallmentTypeTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TransactionType::updateOrCreate(
            ['name' => 'اقساط'],
            [
                'type' => 'withdrawal',
                'for_system' => true,
            ]
        );
    }
}
