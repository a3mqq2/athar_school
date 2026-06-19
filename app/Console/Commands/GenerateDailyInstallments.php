<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentEnrollment;
use App\Models\FeeStructure;
use App\Models\StudentInstallment;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GenerateDailyInstallments extends Command
{
    protected $signature = 'installments:generate-semester';
    protected $description = 'Generate semester-based student installments only';

    public function handle()
    {
        $academicYear = AcademicYear::where('is_current', true)->first();

        if (!$academicYear) {
            $this->error('❌ No current academic year found.');
            return;
        }

        $startDate = Carbon::parse($academicYear->start_date);
        $endDate = Carbon::parse($academicYear->end_date);
        $today = now();
        $midDate = $startDate->copy()->addDays($startDate->diffInDays($endDate) / 2);

        $enrollments = StudentEnrollment::with('student')
            ->where('academic_year_id', $academicYear->id)
            ->whereRaw("LOWER(billing_cycle) = 'semester'")
            ->get();

        $counter = 0;

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            $studentId = $student->id;

            if ($student->status != 'active') {
                continue;
            }

            $feeStructure = FeeStructure::where('stage_id', $enrollment->stage_id)
                ->where('grade_id', $enrollment->grade_id)
                ->first();

            if (!$feeStructure || !$feeStructure->amount) {
                $this->warn("❌ Invalid or missing amount for student ID $studentId");
                continue;
            }

            $amount = (float) $feeStructure->amount;

            DB::beginTransaction();

            try {
                // الفصل الأول
                $firstExists = StudentInstallment::where('student_id', $studentId)
                    ->whereHas('enrollment', function($q) use ($academicYear) {
                        $q->where('academic_year_id', $academicYear->id);
                    })
                    ->whereDate('due_date', $startDate->toDateString())
                    ->exists();

                if (!$firstExists && $today->greaterThanOrEqualTo($startDate)) {
                    StudentInstallment::create([
                        'student_id' => $studentId,
                        'student_enrollment_id' => $enrollment->id,
                        'semester_number' => 1,
                        'amount_due' => $amount,
                        'due_date' => $startDate,
                        'notes' => 'First Semester Installment',
                        'status' => 'due',
                        'reference' => 'SEM-' . $academicYear->id . '-' . $studentId . '-' . now()->format('His'),
                    ]);
                }

                // الفصل الثاني
                $secondExists = StudentInstallment::where('student_id', $studentId)
                    ->whereHas('enrollment', function($q) use ($academicYear) {
                        $q->where('academic_year_id', $academicYear->id);
                    })
                    ->whereDate('due_date', $midDate->toDateString())
                    ->exists();

                if (!$secondExists && $today->greaterThanOrEqualTo($midDate)) {
                    StudentInstallment::create([
                        'student_id' => $studentId,
                        'student_enrollment_id' => $enrollment->id,
                        'semester_number' => 2,
                        'amount_due' => $amount / 2,
                        'due_date' => $midDate,
                        'notes' => 'Second Semester Installment',
                        'status' => 'due',
                        'reference' => 'SEM-' . $academicYear->id . '-' . $studentId . '-' . now()->addSecond()->format('His'),
                    ]);
                }

                DB::commit();
                $counter++;
                $this->info("✅ Semester installments created for student ID: $studentId");

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("❌ Error for student ID: $studentId - {$e->getMessage()}");
            }
        }

        $this->info("🎯 Done. Semester installments processed for $counter students.");
    }
}
