@extends('layouts.app')

@section('title','تحويلات الخزائن')

@push('styles')
@include('partials.page-styles')
<style>
.transfer-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.transfer-arrow {
    color: #0d6efd;
    font-size: 1.2rem;
}

.treasury-name {
    font-weight: 600;
    color: #495057;
}

.amount-transfer {
    color: #0d6efd;
    font-weight: 600;
    font-size: 1.1rem;
}

.transfer-meta {
    font-size: 0.85rem;
    color: #6c757d;
}

.filter-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="page-title">تحويلات الخزائن</h3>
            <p class="text-muted mb-0">إدارة ومتابعة التحويلات بين الخزائن</p>
        </div>
        <a href="{{ route('finance.treasury-transfers.create') }}" class="btn btn-primary">
            <i class="fas fa-exchange-alt me-2"></i>تحويل جديد
        </a>
    </div>

    <!-- Filter Section -->
    <div class="filter-card">
        <form method="get" action="{{ route('finance.treasury-transfers.index') }}">
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold">من خزينة</label>
                    <select name="from_treasury_id" class="form-select">
                        <option value="">جميع الخزائن</option>
                        @foreach($treasuries as $treasury)
                            <option value="{{ $treasury->id }}" @selected($fromTreasuryId==$treasury->id)>
                                {{ $treasury->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold">إلى خزينة</label>
                    <select name="to_treasury_id" class="form-select">
                        <option value="">جميع الخزائن</option>
                        @foreach($treasuries as $treasury)
                            <option value="{{ $treasury->id }}" @selected($toTreasuryId==$treasury->id)>
                                {{ $treasury->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold">من تاريخ</label>
                    <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control">
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold">إلى تاريخ</label>
                    <input type="date" name="to_date" value="{{ $toDate }}" class="form-control">
                </div>
                
                <div class="col-lg-2 col-md-12">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="fas fa-search me-1"></i>تصفية
                        </button>
                        <a href="{{ route('finance.treasury-transfers.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>




    <!-- Transfers Table -->
    <div class="transfer-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="px-4 py-3">التاريخ</th>
                        <th class="px-4 py-3">من خزينة</th>
                        <th class="px-4 py-3"></th>
                        <th class="px-4 py-3">إلى خزينة</th>
                        <th class="px-4 py-3">المبلغ</th>
                        <th class="px-4 py-3">الرقم المرجعي</th>
                        <th class="px-4 py-3">المستخدم</th>
                        <th class="px-4 py-3 text-end">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($transfers as $transfer)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="fw-medium">{{ $transfer->created_at->format('Y-m-d') }}</div>
                            <small class="transfer-meta">{{ $transfer->created_at->format('H:i') }}</small>
                        </td>
                        <td class="px-4 py-3">
                            <div class="treasury-name">{{ $transfer->fromTreasury->name }}</div>
                            <small class="transfer-meta">{{ optional($transfer->fromTreasury->responsible)->name }}</small>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <i class="fas fa-arrow-left transfer-arrow"></i>
                        </td>
                        <td class="px-4 py-3">
                            <div class="treasury-name">{{ $transfer->toTreasury->name }}</div>
                            <small class="transfer-meta">{{ optional($transfer->toTreasury->responsible)->name }}</small>
                        </td>
                        <td class="px-4 py-3">
                            <span class="amount-transfer">{{ $transfer->formatted_amount }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="transfer-meta">{{ $transfer->reference_number ?: '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="transfer-meta">{{ $transfer->user->name }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('finance.treasury-transfers.show', $transfer) }}" 
                                   class="btn btn-outline-info btn-sm" title="عرض التفاصيل">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form method="POST" action="{{ route('finance.treasury-transfers.destroy', $transfer) }}" 
                                      class="d-inline" onsubmit="return confirmDelete()">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" title="حذف التحويل">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-exchange-alt fa-3x mb-3 text-muted"></i>
                                <h5>لا توجد تحويلات</h5>
                                <p class="mb-3">لم يتم العثور على تحويلات بين الخزائن</p>
                                <a href="{{ route('finance.treasury-transfers.create') }}" class="btn btn-primary">
                                    <i class="fas fa-exchange-alt me-2"></i>إنشاء تحويل جديد
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($transfers->hasPages())
        <div class="mt-4">
            {{ $transfers->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
function confirmDelete() {
    return confirm('هل أنت متأكد من حذف هذا التحويل؟\n\nسيتم حذف جميع المعاملات المرتبطة وإعادة حساب أرصدة الخزائن.');
}
</script>
@endpush
@endsection