{{-- resources/views/finance/employee-balances/print.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف أرصدة الموظفين</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            direction: rtl;
            padding: 20px;
            background: white;
        }

        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #925419;
            padding-bottom: 20px;
        }

        .print-header h1 {
            color: #925419;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .print-header .subtitle {
            color: #666;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .print-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .print-info-item {
            font-size: 14px;
        }

        .print-info-item strong {
            color: #925419;
        }

        .filters-applied {
            background: #fff3cd;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-right: 4px solid #ffc107;
        }

        .filters-applied h3 {
            font-size: 14px;
            color: #856404;
            margin-bottom: 8px;
        }

        .filters-applied p {
            font-size: 13px;
            color: #856404;
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 13px;
        }

        thead {
            background: #925419;
            color: white;
        }

        th {
            padding: 12px 8px;
            text-align: right;
            font-weight: bold;
            border: 1px solid #ddd;
        }

        td {
            padding: 10px 8px;
            border: 1px solid #ddd;
            text-align: right;
        }

        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        tbody tr:hover {
            background: #e9ecef;
        }

        .employee-name {
            font-weight: bold;
            color: #333;
        }

        .employee-meta {
            font-size: 11px;
            color: #666;
            margin-top: 3px;
        }

        .balance-positive {
            color: #28a745;
            font-weight: bold;
        }

        .balance-negative {
            color: #dc3545;
            font-weight: bold;
        }

        .balance-zero {
            color: #6c757d;
            font-weight: bold;
        }

        .advances-total {
            color: #ffc107;
            font-weight: bold;
        }

        .role-badge {
            display: inline-block;
            background: #e9ecef;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            color: #666;
            margin: 2px;
        }

        .summary-section {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-top: 3px solid #925419;
        }

        .summary-section h3 {
            color: #925419;
            font-size: 18px;
            margin-bottom: 15px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .summary-item {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        .summary-item .label {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
        }

        .summary-item .value {
            font-size: 20px;
            font-weight: bold;
            color: #925419;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 12px;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 16px;
        }

        @media print {
            body {
                padding: 10px;
            }

            .print-header {
                margin-bottom: 20px;
                padding-bottom: 15px;
            }

            .summary-section {
                page-break-inside: avoid;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }
        }

        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="print-header">
        <h1>كشف أرصدة الموظفين</h1>
        <div class="subtitle">تقرير شامل بأرصدة الموظفين والسلف</div>
        <div class="subtitle">تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}</div>
    </div>

    <!-- Print Info -->
    <div class="print-info">
        <div class="print-info-item">
            <strong>عدد الموظفين:</strong> {{ $users->total() }}
        </div>
        <div class="print-info-item">
            <strong>الصفحة:</strong> {{ $users->currentPage() }} من {{ $users->lastPage() }}
        </div>
        <div class="print-info-item">
            <strong>طُبع بواسطة:</strong> {{ auth()->user()->name }}
        </div>
    </div>

    <!-- Filters Applied -->
    @if(request('search') || request('role'))
    <div class="filters-applied">
        <h3>الفلاتر المطبقة:</h3>
        @if(request('search'))
            <p><strong>البحث:</strong> {{ request('search') }}</p>
        @endif
        @if(request('role'))
            @php
                $roleObj = $roles->firstWhere('name', request('role'));
            @endphp
            <p><strong>الدور:</strong> {{ $roleObj ? $roleObj->display_name : request('role') }}</p>
        @endif
    </div>
    @endif

    <!-- Employees Table -->
    @if($users->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 30%;">الموظف</th>
                <th style="width: 20%;">الدور</th>
                <th style="width: 15%;">الرصيد الحالي</th>
                <th style="width: 15%;">إجمالي السلف</th>
                <th style="width: 15%;">الصافي</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalBalance = 0;
                $totalAdvances = 0;
                $counter = ($users->currentPage() - 1) * $users->perPage();
            @endphp
            @foreach($users as $user)
                @php
                    $counter++;
                    $balance = $user->balance;
                    $advances = $user->total_advances ?? 0;
                    $net = $balance - $advances;
                    
                    $totalBalance += $balance;
                    $totalAdvances += $advances;
                @endphp
                <tr>
                    <td>{{ $counter }}</td>
                    <td>
                        <div class="employee-name">{{ $user->name }}</div>
                        <div class="employee-meta">
                            {{ $user->email }}
                            @if($user->code)
                                • {{ $user->code }}
                            @endif
                        </div>
                    </td>
                    <td>
                        @foreach($user->roles as $role)
                            <span class="role-badge">{{ $role->display_name }}</span>
                        @endforeach
                    </td>
                    <td>
                        <span class="{{ $balance > 0 ? 'balance-positive' : ($balance < 0 ? 'balance-negative' : 'balance-zero') }}">
                            {{ number_format($balance, 2) }} د.ل
                        </span>
                    </td>
                    <td>
                        <span class="advances-total">
                            {{ number_format($advances, 2) }} د.ل
                        </span>
                    </td>
                    <td>
                        <span class="{{ $net > 0 ? 'balance-positive' : ($net < 0 ? 'balance-negative' : 'balance-zero') }}">
                            {{ number_format($net, 2) }} د.ل
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #e9ecef; font-weight: bold;">
                <td colspan="3" style="text-align: center;">الإجمالي</td>
                <td>
                    <span class="{{ $totalBalance > 0 ? 'balance-positive' : ($totalBalance < 0 ? 'balance-negative' : 'balance-zero') }}">
                        {{ number_format($totalBalance, 2) }} د.ل
                    </span>
                </td>
                <td>
                    <span class="advances-total">
                        {{ number_format($totalAdvances, 2) }} د.ل
                    </span>
                </td>
                <td>
                    <span class="{{ ($totalBalance - $totalAdvances) > 0 ? 'balance-positive' : (($totalBalance - $totalAdvances) < 0 ? 'balance-negative' : 'balance-zero') }}">
                        {{ number_format($totalBalance - $totalAdvances, 2) }} د.ل
                    </span>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3>ملخص التقرير</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">إجمالي الأرصدة</div>
                <div class="value">{{ number_format($totalBalance, 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">إجمالي السلف</div>
                <div class="value" style="color: #ffc107;">{{ number_format($totalAdvances, 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">الصافي</div>
                <div class="value" style="color: {{ ($totalBalance - $totalAdvances) > 0 ? '#28a745' : '#dc3545' }};">
                    {{ number_format($totalBalance - $totalAdvances, 2) }}
                </div>
            </div>
            <div class="summary-item">
                <div class="label">عدد الموظفين</div>
                <div class="value">{{ $users->total() }}</div>
            </div>
        </div>
    </div>
    @else
    <div class="no-data">
        <p>لا توجد بيانات للطباعة</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>تم إنشاء هذا التقرير آلياً بواسطة نظام أثر الالكتروني</p>
        <p>{{ config('app.name') }} © {{ date('Y') }}</p>
    </div>

    <script>
        // طباعة تلقائية عند فتح الصفحة
        window.onload = function() {
            window.print();
        };

        // إغلاق النافذة بعد الطباعة أو الإلغاء
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>