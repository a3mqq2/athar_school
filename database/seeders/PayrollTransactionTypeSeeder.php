<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransactionType;

class PayrollTransactionTypeSeeder extends Seeder
{
    public function run(): void
    {
        TransactionType::updateOrCreate(
            ['name' => 'رواتب'],
            [
                'type' => 'withdrawal',
                'for_system' => true,
            ]
        );
    }
}
