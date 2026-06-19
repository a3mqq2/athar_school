{{-- resources/views/finance/reports/employee/view.blade.php --}}
@extends('layouts.app')

@section('title', 'كشف حساب الموظف')

@push('styles')
<style>
    :root {
        --primary: #925419;
        --success: #28a745;
        --danger: #dc3545;
        --info: #17a2b8;
        --light: #f8f9fa;
        --border: #e1e5eb;
        --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .report-header {
        background: linear-gradient(135deg, var(--primary), #6b3410);
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

    .employee-info {
        background: rgba(255, 255, 255, 0.1);
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
    }

    .employee-name {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .report-period {
        opacity: 0.9;
    }

    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        top: 0;
        right: 0;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        opacity: 0.1;
        transform: translate(20px, -20px);
    }

    .stats-card.received { 
        border-left-color: var(--success);
    }
    .stats-card.received::before {
        background: var(--success);
    }

    .stats-card.deductions { 
        border-left-color: var(--danger);
    }
    .stats-card.deductions::before {
        background: var(--danger);
    }

    .stats-card.net-salary { 
        border-left-color: var(--primary);
    }
    .stats-card.net-salary::before {
        background: var(--primary);
    }

    .stats-icon {
        font-size: 2rem;
        margin-bottom: 1rem;
        opacity: 0.7;
    }

    .stats-card.received .stats-icon { color: var(--success); }
    .stats-card.deductions .stats-icon { color: var(--danger); }
    .stats-card.net-salary .stats-icon { color: var(--primary); }

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
        max-height: 500px;
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
    }

    .table td {
        vertical-align: middle;
    }

    .transaction-type {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .transaction-type.salary {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success);
        border: 1px solid rgba(40, 167, 69, 0.2);
    }

    .transaction-type.deduction {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger);
        border: 1px solid rgba(220, 53, 69, 0.2);
    }

    .transaction-type.bonus {
        background: rgba(23, 162, 184, 0.1);
        color: var(--info);
        border: 1px solid rgba(23, 162, 184, 0.2);
    }

    .actions-bar {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        justify-content: space-between;
        align-items: center;
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

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-success {
        background: var(--success);
        color: white;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

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

    .amount-positive {
        color: var(--success);
        font-weight: 600;
    }

    .amount-negative {
        color: var(--danger);
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .stats-cards {
            grid-template-columns: 1fr;
        }
        
        .actions-bar {
            flex-direction: column;
            gap: 1rem;
        }
        
        .btn-group {
            width: 100%;
        }
        
        .btn {
            flex: 1;
            text-align: center;
            justify-content: center;
        }

        .table-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
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
        <h1 class="report-title">كشف حساب الموظف</h1>
        <div class="employee-info">
            <div class="employee-name">{{ $employee->name }}</div>
            <div class="report-period">
                من {{ Carbon\Carbon::parse($filters['from'])->format('Y/m/d') }} 
                إلى {{ Carbon\Carbon::parse($filters['to'])->format('Y/m/d') }}
            </div>
        </div>
    </div>

    <div class="actions-bar">
        <a href="{{ route('finance.reports.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-right"></i>العودة للتقارير
        </a>
        <div class="btn-group">
            <a href="{{ request()->fullUrlWithQuery(['format' => 'print']) }}" 
               class="btn btn-success" target="_blank">
                <i class="fa fa-print"></i>طباعة
            </a>
        </div>
    </div>

    <div class="stats-cards">
        <div class="stats-card received">
            <div class="stats-icon">
                <i class="fa fa-arrow-down"></i>
            </div>
            <div class="stats-value text-success">{{ number_format($report['totals']['total_received'], 2) }}</div>
            <div class="stats-label">إجمالي المستلم</div>
        </div>
        <div class="stats-card deductions">
            <div class="stats-icon">
                <i class="fa fa-arrow-up"></i>
            </div>
            <div class="stats-value text-danger">{{ number_format($report['totals']['total_deductions'], 2) }}</div>
            <div class="stats-label">إجمالي الخصومات</div>
        </div>
        <div class="stats-card net-salary">
            <div class="stats-icon">
                <i class="fa fa-wallet"></i>
            </div>
            <div class="stats-value" style="color: var(--primary)">
                {{ number_format($report['totals']['net_salary'], 2) }}
            </div>
            <div class="stats-label">صافي الراتب</div>
        </div>
    </div>

    <div class="report-table-container">
        <div class="table-header">
            <h3 class="table-title">تفاصيل الحركات المالية</h3>
            <span class="badge bg-primary">{{ $report['rows']->count() }} حركة</span>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="15%">التاريخ</th>
                        <th width="30%">البيان</th>
                        <th width="15%">النوع</th>
                        <th width="15%" class="text-end">المبلغ</th>
                        <th width="5%">العملية</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['rows'] as $index => $transaction)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $transaction->created_at->format('Y/m/d H:i') }}</td>
                            <td>{{ $transaction->description ?: 'غير محدد' }}</td>
                            <td>
                                @php
                                    $type = 'salary';
                                    $typeLabel = 'راتب';
                                    $icon = 'fa-money-bill';
                                    
                                    if (str_contains(strtolower($transaction->description), 'خصم')) {
                                        $type = 'deduction';
                                        $typeLabel = 'خصم';
                                        $icon = 'fa-minus-circle';
                                    } elseif (str_contains(strtolower($transaction->description), 'بدل')) {
                                        $type = 'bonus';
                                        $typeLabel = 'بدل';
                                        $icon = 'fa-plus-circle';
                                    }
                                @endphp
                                <span class="transaction-type {{ $type }}">
                                    <i class="fa {{ $icon }}"></i>
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="{{ $transaction->transaction_type === 'withdrawal' ? 'amount-positive' : 'amount-negative' }}">
                                    {{ $transaction->transaction_type === 'withdrawal' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($transaction->transaction_type === 'withdrawal')
                                    <i class="fa fa-arrow-down text-success" title="استلام"></i>
                                @else
                                    <i class="fa fa-arrow-up text-danger" title="خصم"></i>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fa fa-receipt"></i>
                                    <p>لا توجد حركات مالية للموظف في الفترة المحددة</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($report['rows']->count() > 0)
        <div class="mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-primary">ملخص المعاملات</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>عدد المعاملات:</span>
                                <strong>{{ $report['rows']->count() }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>معاملات الاستلام:</span>
                                <strong class="text-success">{{ $report['rows']->where('transaction_type', 'withdrawal')->count() }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>معاملات الخصم:</span>
                                <strong class="text-danger">{{ $report['rows']->where('transaction_type', 'deposit')->count() }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-primary">معلومات الموظف</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>اسم الموظف:</span>
                                <strong>{{ $employee->name }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>البريد الإلكتروني:</span>
                                <strong>{{ $employee->email }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>تاريخ التقرير:</span>
                                <strong>{{ now()->format('Y/m/d H:i') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection