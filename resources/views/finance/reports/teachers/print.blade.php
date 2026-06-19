{{-- resources/views/finance/reports/teachers/print.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير تسوية المعلمين</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background: white;
            font-size: 12px;
        }

        .print-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 15px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #28a745;
        }

        .header h1 {
            color: #28a745;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .header .subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .filters-info {
            background: #d4edda;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #c3e6cb;
            font-size: 11px;
            color: #155724;
            text-align: center;
        }

        .summary-section {
            margin-bottom: 25px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e1e5eb;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            text-align: center;
        }

        .summary-item {
            padding: 12px;
            background: white;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            position: relative;
        }

        .summary-item::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            opacity: 0.1;
            transform: translate(10px, -10px);
        }

        .summary-item.lessons::before { background: #17a2b8; }
        .summary-item.calculated::before { background: #925419; }
        .summary-item.settled::before { background: #28a745; }
        .summary-item.pending::before { background: #ffc107; }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .summary-value.lessons { color: #17a2b8; }
        .summary-value.calculated { color: #925419; }
        .summary-value.settled { color: #28a745; }
        .summary-value.pending { color: #ffc107; }

        .summary-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .teachers-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .teachers-table th,
        .teachers-table td {
            padding: 6px 4px;
            text-align: right;
            border: 1px solid #dee2e6;
        }

        .teachers-table th {
            background: #28a745;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }

        .teachers-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .teacher-name {
            font-weight: bold;
            color: #495057;
        }

        .teacher-initial {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #28a745;
            color: white;
            text-align: center;
            line-height: 20px;
            font-weight: bold;
            font-size: 10px;
            margin-left: 5px;
        }

        .lessons-badge {
            background: #17a2b8;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }

        .progress-bar {
            width: 100%;
            height: 12px;
            background: #e9ecef;
            border-radius: 6px;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            border-radius: 6px;
            transition: width 0.3s ease;
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 7px;
            font-weight: bold;
            color: white;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            color: white;
        }

        .status-badge.completed { background: #28a745; }
        .status-badge.pending { background: #ffc107; color: #333; }
        .status-badge.partial { background: #17a2b8; }

        .amount-cell {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 9px;
        }

        .text-center { text-align: center; }
        .text-end { text-align: left; }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            font-size: 10px;
            color: #666;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .print-info {
            text-align: left;
        }

        .company-info {
            text-align: right;
            font-weight: bold;
            color: #28a745;
        }

        .statistics-footer {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            border: 1px solid #e1e5eb;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            text-align: center;
        }

        .stat-item {
            padding: 8px;
            background: white;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #28a745;
        }

        .stat-label {
            font-size: 9px;
            color: #666;
        }

        .completion-overview {
            margin-bottom: 20px;
            background: #e7f3e7;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #c3e6cb;
        }

        .completion-title {
            font-size: 12px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 10px;
            text-align: center;
        }

        .completion-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            text-align: center;
        }

        .completion-item {
            padding: 8px;
            background: white;
            border-radius: 4px;
            border: 1px solid #c3e6cb;
        }

        .completion-number {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .completion-number.completed { color: #28a745; }
        .completion-number.pending { color: #ffc107; }
        .completion-number.average { color: #925419; }

        @media print {
            body { margin: 0; }
            .print-container { padding: 0; max-width: 100%; }
            .summary-grid { grid-template-columns: repeat(4, 1fr); }
        }

        @page {
            margin: 10mm;
            size: A4;
        }

        .empty-message {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
            font-size: 14px;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-container">
        <div class="header">
            <h1>تقرير تسوية المعلمين</h1>
            <div class="subtitle">متابعة حسابات الحصص المحسوبة والمدفوعة للمعلمين</div>
            <div class="filters-info">
                الفترة: {{ Carbon\Carbon::parse($filters['from'])->format('Y/m/d') }} - {{ Carbon\Carbon::parse($filters['to'])->format('Y/m/d') }}
                @if($filters['teacher_id'])
                    | المعلم: {{ $teachers->find($filters['teacher_id'])->name ?? 'غير محدد' }}
                @endif
                @if($filters['status'])
                    | الحالة: {{ $filters['status'] === 'pending' ? 'مستحق فقط' : 'مسدد فقط' }}
                @endif
            </div>
        </div>

        <div class="summary-section">
            <div class="summary-grid">
                <div class="summary-item lessons">
                    <div class="summary-value lessons">{{ number_format($report['totals']['total_lessons']) }}</div>
                    <div class="summary-label">إجمالي الدروس</div>
                </div>
                <div class="summary-item calculated">
                    <div class="summary-value calculated">{{ number_format($report['totals']['calculated_amount'], 2) }}</div>
                    <div class="summary-label">المبلغ المحسوب</div>
                </div>
                <div class="summary-item settled">
                    <div class="summary-value settled">{{ number_format($report['totals']['settled_amount'], 2) }}</div>
                    <div class="summary-label">المبلغ المسدد</div>
                </div>
                <div class="summary-item pending">
                    <div class="summary-value pending">{{ number_format($report['totals']['pending_amount'], 2) }}</div>
                    <div class="summary-label">المتبقي</div>
                </div>
            </div>
        </div>

        <div class="completion-overview">
            <h3 class="completion-title">نظرة عامة على حالة التسوية</h3>
            <div class="completion-stats">
                <div class="completion-item">
                    <div class="completion-number completed">{{ $report['rows']->where('pending_amount', 0)->count() }}</div>
                    <div class="stat-label">معلمين مكتملين</div>
                </div>
                <div class="completion-item">
                    <div class="completion-number pending">{{ $report['rows']->where('pending_amount', '>', 0)->count() }}</div>
                    <div class="stat-label">معلمين مستحقين</div>
                </div>
                <div class="completion-item">
                    @php
                        $avgProgress = $report['totals']['calculated_amount'] > 0 ? 
                            ($report['totals']['settled_amount'] / $report['totals']['calculated_amount']) * 100 : 0;
                    @endphp
                    <div class="completion-number average">{{ number_format($avgProgress, 1) }}%</div>
                    <div class="stat-label">متوسط الإنجاز</div>
                </div>
            </div>
        </div>

        <h3 style="margin-bottom: 10px; color: #28a745; font-size: 14px;">
            تفاصيل التسويات ({{ $report['rows']->count() }} معلم)
        </h3>

        @if($report['rows']->count() > 0)
            <table class="teachers-table">
                <thead>
                    <tr>
                        <th style="width: 4%">#</th>
                        <th style="width: 22%">المعلم</th>
                        <th style="width: 8%">الدروس</th>
                        <th style="width: 14%">محسوب</th>
                        <th style="width: 14%">مسدد</th>
                        <th style="width: 14%">متبقي</th>
                        <th style="width: 14%">نسبة التقدم</th>
                        <th style="width: 10%">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['rows'] as $index => $teacher)
                        @php
                            $progress = $teacher['calculated_amount'] > 0 ? ($teacher['settled_amount'] / $teacher['calculated_amount']) * 100 : 0;
                            $status = $teacher['pending_amount'] == 0 ? 'completed' : ($teacher['settled_amount'] > 0 ? 'partial' : 'pending');
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <span class="teacher-initial">{{ substr($teacher['name'], 0, 1) }}</span>
                                <span class="teacher-name">{{ $teacher['name'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="lessons-badge">{{ number_format($teacher['total_lessons']) }}</span>
                            </td>
                            <td class="text-end amount-cell">{{ number_format($teacher['calculated_amount'], 2) }}</td>
                            <td class="text-end amount-cell" style="color: #28a745;">{{ number_format($teacher['settled_amount'], 2) }}</td>
                            <td class="text-end amount-cell" style="color: #ffc107;">{{ number_format($teacher['pending_amount'], 2) }}</td>
                            <td class="text-center">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $progress }}%"></div>
                                    <div class="progress-text">{{ number_format($progress, 0) }}%</div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($status === 'completed')
                                    <span class="status-badge completed">مكتمل</span>
                                @elseif($status === 'partial')
                                    <span class="status-badge partial">جزئي</span>
                                @else
                                    <span class="status-badge pending">مستحق</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="statistics-footer">
                <h4 style="margin-bottom: 10px; color: #28a745; font-size: 12px;">إحصائيات مفصلة</h4>
                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($report['rows']->avg('calculated_amount'), 2) }}</div>
                        <div class="stat-label">متوسط المبلغ المحسوب</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($report['rows']->avg('total_lessons'), 0) }}</div>
                        <div class="stat-label">متوسط الدروس</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($report['rows']->where('pending_amount', '>', 0)->avg('pending_amount'), 2) }}</div>
                        <div class="stat-label">متوسط المتبقي</div>
                    </div>
                </div>
            </div>
        @else
            <div class="empty-message">
                لا توجد بيانات تسويات للمعلمين في الفترة والمعايير المحددة
            </div>
        @endif

        <div class="footer">
            <div class="footer-grid">
                <div class="print-info">
                    <div><strong>تاريخ الطباعة:</strong> {{ now()->format('Y/m/d H:i') }}</div>
                    <div><strong>المستخدم:</strong> {{ auth()->user()->name ?? 'غير محدد' }}</div>
                    <div><strong>إجمالي المعلمين:</strong> {{ $report['rows']->count() }}</div>
                </div>
                <div class="company-info">
                    <div>نظام أثر الالكتروني</div>
                    <div>التقارير المالية - قسم المعلمين</div>
                    <div style="color: #666; font-size: 9px; font-weight: normal;">
                        تقرير سري للاستخدام الداخلي
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>