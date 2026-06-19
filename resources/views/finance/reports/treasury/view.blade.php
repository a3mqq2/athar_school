{{-- resources/views/finance/reports/treasury/view.blade.php --}}
@extends('layouts.app')

@section('title', 'كشف حساب خزينة')

@push('styles')
<style>
    :root {
        --primary: #925419;
        --success: #28a745;
        --danger: #dc3545;
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

    .report-period {
        font-size: 1.1rem;
        opacity: 0.9;
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
    }

    .stats-card.deposits { border-left-color: var(--success); }
    .stats-card.withdrawals { border-left-color: var(--danger); }
    .stats-card.net { border-left-color: var(--primary); }

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
        justify-content: between;
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

    .badge-deposit {
        background: var(--success);
        color: white;
    }

    .badge-withdrawal {
        background: var(--danger);
        color: white;
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
        <h1 class="report-title">كشف حساب خزينة</h1>
        <p class="report-period">
            @if($selectedTreasury)
                {{ $selectedTreasury->name }} |
            @else
                جميع الخزائن |
            @endif
            من {{ Carbon\Carbon::parse($filters['from'])->format('Y/m/d') }} 
            إلى {{ Carbon\Carbon::parse($filters['to'])->format('Y/m/d') }}
        </p>
    </div>

    <div class="actions-bar">
        <a href="{{ route('finance.reports.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-right me-2"></i>العودة للتقارير
        </a>
        <div class="btn-group">
            <a href="{{ request()->fullUrlWithQuery(['format' => 'print']) }}" 
               class="btn btn-success" target="_blank">
                <i class="fa fa-print me-2"></i>طباعة
            </a>
        </div>
    </div>

    <div class="stats-cards">
        <div class="stats-card deposits">
            <div class="stats-value text-success">{{ number_format($report['totals']['deposits'], 2) }}</div>
            <div class="stats-label">إجمالي الإيداعات</div>
        </div>
        <div class="stats-card withdrawals">
            <div class="stats-value text-danger">{{ number_format($report['totals']['withdrawals'], 2) }}</div>
            <div class="stats-label">إجمالي السحوبات</div>
        </div>
        <div class="stats-card net">
            <div class="stats-value" style="color: var(--primary)">
                {{ number_format($report['totals']['net'], 2) }}
            </div>
            <div class="stats-label">صافي الحركة</div>
        </div>
    </div>

    <div class="report-table-container">
        <div class="table-header">
            <h3 class="table-title">تفاصيل الحركات</h3>
            <span class="badge bg-primary">{{ $report['rows']->count() }} حركة</span>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="15%">التاريخ</th>
                        <th width="30%">البيان</th>
                        <th width="15%">الخزينة</th>
                        <th width="10%">النوع</th>
                        <th width="15%" class="text-end">المبلغ</th>
                        <th width="10%">المستفيد</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['rows'] as $index => $transaction)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $transaction->created_at->format('Y/m/d H:i') }}</td>
                            <td>{{ $transaction->description ?: 'غير محدد' }}</td>
                            <td>{{ $transaction->treasury->name ?? 'غير محدد' }}</td>
                            <td>
                                <span class="badge {{ $transaction->transaction_type === 'deposit' ? 'badge-deposit' : 'badge-withdrawal' }}">
                                    {{ $transaction->transaction_type === 'deposit' ? 'إيداع' : 'سحب' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <strong>{{ number_format($transaction->amount, 2) }}</strong>
                            </td>
                            <td>{{ $transaction->payee_name ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fa fa-inbox"></i>
                                    <p>لا توجد حركات في الفترة المحددة</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection