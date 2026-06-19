{{-- resources/views/finance/reports/students/view.blade.php --}}
@extends('layouts.app')

@section('title', 'تقرير مدفوعات الطلاب')

@push('styles')
<style>
    :root {
        --primary: #925419;
        --success: #28a745;
        --warning: #ffc107;
        --danger: #dc3545;
        --info: #17a2b8;
        --light: #f8f9fa;
        --border: #e1e5eb;
        --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .report-header {
        background: linear-gradient(135deg, var(--warning), #d39e00);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        text-align: center;
    }

    .report-title {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }

    .report-subtitle {
        opacity: 0.9;
        font-size: 1.1rem;
    }

    .filters-summary {
        background: rgba(255, 255, 255, 0.1);
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
        font-size: 0.9rem;
    }

    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border-left: 4px solid;
        box-shadow: var(--shadow);
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: -20px;
        right: -20px;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        opacity: 0.1;
    }

    .stats-card.total { 
        border-left-color: var(--success);
    }
    .stats-card.total::before { background: var(--success); }

    .stats-card.count { 
        border-left-color: var(--info);
    }
    .stats-card.count::before { background: var(--info); }

    .stats-card.average { 
        border-left-color: var(--primary);
    }
    .stats-card.average::before { background: var(--primary); }

    .stats-card.today { 
        border-left-color: var(--warning);
    }
    .stats-card.today::before { background: var(--warning); }

    .stats-icon {
        font-size: 2rem;
        margin-bottom: 1rem;
        opacity: 0.7;
    }

    .stats-card.total .stats-icon { color: var(--success); }
    .stats-card.count .stats-icon { color: var(--info); }
    .stats-card.average .stats-icon { color: var(--primary); }
    .stats-card.today .stats-icon { color: var(--warning); }

    .stats-value {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .stats-label {
        color: #6c757d;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .payment-methods-chart {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
    }

    .method-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        background: var(--light);
        border: 1px solid var(--border);
    }

    .method-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .method-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.9rem;
    }

    .method-icon.cash { background: var(--success); }
    .method-icon.pos { background: var(--info); }
    .method-icon.bank { background: var(--primary); }
    .method-icon.transfer { background: var(--warning); }
    .method-icon.other { background: var(--danger); }

    .method-amount {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary);
    }

    .report-table-container {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow);
    }

    .table-header {
        background: var(--light);
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--primary);
        margin: 0;
    }

    .table-responsive {
        max-height: 600px;
        overflow-y: auto;
    }

    .table {
        margin: 0;
    }

    .table th {
        background: var(--light);
        border-top: none;
        font-weight: 600;
        color: #495057;
        position: sticky;
        top: 0;
        z-index: 10;
        white-space: nowrap;
    }

    .table td {
        vertical-align: middle;
    }

    .student-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: var(--warning);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 0.8rem;
        margin-left: 0.5rem;
    }

    .student-info {
        display: flex;
        align-items: center;
    }

    .student-name {
        font-weight: 600;
        color: #495057;
    }

    .payment-method-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        border: 1px solid transparent;
    }

    .payment-method-badge.cash {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success);
        border-color: rgba(40, 167, 69, 0.2);
    }

    .payment-method-badge.pos {
        background: rgba(23, 162, 184, 0.1);
        color: var(--info);
        border-color: rgba(23, 162, 184, 0.2);
    }

    .payment-method-badge.bank {
        background: rgba(146, 84, 25, 0.1);
        color: var(--primary);
        border-color: rgba(146, 84, 25, 0.2);
    }

    .payment-method-badge.transfer {
        background: rgba(255, 193, 7, 0.1);
        color: #856404;
        border-color: rgba(255, 193, 7, 0.2);
    }

    .payment-method-badge.other {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger);
        border-color: rgba(220, 53, 69, 0.2);
    }

    .installment-info {
        font-size: 0.85rem;
        color: #6c757d;
    }

    .amount-highlight {
        font-family: 'Courier New', monospace;
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--success);
    }

    .transaction-id {
        font-family: monospace;
        font-size: 0.8rem;
        color: #6c757d;
        background: var(--light);
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
    }

    .actions-bar {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        justify-content: space-between;
        align-items: center;
    }

    .filters-display {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-tag {
        background: var(--light);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        border: 1px solid var(--border);
        color: #495057;
    }

    .btn-group {
        display: flex;
        gap: 0.5rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary { background: var(--primary); color: white; }
    .btn-success { background: var(--success); color: white; }
    .btn-secondary { background: #6c757d; color: white; }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow);
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .stats-cards {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .actions-bar {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }
        
        .filters-display {
            justify-content: center;
        }
        
        .btn-group {
            width: 100%;
        }
        
        .btn {
            flex: 1;
            justify-content: center;
        }

        .student-avatar {
            width: 30px;
            height: 30px;
            font-size: 0.7rem;
        }

        .table th, .table td {
            font-size: 0.85rem;
        }
    }

    @media print {
        .actions-bar, .btn-group { display: none !important; }
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="report-header">
        <h1 class="report-title">تقرير مدفوعات الطلاب</h1>
        <p class="report-subtitle">تتبع جميع مدفوعات الأقساط والرسوم المدرسية</p>
        <div class="filters-summary">
            الفترة: {{ Carbon\Carbon::parse($filters['from'])->format('Y/m/d') }} - {{ Carbon\Carbon::parse($filters['to'])->format('Y/m/d') }}
            @if($filters['treasury_id'])
                | خزينة محددة
            @endif
            @if($filters['payment_method'])
                | طريقة دفع: {{ $paymentMethods[$filters['payment_method']] }}
            @endif
            @if($filters['student_search'])
                | بحث: {{ $filters['student_search'] }}
            @endif
        </div>
    </div>

    <div class="actions-bar">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('finance.reports.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-right"></i>العودة للتقارير
            </a>
            <div class="filters-display">
                <span class="filter-tag">{{ $report['totals']['count'] }} عملية</span>
                @if($filters['payment_method'])
                    <span class="filter-tag">{{ $paymentMethods[$filters['payment_method']] }}</span>
                @endif
                @if($filters['student_search'])
                    <span class="filter-tag">{{ $filters['student_search'] }}</span>
                @endif
            </div>
        </div>
        <div class="btn-group">
            <a href="{{ request()->fullUrlWithQuery(['format' => 'print']) }}" 
               class="btn btn-success" target="_blank">
                <i class="fa fa-print"></i>طباعة
            </a>
           
        </div>
    </div>

    <div class="stats-cards">
        <div class="stats-card total">
            <div class="stats-icon">
                <i class="fa fa-money-bill-wave"></i>
            </div>
            <div class="stats-value text-success">{{ number_format($report['totals']['amount'], 2) }}</div>
            <div class="stats-label">إجمالي المدفوعات</div>
        </div>
        <div class="stats-card count">
            <div class="stats-icon">
                <i class="fa fa-list-ol"></i>
            </div>
            <div class="stats-value" style="color: var(--info)">{{ number_format($report['totals']['count']) }}</div>
            <div class="stats-label">عدد العمليات</div>
        </div>
        <div class="stats-card average">
            <div class="stats-icon">
                <i class="fa fa-chart-line"></i>
            </div>
            <div class="stats-value" style="color: var(--primary)">
                {{ $report['totals']['count'] > 0 ? number_format($report['totals']['amount'] / $report['totals']['count'], 2) : '0.00' }}
            </div>
            <div class="stats-label">متوسط الدفعة</div>
        </div>
        <div class="stats-card today">
            <div class="stats-icon">
                <i class="fa fa-calendar-day"></i>
            </div>
            <div class="stats-value" style="color: var(--warning)">
                {{ number_format($report['rows']->where('created_at.date', today()->format('Y-m-d'))->sum('amount'), 2) }}
            </div>
            <div class="stats-label">مدفوعات اليوم</div>
        </div>
    </div>

    @if(count($report['totals']['by_method'] ?? []) > 0)
        <div class="payment-methods-chart">
            <h5 class="mb-3 text-primary">التوزيع حسب طريقة الدفع</h5>
            @foreach($report['totals']['by_method'] as $method => $amount)
                @if($amount > 0)
                    <div class="method-item">
                        <div class="method-info">
                            <div class="method-icon {{ $method }}">
                                <i class="fa fa-{{ $method === 'cash' ? 'money-bill' : ($method === 'pos' ? 'credit-card' : ($method === 'bank' ? 'university' : ($method === 'transfer' ? 'exchange-alt' : 'ellipsis-h'))) }}"></i>
                            </div>
                            <div>
                                <div class="fw-bold">{{ $paymentMethods[$method] ?? $method }}</div>
                                <small class="text-muted">
                                    {{ $report['rows']->where('payment_method', $method)->count() }} عملية
                                </small>
                            </div>
                        </div>
                        <div class="method-amount">{{ number_format($amount, 2) }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <div class="report-table-container">
        <div class="table-header">
            <h3 class="table-title">تفاصيل المدفوعات</h3>
            <span class="badge bg-warning text-dark">{{ $report['rows']->count() }} دفعة</span>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="4%">#</th>
                        <th width="12%">التاريخ</th>
                        <th width="20%">الطالب</th>
                        <th width="15%">القسط</th>
                        <th width="10%">الطريقة</th>
                        <th width="12%">الخزينة</th>
                        <th width="12%" class="text-end">المبلغ</th>
                        <th width="10%">معرف المعاملة</th>
                        <th width="5%">الوقت</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['rows'] as $index => $payment)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $payment->created_at?->format('Y/m/d') }}</td>
                            <td>
                                <div class="student-info">
                                    <div class="student-avatar">
                                        {{ substr($payment->student?->name ?? 'غ', 0, 1) }}
                                    </div>
                                    <div class="student-name">{{ $payment->student?->name ?? 'غير محدد' }}</div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="fw-bold">{{ $payment->installment?->installmentType?->name }}</div>
                                    @if($payment->installment?->semester_number)
                                        <div class="installment-info">الفصل {{ $payment->installment->semester_number }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                    $methodMap = [
                                        'cash' => 'نقدي',
                                        'pos' => 'نقاط بيع', 
                                        'bank' => 'إيداع بنكي',
                                        'transfer' => 'تحويل',
                                        'other' => 'أخرى'
                                    ];
                                @endphp
                                <span class="payment-method-badge {{ $payment->payment_method }}">
                                    <i class="fa fa-{{ $payment->payment_method === 'cash' ? 'money-bill' : ($payment->payment_method === 'pos' ? 'credit-card' : ($payment->payment_method === 'bank' ? 'university' : ($payment->payment_method === 'transfer' ? 'exchange-alt' : 'ellipsis-h'))) }}"></i>
                                    {{ $methodMap[$payment->payment_method] ?? $payment->payment_method }}
                                </span>
                            </td>
                            <td>{{ $payment->treasury?->name ?? 'غير محدد' }}</td>
                            <td class="text-end">
                                <span class="amount-highlight">{{ number_format($payment->amount, 2) }}</span>
                            </td>
                            <td>
                                @if($payment->transaction_id)
                                    <span class="transaction-id">TX-{{ $payment->transaction_id }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $payment->created_at?->format('H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fa fa-graduation-cap"></i>
                                    <p>لا توجد مدفوعات للطلاب في الفترة المحددة</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($report['rows']->count() > 0)
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">أعلى دفعة</h6>
                    </div>
                    <div class="card-body">
                        <h4 class="text-success">{{ number_format($report['rows']->max('amount'), 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">أقل دفعة</h6>
                    </div>
                    <div class="card-body">
                        <h4 class="text-info">{{ number_format($report['rows']->min('amount'), 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-header bg-warning text-white">
                        <h6 class="mb-0">عدد الطلاب</h6>
                    </div>
                    <div class="card-body">
                        <h4 class="text-warning">{{ $report['rows']->pluck('student_id')->unique()->count() }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">خزائن مستخدمة</h6>
                    </div>
                    <div class="card-body">
                        <h4 class="text-primary">{{ $report['rows']->pluck('treasury_id')->unique()->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection