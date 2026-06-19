<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\{
    FeeStructure,
    Student,
    StudentEnrollment,
    StudentInstallment,
    InstallmentType,
    AcademicYear
};

class StudentInstallmentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. تحديث year_amount من amount
        FeeStructure::query()->update([
            'year_amount' => DB::raw('amount')
        ]);

        // 2. حذف المدفوعات ثم الأقساط
        DB::table('student_payments')->delete();
        DB::table('student_installments')->delete();

        // 3. إعادة ترقيم ال AUTO_INCREMENT
        DB::statement('ALTER TABLE student_installments AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE student_payments AUTO_INCREMENT = 1');

        // 4. الأكاديمية الحالية
        $currentAcademicYear = AcademicYear::where('is_current', true)->first();
        if (!$currentAcademicYear) {
            $this->command->error("❌ لا توجد سنة دراسية حالية!");
            return;
        }

        $installmentTypeId = InstallmentType::where('name', 'رسوم دراسية')->value('id');

        // 5. جلب الطلاب مع التسجيلات
        $students = Student::with(['enrollments.classroom.grade.stage.sectionObj'])->get();

        foreach ($students as $student) {
            foreach ($student->enrollments as $enrollment) {
                $classroom = $enrollment->classroom;
                $sectionType = optional($classroom->grade->stage->sectionObj)->type;

                $feeStructure = FeeStructure::query()
                    ->where('section_type', $sectionType)
                    ->where('stage_id', $enrollment->stage_id)
                    ->where('grade_id', $enrollment->grade_id)
                    ->first();

                if (!$feeStructure) {
                    $this->command->warn("⚠️ لم يتم العثور على رسوم لصف الطالب: {$student->name}");
                    continue;
                }

                $yearAmount     = (float) $feeStructure->year_amount;
                $semesterAmount = (float) $feeStructure->amount;

                if ($enrollment->billing_cycle === 'semester') {
                    $amountDue = $semesterAmount;
                    $reference = 'SEM1-' . $student->id . '-' . $currentAcademicYear->id;
                } else {
                    $amountDue = $yearAmount;
                    $reference = 'YEAR-' . $student->id . '-' . $currentAcademicYear->id;
                }

                $status = 'due';

                StudentInstallment::create([
                    'student_id'            => $student->id,
                    'student_enrollment_id' => $enrollment->id,
                    'amount_due'            => $amountDue,
                    'paid_amount'           => 0,
                    'due_date'              => null,
                    'status'                => $status,
                    'installment_type_id'   => $installmentTypeId,
                    'reference'             => $reference,
                    'notes'                 => null,
                ]);
            }
        }

        $this->command->info("✅ تم إعادة إنشاء الأقساط لجميع الطلاب بنجاح");
    }
}