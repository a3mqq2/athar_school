@extends('layouts.app')

@section('title','المعاملات المالية')

@push('styles')
<style>
.filter-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.filter-card .form-label { font-weight: 600; color: #495057; margin-bottom: 0.5rem; }
.table-card { background: #ffffff; border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.table thead th { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057; padding: 1rem 0.75rem; }
.table tbody tr { transition: background-color 0.2s ease; }
.table tbody tr:hover { background-color: #f8f9fa; }
.table tbody td { padding: 0.875rem 0.75rem; vertical-align: middle; border-bottom: 1px solid #f1f3f4; }
.amount-deposit { color: #198754; font-weight: 600; }
.amount-withdrawal { color: #dc3545; font-weight: 600; }
.transaction-badge { padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
.transaction-badge.deposit { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
.transaction-badge.withdrawal { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
.transaction-type-badge { background-color: #e9ecef; color: #495057; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 500; }
.btn-group-actions { display: flex; gap: 0.25rem; }
.btn-group-actions .btn { padding: 0.25rem 0.5rem; font-size: 0.8rem; border-radius: 4px; }
.document-number { font-family: 'Courier New', monospace; font-size: 0.85rem; color: #6c757d; }
.user-name { font-size: 0.85rem; color: #6c757d; }
.payee-name { font-weight: 500; color: #212529; }
.treasury-link { color: #0d6efd; text-decoration: none; font-weight: 500; }
.treasury-link:hover { text-decoration: underline; }
.filter-buttons { display: flex; gap: 0.5rem; align-items: center; }
.filter-reset-link { color: #6c757d; text-decoration: none; font-size: 0.9rem; }
.filter-reset-link:hover { color: #495057; text-decoration: underline; }
.empty-state { text-align: center; padding: 3rem 1rem; color: #6c757d; }
.page-title { color: #212529; font-weight: 600; margin-bottom: 0.5rem; }
@media (max-width: 768px) {
    .filter-card { padding: 1rem; }
    .table-responsive { font-size: 0.9rem; }
    .btn-group-actions { flex-direction: column; gap: 0.125rem; }
    .filter-buttons { flex-direction: column; align-items: stretch; }
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="page-title">المعاملات المالية</h3>
            <p class="text-muted mb-0">إدارة وتتبع المعاملات المالية</p>
        </div>
        <a href="{{ route('finance.transactions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>إضافة معاملة
        </a>
    </div>

    <div class="filter-card">
        <form method="get" action="{{ route('finance.transactions.index') }}">
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">اسم المستلم</label>
                    <input type="text" name="payee_name" value="{{ $payeeName }}" class="form-control" placeholder="ابحث باسم المستلم">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">الخزينة</label>
                    <select name="treasury_id" class="form-select">
                        <option value="">جميع الخزائن</option>
                        @foreach($treasuries as $treasury)
                            <option value="{{ $treasury->id }}" @selected($treasuryId==$treasury->id)>{{ $treasury->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">نوع المعاملة</label>
                    <select name="transaction_type" class="form-select">
                        <option value="">جميع الأنواع</option>
                        <option value="deposit" @selected($transactionType=='deposit')>إيداع</option>
                        <option value="withdrawal" @selected($transactionType=='withdrawal')>سحب</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">التصنيف</label>
                    <select name="transaction_type_id" class="form-select">
                        <option value="">جميع التصنيفات</option>
                        @foreach($transactionTypes as $type)
                            <option value="{{ $type->id }}" @selected($transactionTypeId==$type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">طريقة الدفع</label>
                    <select name="payment_method" class="form-select">
                        <option value="">جميع الطرق</option>
                        <option value="cash" @selected($paymentMethod=='cash')>نقدي</option>
                        <option value="bank" @selected($paymentMethod=='bank_transfer')>تحويل بنكي</option>
                    </select>
                </div>
                <div class="col-lg-1-5 col-md-6">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control">
                </div>
                <div class="col-lg-1-5 col-md-6">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to_date" value="{{ $toDate }}" class="form-control">
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>تصفية</button>
                    <a href="{{ route('finance.transactions.index') }}" class="filter-reset-link"><i class="fas fa-times me-1"></i>إلغاء التصفية</a>
                    @if($hasFilters ?? false)
                        <a href="{{ route('finance.transactions.statement', request()->all()) }}" class="btn btn-success" target="_blank">
                            <i class="fas fa-print me-1"></i>طباعة كشف الحساب
                        </a>
                    @endif
                </div>
                @if($payeeName || $treasuryId || $transactionType || $transactionTypeId || $paymentMethod || $fromDate || $toDate)
                    <small class="text-muted"><i class="fas fa-filter me-1"></i>يتم عرض النتائج المفلترة</small>
                @endif
            </div>
        </form>
    </div>

    @if($hasFilters ?? false)
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">إجمالي الإيداعات</h6>
                        <h4 class="text-success mb-0">{{ number_format($totalDeposits, 2) }} د.ل</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-danger">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">إجمالي السحوبات</h6>
                        <h4 class="text-danger mb-0">{{ number_format($totalWithdrawals, 2) }} د.ل</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">الصافي</h6>
                        <h4 class="text-primary mb-0">{{ number_format($netBalance, 2) }} د.ل</h4>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>المستلم</th>
                        <th>المبلغ</th>
                        <th>النوع</th>
                        <th>التصنيف</th>
                        <th>طريقة الدفع</th>
                        <th>الخزينة</th>
                        <th>رقم المستند</th>
                        <th>المستخدم</th>
                        <th class="text-end">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td>
                            <div class="fw-medium">{{ $transaction->created_at->format('Y-m-d') }}</div>
                            <small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            <a href="{{ route('finance.transactions.show',$transaction) }}" class="payee-name text-decoration-none">
                                {{ $transaction->payee_name }}
                            </a>
                        </td>
                        <td>
                            <span class="fw-bold {{ $transaction->transaction_type === 'deposit' ? 'amount-deposit' : 'amount-withdrawal' }}">
                                {{ $transaction->transaction_type === 'deposit' ? '+' : '-' }}{{ $transaction->formatted_amount }}
                            </span>
                        </td>
                        <td>
                            <span class="transaction-badge {{ $transaction->transaction_type }}">{{ $transaction->transaction_type_name }}</span>
                        </td>
                        <td>
                            <span class="transaction-type-badge">{{ $transaction->transactionType->name }}</span>
                        </td>
                        <td>
                            <span class="transaction-type-badge">{{ $transaction->payment_method_name }}</span>
                        </td>
                        <td>
                            <a href="{{ route('finance.treasuries.show', $transaction->treasury) }}" class="treasury-link">
                                {{ $transaction->treasury->name }}
                            </a>
                        </td>
                        <td>
                            <span class="document-number">{{ $transaction->document_number ?: '-' }}</span>
                        </td>
                        <td>
                            <span class="user-name">{{ $transaction->user->name }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="action-buttons text-end">
                                <a href="{{ route('finance.transactions.show',$transaction) }}" class="btn btn-outline-info btn-sm" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('finance.transactions.edit',$transaction) }}" class="btn btn-outline-primary btn-sm" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('finance.transactions.receipt',$transaction) }}" class="btn btn-outline-success btn-sm" title="طباعة إيصال" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                                @if (auth()->id() == 1)
                                <form method="POST" action="{{ route('finance.transactions.destroy',$transaction) }}" class="d-inline" onsubmit="return confirm('تأكيد الحذف؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" title="حذف"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">
                            <div class="empty-state">
                                <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
                                <h5>لا توجد معاملات</h5>
                                <p class="mb-3">لم يتم العثور على معاملات مالية</p>
                                <a href="{{ route('finance.transactions.create') }}" class="btn btn-primary">إضافة معاملة جديدة</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(!($hasFilters ?? false) && method_exists($transactions, 'hasPages') && $transactions->hasPages())
        <div class="mt-4">{{ $transactions->links() }}</div>
    @endif
</div>
@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    const transactionId = urlParams.get("transaction_id");
    if (transactionId) {
        const receiptUrl = `/finance/transactions/${transactionId}/receipt`;
        window.open(receiptUrl, "_blank");
    }
});
</script>
@endpush
@endsection
