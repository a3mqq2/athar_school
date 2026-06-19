<?php
// database/seeders/TransactionTypeSeeder.php (إضافة التصنيف الجديد)

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransactionType;

class TeacherSettlementTransactionTypeSeeder extends Seeder
{
    public function run(): void
    {
        TransactionType::updateOrCreate(
            ['name' => 'تسوية حصص معلم'],
            ['type' => 'withdrawal', 'for_system' => true]
        );
    }
}
