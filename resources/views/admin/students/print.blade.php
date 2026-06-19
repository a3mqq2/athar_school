{{-- resources/views/admin/students/print.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة قائمة الطلاب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.3;
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .print-header h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .print-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        .table {
            font-size: 10px;
        }
        
        .table th {
            background-color: #f8f9fa !important;
            border: 1px solid #333 !important;
            font-weight: bold;
            text-align: center;
            padding: 8px 4px !important;
        }
        
        .table td {
            border: 1px solid #666 !important;
            padding: 6px 4px !important;
            vertical-align: middle;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .status-graduated { background: #d1ecf1; color: #0c5460; }
        .status-transferred { background: #fff3cd; color: #856404; }
        
        @media print {
            body { margin: 0; }
            .table { page-break-inside: auto; }
            .table tr { page-break-inside: avoid; page-break-after: auto; }
            .table thead { display: table-header-group; }
        }
        
        @page {
            margin: 1cm;
            size: A4 landscape;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="print-header">
            <h1>قائمة الطلاب</h1>
            <div class="print-info">
                <div>
                    <strong>تاريخ الطباعة:</strong> {{ now()->format('Y-m-d H:i') }}
                </div>
                <div>
                    <strong>إجمالي الطلاب:</strong> {{ $students->count() }}
                </div>
            </div>
            
            @if(request()->hasAny(['search','section_id','stage_id','grade_id','classroom_id']))
                <div class="alert alert-info py-2">
                    <strong>مفلتر حسب:</strong>
                    @if(request('search')) البحث: "{{ request('search') }}" @endif
                    @if(request('section_id')) | القسم: {{ $sections->find(request('section_id'))->type_name ?? '' }} @endif
                    @if(request('stage_id')) | المرحلة @endif
                    @if(request('grade_id')) | الصف @endif
                    @if(request('classroom_id')) | الفصل @endif
                </div>
            @endif
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th style="width:5%">الكود</th>
                    <th style="width:9%">الرقم الوطني</th>
                    <th style="width:8%">رقم القيد</th>
                    <th style="width:11%">الاسم</th>
                    <th style="width:7%">الجنسية</th>
                    <th style="width:11%">ولي الأمر</th>
                    <th style="width:9%">الهاتف</th>
                    <th style="width:11%">اسم الأم</th>
                    <th style="width:9%">رقم الهاتف</th>
                    <th style="width:6%">الجنس</th>
                    <th style="width:8%">الحالة</th>
                    <th style="width:6%">الصف</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                    <tr>
                        <td class="text-center">{{ $student->code ?? '—' }}</td>
                        <td>{{ $student->national_id ?? '—' }}</td>
                        <td class="text-center">{{ $student->registration_number ?? '—' }}</td>
                        <td><strong>{{ $student->name }}</strong></td>
                        <td>{{ $student->nationality ?? '—' }}</td>
                        <td>{{ $student->parent_name ?? '—' }}</td>
                        <td>{{ $student->phone ?? '—' }}</td>
                        <td>{{ $student->mother_name ?? '—' }}</td>
                        <td>{{ $student->phone2 ?? '—' }}</td>
                        <td class="text-center">
                            @if($student->gender === 'male') ذكر
                            @elseif($student->gender === 'female') أنثى
                            @else — @endif
                        </td>
                        <td class="text-center">
                            @php $st = $student->status; @endphp
                            <span class="status-badge status-{{ $st }}">
                                @switch($st)
                                    @case('active') نشط @break
                                    @case('inactive') غير نشط @break
                                    @case('graduated') متخرج @break
                                    @case('transferred') منتقل @break
                                    @default غير معروف
                                @endswitch
                            </span>
                        </td>
                        <td>{{ $student->currentEnrollment->grade->name ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="mt-4 text-center">
            <small class="text-muted">تم إنشاء هذا التقرير بواسطة نظام أثر الالكتروني</small>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
            // Close window after printing (optional)
            window.onafterprint = function() {
                window.close();
            }
        }
    </script>
</body>
</html>