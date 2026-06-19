<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف أقساط الطلاب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
            padding: 20px;
        }

        .print-header {
            text-align: center;
            border-bottom: 3px solid #925419;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .print-header h2 {
            color: #925419;
            font-weight: 800;
            margin: 0 0 10px 0;
        }

        .print-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .filters-applied {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 15px;
        }

        .filters-applied strong {
            color: #856404;
        }

        .filters-applied span {
            display: inline-block;
            background: white;
            padding: 3px 10px;
            margin: 3px;
            border-radius: 5px;
            font-size: 0.85rem;
        }

        .print-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .print-table thead {
            background: #925419;
            color: white;
        }

        .print-table th,
        .print-table td {
            border: 1px solid #dee2e6;
            padding: 8px 6px;
            text-align: center;
        }

        .print-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .print-table tbody tr:hover {
            background: #e9ecef;
        }

        .row-overdue {
            background: rgba(220, 53, 69, 0.1) !important;
            border-right: 3px solid #dc3545;
        }

        .badge-dues {
            background: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .badge-overdue {
            background: #f8d7da;
            color: #721c24;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .badge-paid {
            background: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 600;
        }

        .summary-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #925419;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }

        .summary-box .row > div {
            text-align: center;
            padding: 10px;
        }

        .summary-box h5 {
            color: #925419;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .summary-box .value {
            font-size: 1.3rem;
            font-weight: 800;
            color: #198754;
        }

        .summary-box .value.danger {
            color: #dc3545;
        }

        .footer-print {
            margin-top: 30px;
            text-align: center;
            font-size: 0.75rem;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .print-table {
                font-size: 0.75rem;
            }

            .print-table th,
            .print-table td {
                padding: 5px 4px;
            }

            @page {
                margin: 10mm;
            }
        }

        .status-badge {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-graduated {
            background: #925419;
            color: white;
        }

        .status-transferred {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <!-- زر الطباعة -->
    <div class="no-print mb-3">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> طباعة
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            إغلاق
        </button>
    </div>

    <!-- Header -->
    <div class="print-header">
        <h2>كشف أقساط الطلاب</h2>
        <div class="print-info">
            <div>تاريخ الطباعة: <strong>{{ now()->format('Y-m-d') }}</strong></div>
            <div>الوقت: <strong>{{ now()->format('H:i') }}</strong></div>
            <div>إجمالي الطلاب: <strong>{{ $students->count() }}</strong></div>
        </div>
    </div>

    <!-- Applied Filters -->
    @if(!empty($appliedFilters))
    <div class="filters-applied">
        <strong><i class="fas fa-filter"></i> الفلاتر المطبقة:</strong>
        @foreach($appliedFilters as $filter)
            <span>{{ $filter }}</span>
        @endforeach
    </div>
    @endif

    <!-- Table -->
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th style="width: 180px;">اسم الطالب</th>
                <th style="width: 120px;">ولي الأمر</th>
                <th style="width: 100px;">الهاتف</th>
                <th style="width: 80px;">القسم</th>
                <th style="width: 80px;">المرحلة</th>
                <th style="width: 70px;">الصف</th>
                <th style="width: 70px;">الفصل</th>
                <th style="width: 60px;">الحالة</th>
                <th style="width: 100px;">المستحق</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
                <tr class="{{ ($student->overdue_sum ?? 0) > 0 ? 'row-overdue' : '' }}">
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: right; font-weight: 600;">{{ $student->name }}</td>
                    <td style="text-align: right;">{{ $student->parent_name ?? '—' }}</td>
                    <td>{{ $student->phone ?? '—' }}</td>
                    <td>{{ $student->currentEnrollment->stage->sectionObj->type_name ?? '—' }}</td>
                    <td>{{ $student->currentEnrollment->stage->name ?? '—' }}</td>
                    <td>{{ $student->currentEnrollment->grade->name ?? '—' }}</td>
                    <td>{{ $student->currentEnrollment->classroom->name ?? '—' }}</td>
                    <td>
                        <span class="status-badge status-{{ $student->status }}">
                            @switch($student->status)
                                @case('active') نشط @break
                                @case('graduated') متخرج @break
                                @case('transferred') منقول @break
                                @default {{ $student->status }}
                            @endswitch
                        </span>
                    </td>
                    <td>
                        @if($student->due_sum > 0)
                            <span class="badge-dues">{{ number_format($student->due_sum, 2) }}</span>
                        @else
                            <span class="badge-paid">مدفوع</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="padding: 40px; text-align: center; color: #6c757d;">
                        لا توجد بيانات للطباعة
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary -->
    @if($students->count() > 0)
    <div class="summary-box">
        <div class="row">
            <div class="col-md-4">
                <h5>عدد الطلاب</h5>
                <div class="value">{{ $students->count() }}</div>
            </div>
            <div class="col-md-4">
                <h5>إجمالي المستحقات</h5>
                <div class="value">{{ number_format($totalDue, 2) }}</div>
            </div>
   
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer-print">
        <p>تم الطباعة بواسطة: <strong>{{ auth()->user()->name }}</strong> | النظام المحاسبي للمدارس</p>
    </div>

    <script>
        // Auto print on load (optional)
        window.onload = () => window.print();
    </script>
</body>
</html>