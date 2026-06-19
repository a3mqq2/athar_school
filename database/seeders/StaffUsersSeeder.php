<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class StaffUsersSeeder extends Seeder
{
    public function run(): void
    {
        $people = [
            // Teachers
            ['name' => 'Saja Najeeb',            'email' => 'saja.najeeb@school.test',           'subject' => 'Social Studies', 'job_title' => 'Teacher (Social Studies)',                   'role' => 'teacher'],
            ['name' => 'Amjad Sergiwa',          'email' => 'amjad.sergiwa@school.test',         'subject' => 'English',        'job_title' => 'Teacher (English) + Academic Supervisor',   'role' => 'teacher'],
            ['name' => 'Marwa Saad',             'email' => 'marwa.saad@school.test',            'subject' => null,             'job_title' => 'Teacher Assistant',                         'role' => 'teacher'],
            ['name' => 'Mohamed Abdulbasit',     'email' => 'mohamed.abdulbasit@school.test',    'subject' => 'English',        'job_title' => 'Teacher (English)',                         'role' => 'teacher'],
            ['name' => 'Aisha Fathi',            'email' => 'aisha.fathi@school.test',           'subject' => 'Arabic',         'job_title' => 'Teacher (Arabic)',                          'role' => 'teacher'],
            ['name' => 'Arwa Yousif',            'email' => 'arwa.yousif@school.test',           'subject' => 'English',        'job_title' => 'Teacher (English)',                         'role' => 'teacher'],
            ['name' => 'Heba Kheralla',          'email' => 'heba.kheralla@school.test',         'subject' => 'Maths',          'job_title' => 'Teacher (Maths)',                           'role' => 'teacher'],
            ['name' => 'Sumia Abdullah',         'email' => 'sumia.abdullah@school.test',        'subject' => 'English',        'job_title' => 'Teacher (English)',                         'role' => 'teacher'],
            ['name' => 'Sumaya Shallouf',        'email' => 'sumaya.shallouf@school.test',       'subject' => null,             'job_title' => 'Teacher + Head of Examination Committee',   'role' => 'teacher'],
            ['name' => 'Ibtesam Abdulhaleem',    'email' => 'ibtesam.abdulhaleem@school.test',   'subject' => 'Arabic',         'job_title' => 'Teacher (Arabic)',                          'role' => 'teacher'],
            ['name' => 'Seham Almabrouk',        'email' => 'seham.almabrouk@school.test',       'subject' => null,             'job_title' => 'Teacher + Academic Supervisor',             'role' => 'teacher'],
            ['name' => 'Aya Alsanousi',          'email' => 'aya.alsanousi@school.test',         'subject' => 'ICT',            'job_title' => 'Teacher (ICT)',                             'role' => 'teacher'],
            ['name' => 'Noor-Alhuda Najeeb',     'email' => 'noor-alhuda.najeeb@school.test',    'subject' => 'Maths',          'job_title' => 'Teacher (Maths)',                           'role' => 'teacher'],
            ['name' => 'Salma Younis',           'email' => 'salma.younis@school.test',          'subject' => 'Science',        'job_title' => 'Teacher (Science)',                         'role' => 'teacher'],

            // Admin / Finance
            ['name' => 'Saad Bushallah',         'email' => 'saad.bushallah@school.test',        'subject' => null,             'job_title' => 'General Manager',                           'role' => 'admin'],
            ['name' => 'Abdurahman Omar',        'email' => 'abdurahman.omar@school.test',       'subject' => null,             'job_title' => 'Chief of Administration',                   'role' => 'admin'],
            ['name' => 'Menna Omar',             'email' => 'menna.omar@school.test',            'subject' => null,             'job_title' => 'Payroll Officer',                           'role' => 'finance'],
            ['name' => 'Amina Alamami',          'email' => 'amina.alamami@school.test',         'subject' => null,             'job_title' => 'Administrative Assistant',                  'role' => 'admin'],
            ['name' => 'Rehab Moahmmed',         'email' => 'rehab.moahmmed@school.test',        'subject' => null,             'job_title' => 'Finance Manager',                           'role' => 'finance'],
            ['name' => 'Idrees Musa',            'email' => 'idrees.musa@school.test',           'subject' => null,             'job_title' => 'School Director',                           'role' => 'admin'],
            ['name' => 'Hanan Fawzi',            'email' => 'hanan.fawzi@school.test',           'subject' => null,             'job_title' => 'Web Officer',                               'role' => 'admin'],
            ['name' => 'Zainab Ahmed',           'email' => 'zainab.ahmed@school.test',          'subject' => null,             'job_title' => 'Foundation Director',                       'role' => 'admin'],
        ];

        foreach ($people as $p) {
            $user = User::firstOrCreate(
                ['email' => $p['email']],
                [
                    'name'      => $p['name'],
                    'password'  => Hash::make('password'),
                    'hire_date' => now(),
                    'subject'   => $p['subject'] ?? null,
                    'job_title' => $p['job_title'],
                    'code'      => $this->generateUniqueCode(),
                ]
            );

            $user->syncRoles([$p['role']]);
        }
    }

    private function generateUniqueCode(): string
    {
        do {
            // رقم مكون من 6 أرقام عشوائية
            $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (User::where('code', $code)->exists());

        return $code;
    }
}