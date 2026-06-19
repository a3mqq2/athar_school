<?php
// app/Exports/StudentContactsExport.php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentContactsExport implements FromQuery, WithHeadings, WithMapping, WithColumnWidths, WithStyles
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

        // Apply the same filters as in StudentsExport
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

        if (!empty($this->filters['section_id'])) {
            $query->whereHas('currentEnrollment.stage.sectionObj', function($q) {
                $q->where('id', $this->filters['section_id']);
            });
        }

        if (!empty($this->filters['stage_id'])) {
            $query->whereHas('currentEnrollment.stage', function($q) {
                $q->where('id', $this->filters['stage_id']);
            });
        }

        if (!empty($this->filters['grade_id'])) {
            $query->whereHas('currentEnrollment.grade', function($q) {
                $q->where('id', $this->filters['grade_id']);
            });
        }

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
            'اسم الطالب',
            'ولي الأمر',
            'الهاتف',
            'اسم الأم',
            'رقم الهاتف الام',
            'القسم',
            'الصف'
        ];
    }

    public function map($student): array
    {
        return [
            $student->name ?? '',
            $student->parent_name ?? '',
            $student->phone ?? '',
            $student->mother_name ?? '',
            $student->phone2 ?? '',
            $student->currentEnrollment->stage->sectionObj->type_name ?? '',
            $student->currentEnrollment->grade->name ?? ''
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, // اسم الطالب
            'B' => 30, // ولي الأمر
            'C' => 20, // الهاتف
            'D' => 30, // اسم الأم
            'E' => 20, // رقم الهاتف 2
            'F' => 25, // القسم
            'G' => 20, // الصف
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