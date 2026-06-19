<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@demo.com',
            'password' => Hash::make(123123123),
            'is_active' => true,
            
        ]);

        $this->call([
            PermissionSeeder::class,
            // StaffUsersSeeder::class,
            TransactionTypeSeeder::class,
            PayrollTransactionTypeSeeder::class,
            TeacherSettlementTransactionTypeSeeder::class,
            InstallmentTypeTransactionSeeder::class,
            InstallmentTypeSeeder::class,
            RefundTransactionTypeSeeder::class,
        ]);
    }
}
