<?php
// app/Exports/StudentsExport.php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsExport implements FromQuery, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Student::with([
            'currentEnrollment.stage.sectionObj',
            'currentEnrollment.grade',
            'currentEnrollment.classroom'
        ]);

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('parent_name', 'LIKE', "%{$search}%")
                  ->orWhere('mother_name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('phone2', 'LIKE', "%{$search}%");
            });
        }

        // Apply section filter
        if (!empty($this->filters['section_id'])) {
            $query->whereHas('currentEnrollment.stage.sectionObj', function($q) {
                $q->where('id', $this->filters['section_id']);
            });
        }

        // Apply stage filter
        if (!empty($this->filters['stage_id'])) {
            $query->whereHas('currentEnrollment.stage', function($q) {
                $q->where('id', $this->filters['stage_id']);
            });
        }

        // Apply grade filter
        if (!empty($this->filters['grade_id'])) {
            $query->whereHas('currentEnrollment.grade', function($q) {
                $q->where('id', $this->filters['grade_id']);
            });
        }

        // Apply classroom filter
        if (!empty($this->filters['classroom_id'])) {
            $query->whereHas('currentEnrollment.classroom', function($q) {
                $q->where('id', $this->filters['classroom_id']);
            });
        }

        return $query->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'الكود',
            'الرقم الوطني',
            'رقم القيد',
            'اسم الطالب',
            'ولي الأمر',
            'الجنسية',
            'الهاتف',
            'اسم الأم',
            'رقم الهاتف الام',
            'الجنس',
            'الحالة',
            'القسم',
            'المرحلة',
            'الصف',
            'الفصل',
            'تاريخ الإنشاء'
        ];
    }

    public function map($student): array
    {
        return [
            $student->code ?? '',
            $student->national_id ?? '',
            $student->registration_number ?? '',
            $student->name ?? '',
            $student->parent_name ?? '',
            $student->nationality ?? '',
            $student->phone ?? '',
            $student->mother_name ?? '',
            $student->phone2 ?? '',
            $student->gender === 'male' ? 'ذكر' : ($student->gender === 'female' ? 'أنثى' : ''),
            $this->getStatusText($student->status),
            $student->currentEnrollment->stage->sectionObj->type_name ?? '',
            $student->currentEnrollment->stage->name ?? '',
            $student->currentEnrollment->grade->name ?? '',
            $student->currentEnrollment->classroom->name ?? '',
            $student->created_at->format('Y-m-d')
        ];
    }

    private function getStatusText($status)
    {
        return match($status) {
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'graduated' => 'متخرج',
            'transferred' => 'منتقل',
            default => 'غير معروف'
        };
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12, // الكود
            'B' => 18, // الرقم الوطني
            'C' => 18, // رقم القيد
            'D' => 25, // اسم الطالب
            'E' => 22, // ولي الأمر
            'F' => 15, // الجنسية
            'G' => 15, // الهاتف
            'H' => 22, // اسم الأم
            'I' => 15, // رقم الهاتف 2
            'J' => 12, // الجنس
            'K' => 15, // الحالة
            'L' => 18, // القسم
            'M' => 18, // المرحلة
            'N' => 15, // الصف
            'O' => 15, // الفصل
            'P' => 18, // تاريخ الإنشاء
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}