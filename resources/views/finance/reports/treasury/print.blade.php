{{-- resources/views/finance/reports/treasury/print.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف حساب خزينة</title>
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
        }

        .print-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #925419;
        }

        .header h1 {
            color: #925419;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 16px;
            color: #666;
        }

        .summary-section {
            margin-bottom: 30px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e1e5eb;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            text-align: center;
        }

        .summary-item {
            padding: 15px;
            background: white;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }

        .summary-value {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .summary-value.deposits { color: #28a745; }
        .summary-value.withdrawals { color: #dc3545; }
        .summary-value.net { color: #925419; }

        .summary-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 12px;
        }

        .transactions-table th,
        .transactions-table td {
            padding: 8px;
            text-align: right;
            border: 1px solid #dee2e6;
        }

        .transactions-table th {
            background: #925419;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        .transactions-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .transactions-table tbody tr:hover {
            background: #e9ecef;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            color: white;
        }

        .badge-deposit { background: #28a745; }
        .badge-withdrawal { background: #dc3545; }

        .text-center { text-align: center; }
        .text-end { text-align: left; }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .text-primary { color: #925419; }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #666;
            text-align: center;
        }

        .print-date {
            margin-bottom: 10px;
        }

        .company-info {
            font-weight: bold;
            color: #925419;
        }

        @media print {
            body { margin: 0; }
            .print-container { padding: 0; max-width: 100%; }
            .summary-grid { grid-template-columns: repeat(3, 1fr); }
        }

        @page {
            margin: 15mm;
            size: A4;
        }

        .no-print {
            display: none;
        }

        .empty-message {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-container">
        <div class="header">
            <h1>كشف حساب خزينة</h1>
            <p>
                @if($selectedTreasury)
                    {{ $selectedTreasury->name }}
                @else
                    جميع الخزائن
                @endif
                | من {{ Carbon\Carbon::parse($filters['from'])->format('Y/m/d') }} 
                إلى {{ Carbon\Carbon::parse($filters['to'])->format('Y/m/d') }}
            </p>
        </div>

        <div class="summary-section">
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-value deposits">{{ number_format($report['totals']['deposits'], 2) }}</div>
                    <div class="summary-label">إجمالي الإيداعات</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value withdrawals">{{ number_format($report['totals']['withdrawals'], 2) }}</div>
                    <div class="summary-label">إجمالي السحوبات</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value net">{{ number_format($report['totals']['net'], 2) }}</div>
                    <div class="summary-label">صافي الحركة</div>
                </div>
            </div>
        </div>

        <h3 style="margin-bottom: 15px; color: #925419;">تفاصيل الحركات ({{ $report['rows']->count() }} حركة)</h3>

        @if($report['rows']->count() > 0)
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="12%">التاريخ</th>
                        <th width="25%">البيان</th>
                        <th width="12%">الخزينة</th>
                        <th width="8%">النوع</th>
                        <th width="12%">المبلغ</th>
                        <th width="15%">المستفيد</th>
                        <th width="11%">الوقت</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['rows'] as $index => $transaction)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $transaction->created_at->format('Y/m/d') }}</td>
                            <td>{{ $transaction->description ?: 'غير محدد' }}</td>
                            <td>{{ $transaction->treasury->name ?? 'غير محدد' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $transaction->transaction_type === 'deposit' ? 'badge-deposit' : 'badge-withdrawal' }}">
                                    {{ $transaction->transaction_type === 'deposit' ? 'إيداع' : 'سحب' }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format($transaction->amount, 2) }}</td>
                            <td>{{ $transaction->payee_name ?: '-' }}</td>
                            <td>{{ $transaction->created_at->format('H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-message">
                لا توجد حركات في الفترة المحددة
            </div>
        @endif

        <div class="footer">
            <div class="print-date">تاريخ الطباعة: {{ now()->format('Y/m/d H:i') }}</div>
            <div class="company-info">نظام أثر الالكتروني - التقارير المالية</div>
        </div>
    </div>
</body>
</html>