{{-- resources/views/finance/reports/students/print.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير مدفوعات الطلاب</title>
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
            border-bottom: 3px solid #ffc107;
        }

        .header h1 {
            color: #ffc107;
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
            background: #fff3cd;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ffeaa7;
            font-size: 11px;
            color: #856404;
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
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .summary-value.total { color: #28a745; }
        .summary-value.count { color: #17a2b8; }
        .summary-value.average { color: #925419; }
        .summary-value.methods { color: #ffc107; }

        .summary-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .methods-section {
            margin-bottom: 20px;
        }

        .methods-title {
            font-size: 14px;
            font-weight: bold;
            color: #925419;
            margin-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }

        .methods-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 8px;
        }

        .method-item {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 8px;
            text-align: center;
        }

        .method-name {
            font-size: 10px;
            color: #666;
            margin-bottom: 3px;
        }

        .method-amount {
            font-weight: bold;
            font-size: 11px;
            color: #925419;
        }

        .method-count {
            font-size: 9px;
            color: #999;
        }

        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .payments-table th,
        .payments-table td {
            padding: 6px 4px;
            text-align: right;
            border: 1px solid #dee2e6;
        }

        .payments-table th {
            background: #ffc107;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }

        .payments-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .payments-table tbody tr:hover {
            background: #e9ecef;
        }

        .student-name {
            font-weight: bold;
            color: #495057;
        }

        .installment-info {
            font-size: 9px;
            color: #666;
        }

        .payment-method {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            color: white;
        }

        .payment-method.cash { background: #28a745; }
        .payment-method.pos { background: #17a2b8; }
        .payment-method.bank { background: #925419; }
        .payment-method.transfer { background: #ffc107; color: #333; }
        .payment-method.other { background: #dc3545; }

        .amount-highlight {
            font-weight: bold;
            color: #28a745;
            font-family: 'Courier New', monospace;
        }

        .transaction-id {
            font-family: monospace;
            font-size: 8px;
            color: #666;
            background: #f8f9fa;
            padding: 1px 4px;
            border-radius: 2px;
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
            color: #925419;
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
            grid-template-columns: repeat(4, 1fr);
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
            font-size: 12px;
            font-weight: bold;
            color: #925419;
        }

        .stat-label {
            font-size: 9px;
            color: #666;
        }

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

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-container">
        <div class="header">
            <h1>تقرير مدفوعات الطلاب</h1>
            <div class="subtitle">تتبع جميع مدفوعات الأقساط والرسوم المدرسية</div>
            <div class="filters-info">
                الفترة: {{ Carbon\Carbon::parse($filters['from'])->format('Y/m/d') }} - {{ Carbon\Carbon::parse($filters['to'])->format('Y/m/d') }}
                @if($filters['treasury_id'])
                    | الخزينة: {{ $treasuries->find($filters['treasury_id'])->name ?? 'غير محدد' }}
                @endif
                @if($filters['payment_method'])
                    | طريقة الدفع: {{ $paymentMethods[$filters['payment_method']] }}
                @endif
                @if($filters['student_search'])
                    | البحث: {{ $filters['student_search'] }}
                @endif
            </div>
        </div>

        <div class="summary-section">
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-value total">{{ number_format($report['totals']['amount'], 2) }}</div>
                    <div class="summary-label">إجمالي المدفوعات</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value count">{{ number_format($report['totals']['count']) }}</div>
                    <div class="summary-label">عدد العمليات</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value average">
                        {{ $report['totals']['count'] > 0 ? number_format($report['totals']['amount'] / $report['totals']['count'], 2) : '0.00' }}
                    </div>
                    <div class="summary-label">متوسط الدفعة</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value methods">{{ $report['rows']->pluck('student_id')->unique()->count() }}</div>
                    <div class="summary-label">طلاب مختلفين</div>
                </div>
            </div>
        </div>

        @if(count($report['totals']['by_method'] ?? []) > 0)
            <div class="methods-section">
                <h3 class="methods-title">التوزيع حسب طريقة الدفع</h3>
                <div class="methods-grid">
                    @foreach($report['totals']['by_method'] as $method => $amount)
                        @if($amount > 0)
                            <div class="method-item">
                                <div class="method-name">{{ $paymentMethods[$method] ?? $method }}</div>
                                <div class="method-amount">{{ number_format($amount, 2) }}</div>
                                <div class="method-count">{{ $report['rows']->where('payment_method', $method)->count() }} عملية</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        <h3 style="margin-bottom: 10px; color: #925419; font-size: 14px;">
            تفاصيل المدفوعات ({{ $report['rows']->count() }} دفعة)
        </h3>

        @if($report['rows']->count() > 0)
            <table class="payments-table">
                <thead>
                    <tr>
                        <th style="width: 4%">#</th>
                        <th style="width: 10%">التاريخ</th>
                        <th style="width: 18%">الطالب</th>
                        <th style="width: 15%">القسط</th>
                        <th style="width: 10%">الطريقة</th>
                        <th style="width: 12%">الخزينة</th>
                        <th style="width: 12%">المبلغ</th>
                        <th style="width: 10%">المعاملة</th>
                        <th style="width: 7%">الوقت</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['rows'] as $index => $payment)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $payment->created_at?->format('Y/m/d') }}</td>
                            <td>
                                <div class="student-name">{{ $payment->student?->name ?? 'غير محدد' }}</div>
                            </td>
                            <td>
                                <div style="font-size: 9px;">
                                    <strong>{{ $payment->installment->installmentType?->name }}</strong>
                                    @if($payment->installment?->semester_number)
                                        <br><span class="installment-info">الفصل {{ $payment->installment->semester_number }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                @php
                                    $methodMap = [
                                        'cash' => 'نقدي',
                                        'pos' => 'نقاط بيع', 
                                        'bank' => 'إيداع بنكي',
                                        'transfer' => 'تحويل',
                                        'other' => 'أخرى'
                                    ];
                                @endphp
                                <span class="payment-method {{ $payment->payment_method }}">
                                    {{ $methodMap[$payment->payment_method] ?? $payment->payment_method }}
                                </span>
                            </td>
                            <td>{{ $payment->treasury?->name ?? 'غير محدد' }}</td>
                            <td class="text-end">
                                <span class="amount-highlight">{{ number_format($payment->amount, 2) }}</span>
                            </td>
                            <td class="text-center">
                                @if($payment->transaction_id)
                                    <span class="transaction-id">{{ $payment->transaction_id }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-center">{{ $payment->created_at?->format('H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="statistics-footer">
                <h4 style="margin-bottom: 10px; color: #925419; font-size: 12px;">إحصائيات إضافية</h4>
                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($report['rows']->max('amount'), 2) }}</div>
                        <div class="stat-label">أعلى دفعة</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($report['rows']->min('amount'), 2) }}</div>
                        <div class="stat-label">أقل دفعة</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $report['rows']->pluck('treasury_id')->unique()->count() }}</div>
                        <div class="stat-label">خزائن مستخدمة</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">
                            {{ $report['rows']->groupBy(function($item) { return $item->created_at->format('Y-m-d'); })->count() }}
                        </div>
                        <div class="stat-label">أيام نشطة</div>
                    </div>
                </div>
            </div>
        @else
            <div class="empty-message">
                لا توجد مدفوعات للطلاب في الفترة والمعايير المحددة
            </div>
        @endif

        <div class="footer">
            <div class="footer-grid">
                <div class="print-info">
                    <div><strong>تاريخ الطباعة:</strong> {{ now()->format('Y/m/d H:i') }}</div>
                    <div><strong>المستخدم:</strong> {{ auth()->user()->name ?? 'غير محدد' }}</div>
                    <div><strong>عدد الصفحات:</strong> <span id="pageCount">1</span></div>
                </div>
                <div class="company-info">
                    <div>نظام أثر الالكتروني</div>
                    <div>التقارير المالية - قسم الطلاب</div>
                    <div style="color: #666; font-size: 9px; font-weight: normal;">
                        هذا التقرير سري ومخصص للاستخدام الداخلي فقط
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // إضافة ترقيم الصفحات للطباعة
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>