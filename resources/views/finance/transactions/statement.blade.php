<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف حساب المعاملات المالية</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: white;
        }

        .statement {
            width: 100%;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .header h2 {
            font-size: 18px;
            color: #333;
            margin-bottom: 5px;
        }

        .filters-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .filters-section h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #495057;
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 5px;
        }

        .filter-item {
            flex: 1;
            min-width: 200px;
        }

        .filter-item strong {
            color: #212529;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .transactions-table thead {
            background: #343a40;
            color: white;
        }

        .transactions-table th,
        .transactions-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: center;
        }

        .transactions-table th {
            font-weight: bold;
            font-size: 12px;
        }

        .transactions-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .transactions-table tbody tr:hover {
            background: #e9ecef;
        }

        .amount-deposit {
            color: #198754;
            font-weight: bold;
        }

        .amount-withdrawal {
            color: #dc3545;
            font-weight: bold;
        }

        .totals-section {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 5px;
        }

        .totals-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 15px;
        }

        .total-card {
            text-align: center;
            padding: 15px;
            border-radius: 5px;
            border: 2px solid;
        }

        .total-card.deposits {
            border-color: #198754;
            background: #d1e7dd;
        }

        .total-card.withdrawals {
            border-color: #dc3545;
            background: #f8d7da;
        }

        .total-card.net {
            border-color: #0d6efd;
            background: #cfe2ff;
        }

        .total-card h4 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #495057;
        }

        .total-card .amount {
            font-size: 20px;
            font-weight: bold;
        }

        .total-card.deposits .amount {
            color: #198754;
        }

        .total-card.withdrawals .amount {
            color: #dc3545;
        }

        .total-card.net .amount {
            color: #0d6efd;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }

        .footer-item {
            text-align: center;
        }

        .footer-item strong {
            display: block;
            margin-bottom: 5px;
        }

        .footer-item .line {
            border-top: 1px solid #000;
            width: 150px;
            margin: 0 auto;
        }

        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .statement {
                padding: 0;
            }
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-size: 16px;
        }

        .print-date {
            text-align: left;
            font-size: 11px;
            color: #6c757d;
            margin-bottom: 10px;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="statement">
        <div class="header">
            <h1>نظام أثر الالكتروني</h1>
            <h2>كشف حساب المعاملات المالية</h2>
        </div>

        <div class="print-date">
            تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}
        </div>

        @if(array_filter($filters))
        <div class="filters-section">
            <h3>معايير التصفية:</h3>
            <div class="filter-row">
                @if($filters['treasury'])
                    <div class="filter-item">
                        <strong>الخزينة:</strong> {{ $filters['treasury'] }}
                    </div>
                @endif
                @if($filters['transactionType'])
                    <div class="filter-item">
                        <strong>نوع المعاملة:</strong> {{ $filters['transactionType'] }}
                    </div>
                @endif
                @if($filters['transactionTypeId'])
                    <div class="filter-item">
                        <strong>التصنيف:</strong> {{ $filters['transactionTypeId'] }}
                    </div>
                @endif
            </div>
            <div class="filter-row">
                @if($filters['paymentMethod'])
                    <div class="filter-item">
                        <strong>طريقة الدفع:</strong> {{ $filters['paymentMethod'] }}
                    </div>
                @endif
                @if($filters['payeeName'])
                    <div class="filter-item">
                        <strong>اسم المستلم:</strong> {{ $filters['payeeName'] }}
                    </div>
                @endif
                @if($filters['fromDate'])
                    <div class="filter-item">
                        <strong>من تاريخ:</strong> {{ $filters['fromDate'] }}
                    </div>
                @endif
                @if($filters['toDate'])
                    <div class="filter-item">
                        <strong>إلى تاريخ:</strong> {{ $filters['toDate'] }}
                    </div>
                @endif
            </div>
        </div>
        @endif

        @if($transactions->count() > 0)
        <table class="transactions-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>التاريخ</th>
                    <th>الوقت</th>
                    <th>المستلم</th>
                    <th>النوع</th>
                    <th>التصنيف</th>
                    <th>طريقة الدفع</th>
                    <th>الخزينة</th>
                    <th>رقم المستند</th>
                    <th>المبلغ</th>
                    <th>الوصف</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $index => $transaction)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $transaction->created_at->format('Y-m-d') }}</td>
                    <td>{{ $transaction->created_at->format('H:i') }}</td>
                    <td>{{ $transaction->payee_name }}</td>
                    <td>{{ $transaction->transaction_type_name }}</td>
                    <td>{{ $transaction->transactionType->name }}</td>
                    <td>{{ $transaction->payment_method_name }}</td>
                    <td>{{ $transaction->treasury->name }}</td>
                    <td>{{ $transaction->document_number ?: '-' }}</td>
                    <td class="{{ $transaction->transaction_type === 'deposit' ? 'amount-deposit' : 'amount-withdrawal' }}">
                        {{ $transaction->transaction_type === 'deposit' ? '+' : '-' }}{{ $transaction->formatted_amount }}
                    </td>
                    <td>{{ $transaction->description ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-section">
            <h3 style="text-align: center; margin-bottom: 10px;">الإجماليات</h3>
            <div class="totals-grid">
                <div class="total-card deposits">
                    <h4>إجمالي الإيداعات</h4>
                    <div class="amount">{{ number_format($totalDeposits, 2) }} د.ل</div>
                    <small>عدد العمليات: {{ $transactions->where('transaction_type', 'deposit')->count() }}</small>
                </div>
                <div class="total-card withdrawals">
                    <h4>إجمالي السحوبات</h4>
                    <div class="amount">{{ number_format($totalWithdrawals, 2) }} د.ل</div>
                    <small>عدد العمليات: {{ $transactions->where('transaction_type', 'withdrawal')->count() }}</small>
                </div>
                <div class="total-card net">
                    <h4>الصافي</h4>
                    <div class="amount">{{ number_format($netBalance, 2) }} د.ل</div>
                    <small>إجمالي العمليات: {{ $transactions->count() }}</small>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="footer-item">
                <strong>المحاسب</strong>
                <div class="line"></div>
            </div>
            <div class="footer-item">
                <strong>المدير المالي</strong>
                <div class="line"></div>
            </div>
            <div class="footer-item">
                <strong>المدير العام</strong>
                <div class="line"></div>
            </div>
        </div>

        @else
        <div class="no-data">
            <p>لا توجد معاملات مالية وفقاً لمعايير التصفية المحددة</p>
        </div>
        @endif
    </div>
</body>
</html>
