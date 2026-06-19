{{-- resources/views/finance/reports/employee/print.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف حساب الموظف - {{ $employee->name }}</title>
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
            border-bottom: 3px solid #925419;
        }

        .header h1 {
            color: #925419;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .employee-info {
            background: rgba(146, 84, 25, 0.1);
            padding: 12px;
            border-radius: 6px;
            border: 1px solid rgba(146, 84, 25, 0.2);
            margin: 10px 0;
        }

        .employee-name {
            font-size: 18px;
            font-weight: bold;
            color: #925419;
            margin-bottom: 5px;
        }

        .employee-details {
            font-size: 11px;
            color: #6c4311;
        }

        .period-info {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
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
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            text-align: center;
        }

        .summary-item {
            padding: 12px;
            background: white;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            position: relative;
            overflow: hidden;
        }

        .summary-item::before {
            content: '';
            position: absolute;
            top: -15px;
            right: -15px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            opacity: 0.1;
        }

        .summary-item.received::before { background: #28a745; }
        .summary-item.deductions::before { background: #dc3545; }
        .summary-item.net::before { background: #925419; }

        .summary-icon {
            font-size: 16px;
            margin-bottom: 5px;
            opacity: 0.7;
        }

        .summary-item.received .summary-icon { color: #28a745; }
        .summary-item.deductions .summary-icon { color: #dc3545; }
        .summary-item.net .summary-icon { color: #925419; }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .summary-value.received { color: #28a745; }
        .summary-value.deductions { color: #dc3545; }
        .summary-value.net { color: #925419; }

        .summary-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .transactions-table th,
        .transactions-table td {
            padding: 6px 4px;
            text-align: right;
            border: 1px solid #dee2e6;
        }

        .transactions-table th {
            background: #925419;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }

        .transactions-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .transaction-type {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            border: 1px solid transparent;
        }

        .transaction-type.salary {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border-color: rgba(40, 167, 69, 0.2);
        }

        .transaction-type.deduction {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-color: rgba(220, 53, 69, 0.2);
        }

        .transaction-type.bonus {
            background: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
            border-color: rgba(23, 162, 184, 0.2);
        }

        .amount-positive {
            color: #28a745;
            font-weight: bold;
        }

        .amount-negative {
            color: #dc3545;
            font-weight: bold;
        }

        .amount-cell {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 10px;
        }

        .operation-icon {
            font-size: 12px;
            font-weight: bold;
        }

        .operation-icon.positive { color: #28a745; }
        .operation-icon.negative { color: #dc3545; }

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

        .summary-cards {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            border: 1px solid #e1e5eb;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            text-align: center;
        }

        .card-item {
            padding: 10px;
            background: white;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .card-title {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }

        .card-value {
            font-size: 14px;
            font-weight: bold;
            color: #925419;
        }

        .employee-summary {
            background: #e6f3ff;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #b3d9ff;
            margin-bottom: 15px;
        }

        .employee-summary h4 {
            color: #0066cc;
            font-size: 12px;
            margin-bottom: 8px;
            text-align: center;
        }

        .summary-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            font-size: 10px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            border-bottom: 1px dotted #ccc;
        }

        .detail-label {
            color: #666;
        }

        .detail-value {
            font-weight: bold;
            color: #333;
        }

        @media print {
            body { margin: 0; }
            .print-container { padding: 0; max-width: 100%; }
            .summary-grid { grid-template-columns: repeat(3, 1fr); }
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

        .confidential-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 8px;
            border-radius: 4px;
            font-size: 9px;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-container">
        <div class="header">
            <h1>كشف حساب الموظف</h1>
            <div class="employee-info">
                <div class="employee-name">{{ $employee->name }}</div>
                <div class="employee-details">
                    البريد الإلكتروني: {{ $employee->email }}
                    @if($employee->phone)
                        | الهاتف: {{ $employee->phone }}
                    @endif
                </div>
            </div>
            <div class="period-info">
                من {{ Carbon\Carbon::parse($filters['from'])->format('Y/m/d') }} 
                إلى {{ Carbon\Carbon::parse($filters['to'])->format('Y/m/d') }}
            </div>
        </div>


        <div class="employee-summary">
            <h4>ملخص حساب الموظف</h4>
            <div class="summary-details">
                <div class="detail-item">
                    <span class="detail-label">عدد المعاملات:</span>
                    <span class="detail-value">{{ $report['rows']->count() }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">معاملات الاستلام:</span>
                    <span class="detail-value">{{ $report['rows']->where('transaction_type', 'withdrawal')->count() }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">معاملات الخصم:</span>
                    <span class="detail-value">{{ $report['rows']->where('transaction_type', 'deposit')->count() }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">تاريخ التقرير:</span>
                    <span class="detail-value">{{ now()->format('Y/m/d') }}</span>
                </div>
            </div>
        </div>

        <h3 style="margin-bottom: 10px; color: #925419; font-size: 14px;">
            تفاصيل الحركات المالية ({{ $report['rows']->count() }} حركة)
        </h3>

        @if($report['rows']->count() > 0)
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th style="width: 4%">#</th>
                        <th style="width: 12%">التاريخ</th>
                        <th style="width: 25%">البيان</th>
                        <th style="width: 12%">النوع</th>
                        <th style="width: 15%">الخزينة</th>
                        <th style="width: 15%">المبلغ</th>
                        <th style="width: 5%">العملية</th>
                        <th style="width: 12%">الوقت</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['rows'] as $index => $transaction)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $transaction->created_at->format('Y/m/d') }}</td>
                            <td>{{ $transaction->description ?: 'غير محدد' }}</td>
                            <td class="text-center">
                                @php
                                    $type = 'salary';
                                    $typeLabel = 'راتب';
                                    
                                    if (str_contains(strtolower($transaction->description), 'خصم')) {
                                        $type = 'deduction';
                                        $typeLabel = 'خصم';
                                    } elseif (str_contains(strtolower($transaction->description), 'بدل')) {
                                        $type = 'bonus';
                                        $typeLabel = 'بدل';
                                    }
                                @endphp
                                <span class="transaction-type {{ $type }}">
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td>{{ $transaction->treasury->name ?? 'غير محدد' }}</td>
                            <td class="text-end amount-cell">
                                <span class="{{ $transaction->transaction_type === 'withdrawal' ? 'amount-positive' : 'amount-negative' }}">
                                    {{ $transaction->transaction_type === 'withdrawal' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($transaction->transaction_type === 'withdrawal')
                                    <span class="operation-icon positive">↓</span>
                                @else
                                    <span class="operation-icon negative">↑</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $transaction->created_at->format('H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="summary-cards">
                <h4 style="margin-bottom: 10px; color: #925419; font-size: 12px; text-align: center;">إحصائيات إضافية</h4>
                <div class="cards-grid">
                    <div class="card-item">
                        <div class="card-title">أكبر مبلغ مستلم</div>
                        <div class="card-value" style="color: #28a745;">
                            {{ number_format($report['rows']->where('transaction_type', 'withdrawal')->max('amount'), 2) }}
                        </div>
                    </div>
                    <div class="card-item">
                        <div class="card-title">أكبر خصم</div>
                        <div class="card-value" style="color: #dc3545;">
                            {{ number_format($report['rows']->where('transaction_type', 'deposit')->max('amount'), 2) }}
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="empty-message">
                لا توجد حركات مالية للموظف {{ $employee->name }} في الفترة المحددة
            </div>
        @endif

        <div class="confidential-notice">
            🔒 هذا المستند سري وشخصي ومخصص للموظف المذكور والإدارة المالية فقط
        </div>

        <div class="footer">
            <div class="footer-grid">
                <div class="print-info">
                    <div><strong>تاريخ الطباعة:</strong> {{ now()->format('Y/m/d H:i') }}</div>
                    <div><strong>طُبع بواسطة:</strong> {{ auth()->user()->name ?? 'غير محدد' }}</div>
                    <div><strong>رقم الموظف:</strong> {{ $employee->id }}</div>
                </div>
                <div class="company-info">
                    <div>نظام أثر الالكتروني</div>
                    <div>التقارير المالية - شؤون الموظفين</div>
                    <div style="color: #666; font-size: 9px; font-weight: normal;">
                        كشف حساب شخصي وسري
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // طباعة تلقائية عند تحميل الصفحة
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>