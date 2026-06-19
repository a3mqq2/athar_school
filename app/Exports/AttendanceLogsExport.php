<?php

namespace App\Exports;

use App\Models\AttendanceLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Http\Request;

class AttendanceLogsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $request;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    public function query()
    {
        $query = AttendanceLog::with(['user', 'user.roles', 'supervisor']);
        
        // Apply filters
        if ($this->request->date_from) {
            $query->whereDate('date', '>=', $this->request->date_from);
        }
        
        if ($this->request->date_to) {
            $query->whereDate('date', '<=', $this->request->date_to);
        }
        
        if ($this->request->search) {
            $query->whereHas('user', function($q) {
                $q->where('name', 'like', '%' . $this->request->search . '%')
                  ->orWhere('code', 'like', '%' . $this->request->search . '%')
                  ->orWhere('email', 'like', '%' . $this->request->search . '%');
            });
        }
        
        if ($this->request->role) {
            $query->whereHas('user.roles', function($q) {
                $q->where('name', $this->request->role);
            });
        }
        
        return $query->orderBy('date', 'desc')->orderBy('check_in_time', 'desc');
    }
    
    public function headings(): array
    {
        return [
            'م',
            'الاسم',
            'البريد الإلكتروني',
            'الكود',
            'الصلاحية',
            'التاريخ',
            'وقت الحضور',
            'عدد الحصص',
            'المسجل',
            'ملاحظات'
        ];
    }
    
    public function map($log): array
    {
        static $rowNumber = 0;
        $rowNumber++;
        
        return [
            $rowNumber,
            $log->user->name,
            $log->user->email,
            $log->user->code,
            $log->user->roles->pluck('name')->implode(', '),
            $log->date->format('Y-m-d'),
            $log->check_in_time->format('h:i A'),
            $log->lessons_count ?: '-',
            $log->supervisor->name,
            $log->notes ?: '-'
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        // Header style
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '333333'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FBD02B'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        // Set RTL direction
        $sheet->setRightToLeft(true);
        
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}