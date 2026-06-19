<?php

namespace Database\Seeders;

use App\Models\TransactionType;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    public function run()
    {
        // تصنيفات النظام - للإيداعات
        $systemDeposits = [
            'رأس المال',
            'إيداع من خزينة أخرى',
            'تحويل من خزينة أخرى',
            'استرداد مصروف',
            'فوائد بنكية',
            'أرباح استثمارية'
        ];

        // تصنيفات النظام - للسحوبات
        $systemWithdrawals = [
            'تحويل إلى خزينة أخرى',
            'سحب لخزينة أخرى',
            'مصروفات إدارية',
            'رواتب موظفين',
            'رسوم بنكية',
            'استثمارات'
        ];

        // تصنيفات المستخدمين - للإيداعات
        $userDeposits = [
            'مبيعات نقدية',
            'تحصيل ديون',
            'إيرادات أخرى',
            'هبات وتبرعات',
            'إيرادات خدمات'
        ];

        // تصنيفات المستخدمين - للسحوبات
        $userWithdrawals = [
            'مشتريات',
            'مصروفات تشغيلية',
            'صيانة وإصلاح',
            'مواد مكتبية',
            'مصروفات متنوعة',
            'مصروفات نقل',
            'مصروفات ضيافة'
        ];

        // إضافة تصنيفات النظام للإيداعات
        foreach ($systemDeposits as $name) {
            TransactionType::create([
                'name' => $name,
                'type' => 'deposit',
                'for_system' => true
            ]);
        }

        // إضافة تصنيفات النظام للسحوبات
        foreach ($systemWithdrawals as $name) {
            TransactionType::create([
                'name' => $name,
                'type' => 'withdrawal',
                'for_system' => true
            ]);
        }

        // إضافة تصنيفات المستخدمين للإيداعات
        foreach ($userDeposits as $name) {
            TransactionType::create([
                'name' => $name,
                'type' => 'deposit',
                'for_system' => false
            ]);
        }

        // إضافة تصنيفات المستخدمين للسحوبات
        foreach ($userWithdrawals as $name) {
            TransactionType::create([
                'name' => $name,
                'type' => 'withdrawal',
                'for_system' => false
            ]);
        }
    }
}