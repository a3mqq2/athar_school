<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // تعريف الأدوار
        $roles = [
            'admin'      => 'الادارة',
            'finance'    => 'المالية',
            'teacher'    => 'معلم',
            'supervisor' => 'مشرف',
        ];

        foreach ($roles as $name => $displayName) {
            Role::firstOrCreate(
                ['name' => $name],
                ['display_name' => $displayName]
            );
        }

        // تعريف الصلاحيات
        $permissions = [
            // صلاحيات الإدارة
            'manage_students'     => 'ادارة الطلاب',
            'manage_settings'     => 'الاعدادات',
            'manage_users'        => 'المستخدمين',

            // صلاحيات المالية
            'student_installments'    => 'اقساط الطلاب',
            'teacher_settlements'     => 'تسوية معلمين',
            'salaries'                => 'الرواتب',
            'manage_financial_vaults' => 'ادارة الخزائن المالية',
            'financial_transactions'  => 'المعاملات المالية',
            'vault_transfers'         => 'التحويلات بين الخزائن',
            'financial_reports'       => 'تقارير مالية',
        ];

        foreach ($permissions as $name => $displayName) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $displayName]
            );
        }

        // ربط الصلاحيات بالأدوار
        $adminPermissions = [
            'manage_students',
            'manage_settings',
            'manage_users',
        ];

        $financePermissions = [
            'student_installments',
            'teacher_settlements',
            'salaries',
            'manage_financial_vaults',
            'financial_transactions',
            'vault_transfers',
            'financial_reports',
        ];

        // تعيين الصلاحيات
        Role::where('name', 'admin')->first()?->givePermissionTo($adminPermissions);
        Role::where('name', 'finance')->first()?->givePermissionTo($financePermissions);

        // supervisor بدون صلاحيات
        // teacher بدون صلاحيات محددة حالياً

        // تعيين الأدوار للمستخدم id=1
        $user = User::find(1);
        if ($user) {
            $user->syncRoles(['admin', 'finance', 'supervisor']);
        }
    }
}