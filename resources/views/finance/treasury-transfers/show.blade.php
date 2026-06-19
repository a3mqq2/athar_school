@extends('layouts.app')

@section('title','تفاصيل التحويل')

@push('styles')
@include('partials.page-styles')
<style>
.transfer-overview {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.treasury-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
    height: 100%;
}

.transfer-arrow {
    font-size: 3rem;
    color: #0d6efd;
    text-align: center;
    margin: 2rem 0;
}

.amount-highlight {
    font-size: 2rem;
    font-weight: 700;
    color: #0d6efd;
    text-align: center;
    margin: 1rem 0;
}

.info-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
}

.transaction-link {
    color: #0d6efd;
    text-decoration: none;
    font-weight: 500;
}

.transaction-link:hover {
    text-decoration: underline;
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="page-title">تفاصيل التحويل</h3>
            <p class="text-muted mb-0">معلومات تفصيلية عن التحويل بين الخزائن</p>
        </div>
        <div>
            <a href="{{ route('finance.treasury-transfers.index') }}" class="btn btn-secondary">
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Transfer Overview -->
    <div class="transfer-overview">
        <div class="row align-items-center">
            <!-- From Treasury -->
            <div class="col-md-4">
                <div class="treasury-card">
                    <div class="text-center">
                        <i class="fas fa-vault fa-2x text-danger mb-3"></i>
                        <h5 class="text-danger">من خزينة</h5>
                        <h4 class="fw-bold">{{ $treasuryTransfer->fromTreasury->name }}</h4>
                        <p class="text-muted mb-2">{{ optional($treasuryTransfer->fromTreasury->responsible)->name }}</p>
                        <small class="text-muted">
                            الرصيد الحالي: {{ number_format($treasuryTransfer->fromTreasury->current_balance, 2) }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Transfer Arrow & Amount -->
            <div class="col-md-4">
                <div class="text-center">
                    <div class="transfer-arrow">
                        <i class="fas fa-arrow-left"></i>
                    </div>
                    <div class="amount-highlight">
                        {{ $treasuryTransfer->formatted_amount }}
                    </div>
                    <small class="text-muted">المبلغ المنقول</small>
                </div>
            </div>

            <!-- To Treasury -->
            <div class="col-md-4">
                <div class="treasury-card">
                    <div class="text-center">
                        <i class="fas fa-vault fa-2x text-success mb-3"></i>
                        <h5 class="text-success">إلى خزينة</h5>
                        <h4 class="fw-bold">{{ $treasuryTransfer->toTreasury->name }}</h4>
                        <p class="text-muted mb-2">{{ optional($treasuryTransfer->toTreasury->responsible)->name }}</p>
                        <small class="text-muted">
                            الرصيد الحالي: {{ number_format($treasuryTransfer->toTreasury->current_balance, 2) }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Transfer Details -->
        <div class="col-md-8">
            <div class="info-card">
                <h5 class="mb-4">تفاصيل التحويل</h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted fw-semibold">تاريخ التحويل</label>
                        <div class="fw-bold">{{ $treasuryTransfer->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted fw-semibold">المستخدم</label>
                        <div class="fw-bold">{{ $treasuryTransfer->user->name }}</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted fw-semibold">الرقم المرجعي</label>
                        <div class="fw-bold">{{ $treasuryTransfer->reference_number ?: 'غير محدد' }}</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted fw-semibold">المبلغ</label>
                        <div class="fw-bold text-primary fs-5">{{ $treasuryTransfer->formatted_amount }}</div>
                    </div>

                    @if($treasuryTransfer->description)
                    <div class="col-12">
                        <label class="form-label text-muted fw-semibold">وصف التحويل</label>
                        <div class="fw-bold">{{ $treasuryTransfer->description }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Related Transactions -->
            <div class="info-card mt-4">
                <h5 class="mb-4">المعاملات المرتبطة</h5>
                
                <div class="row g-3">
                    @if($treasuryTransfer->withdrawalTransaction)
                    <div class="col-md-6">
                        <div class="border rounded p-3">
                            <h6 class="text-danger mb-2">
                                <i class="fas fa-minus-circle me-2"></i>معاملة السحب
                            </h6>
                            <p class="mb-1">
                                <strong>الخزينة:</strong> {{ $treasuryTransfer->withdrawalTransaction->treasury->name }}
                            </p>
                            <p class="mb-1">
                                <strong>المبلغ:</strong> 
                                <span class="text-danger fw-bold">-{{ $treasuryTransfer->withdrawalTransaction->formatted_amount }}</span>
                            </p>
                            <a href="{{ route('finance.transactions.show', $treasuryTransfer->withdrawalTransaction) }}" 
                               class="transaction-link">
                                عرض تفاصيل المعاملة
                            </a>
                        </div>
                    </div>
                    @endif

                    @if($treasuryTransfer->depositTransaction)
                    <div class="col-md-6">
                        <div class="border rounded p-3">
                            <h6 class="text-success mb-2">
                                <i class="fas fa-plus-circle me-2"></i>معاملة الإيداع
                            </h6>
                            <p class="mb-1">
                                <strong>الخزينة:</strong> {{ $treasuryTransfer->depositTransaction->treasury->name }}
                            </p>
                            <p class="mb-1">
                                <strong>المبلغ:</strong> 
                                <span class="text-success fw-bold">+{{ $treasuryTransfer->depositTransaction->formatted_amount }}</span>
                            </p>
                            <a href="{{ route('finance.transactions.show', $treasuryTransfer->depositTransaction) }}" 
                               class="transaction-link">
                                عرض تفاصيل المعاملة
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="info-card">
                <h5 class="mb-4">إجراءات سريعة</h5>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('finance.treasuries.show', $treasuryTransfer->fromTreasury) }}" 
                       class="btn btn-outline-danger">
                        <i class="fas fa-vault me-2"></i>عرض الخزينة المرسلة
                    </a>
                    
                    <a href="{{ route('finance.treasuries.show', $treasuryTransfer->toTreasury) }}" 
                       class="btn btn-outline-success">
                        <i class="fas fa-vault me-2"></i>عرض الخزينة المستقبلة
                    </a>
                    
                    <a href="{{ route('finance.treasury-transfers.create') }}" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-exchange-alt me-2"></i>تحويل جديد
                    </a>
                    
                    <hr>
                    
                    <form method="POST" action="{{ route('finance.treasury-transfers.destroy', $treasuryTransfer) }}" 
                          onsubmit="return confirmDelete()">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>حذف التحويل
                        </button>
                    </form>
                </div>
            </div>

            <!-- Transfer Summary -->
            <div class="info-card mt-4">
                <h5 class="mb-4">ملخص التحويل</h5>
                
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <div class="border rounded p-3">
                            <h6 class="text-muted">المبلغ الإجمالي</h6>
                            <h4 class="text-primary mb-0">{{ $treasuryTransfer->formatted_amount }}</h4>
                        </div>
                    </div>
                </div>
                
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    تم إنشاء معاملتين: سحب من الخزينة المرسلة وإيداع في الخزينة المستقبلة
                </small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete() {
    return confirm('هل أنت متأكد من حذف هذا التحويل؟\n\nسيتم حذف جميع المعاملات المرتبطة وإعادة حساب أرصدة الخزائن.');
}
</script>
@endpush
@endsection