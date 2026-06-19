{{-- resources/views/finance/employee-balances/statement.blade.php --}}
@extends('layouts.app')

@section('title', 'كشف حساب الموظف')


@push('styles')
<style>
:root {
    --primary-color: #925419;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --bg-light: #f8f9fa;
    --border-color: #e1e5eb;
    --text-muted: #6c757d;
}

.statement-card {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.amount-credit {
    color: var(--success-color);
    font-weight: 600;
}

.amount-debit {
    color: var(--danger-color);
    font-weight: 600;
}

.summary-footer {
    background: linear-gradient(135deg, #f6e7d7 0%, #f3efe7 100%);
    border-top: 2px solid var(--primary-color);
    font-weight: 600;
}

.summary-footer th {
    color: var(--primary-color);
    font-weight: 700;
    text-align: center;
    padding: 15px 12px;
    border: none;
}

.current-balance {
    background: var(--primary-color);
    color: white;
    font-size: 1.1rem;
}

.page-header {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid var(--primary-color);
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-1">كشف حساب الموظف: {{ $user->name }}</h3>
                <p class="text-muted mb-0">
                    الرصيد الحالي: 
                    <span class="fw-bold text-primary">{{ number_format($user->balance, 2) }} د.ل</span>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('finance.employee-balances.index') }}" class="btn btn-secondary">
                    العودة للقائمة
                </a>
                <a href="{{ route('finance.reports.employee', [
                        'employee_id' => $user->id,
                        'from' => request('from'),
                        'to' => request('to'),
                        'treasury_id' => request('treasury_id'),
                        'format' => 'print'
                    ]) }}" 
                    target="_blank" 
                    class="btn btn-outline-primary">
                    <i class="bi bi-printer"></i> طباعة
                </a>
            </div>
        </div>
    </div>


    <!-- فلاتر التصفية -->
    <div class="statement-card">
        <div class="card-header bg-light">
            <h5 class="mb-0">تصفية الحركات</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">الخزينة</label>
                    <select name="treasury_id" class="form-select">
                        <option value="">جميع الخزائن</option>
                        @foreach($treasuries as $treasury)
                            <option value="{{ $treasury->id }}" @selected(request('treasury_id') == $treasury->id)>
                                {{ $treasury->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">تصفية</button>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول الحركات -->
    <div class="statement-card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">حركات الحساب</h5>
            <span class="badge bg-primary">{{ $userTransactions->total() ?? 0 }} معاملة</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>الوصف</th>
                            <th>نوع المعاملة</th>
                            <th>الخزينة</th>
                            <th class="text-end">المبلغ</th>
                            <th>المسؤول</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userTransactions ?? [] as $userTx)
                            <tr>
                                <td>
                                    <div>{{ $userTx->created_at->format('Y-m-d') }}</div>
                                    <small class="text-muted">{{ $userTx->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $userTx->description }}</div>
                                    @if($userTx->reference_type)
                                        <small class="badge bg-info">{{ $userTx->reference_type }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $userTx->type === 'credit' ? 'success' : 'danger' }}">
                                        {{ $userTx->type === 'credit' ? 'إضافة رصيد' : 'خصم رصيد' }}
                                    </span>
                                </td>
                                <td>{{ $userTx->transaction?->treasury?->name ?? '-' }}</td>
                                <td class="text-end">
                                    <span class="{{ $userTx->type === 'credit' ? 'amount-credit' : 'amount-debit' }}">
                                        {{ $userTx->type === 'credit' ? '+' : '-' }}{{ number_format($userTx->amount, 2) }}
                                    </span>
                                </td>
                                <td>{{ $userTx->creator->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    لا توجد حركات في الحساب
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    
                    @if(isset($totalCredits) && isset($totalDebits))
                    <tfoot class="summary-footer">
                        <tr>
                            <th colspan="4" class="text-end">إجمالي الإضافات:</th>
                            <th class="text-end">
                                <span class="amount-credit">+{{ number_format($totalCredits, 2) }} د.ل</span>
                            </th>
                            <th></th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">إجمالي الخصومات:</th>
                            <th class="text-end">
                                <span class="amount-debit">-{{ number_format($totalDebits, 2) }} د.ل</span>
                            </th>
                            <th></th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">الرصيد الحالي:</th>
                            <th class="text-end current-balance">
                                {{ number_format($user->balance, 2) }} د.ل
                            </th>
                            <th class="current-balance"></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($userTransactions) && $userTransactions->hasPages())
                <div class="p-3 border-top">
                    {{ $userTransactions->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection