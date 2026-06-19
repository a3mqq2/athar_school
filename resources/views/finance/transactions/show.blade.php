@extends('layouts.app')

@section('title','تفاصيل المعاملة المالية')

@push('styles')
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="page-header">تفاصيل المعاملة المالية</h3>
        <div>
            <a href="{{ route('finance.transactions.edit', $transaction) }}" class="btn btn-primary">تعديل</a>
            <a href="{{ route('finance.transactions.index') }}" class="btn btn-secondary">العودة للقائمة</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">معلومات المعاملة</h5>
                    <span class="badge bg-{{ $transaction->transaction_type === 'deposit' ? 'success' : 'danger' }} fs-6">
                        {{ $transaction->transaction_type_name }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">اسم المستلم</label>
                            <div class="fw-bold">{{ $transaction->payee_name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">المبلغ</label>
                            <div class="fw-bold text-{{ $transaction->transaction_type === 'deposit' ? 'success' : 'danger' }} fs-4">
                                {{ $transaction->transaction_type === 'deposit' ? '+' : '-' }}{{ $transaction->formatted_amount }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">الخزينة</label>
                            <div class="fw-bold">
                                <a href="{{ route('finance.treasuries.show', $transaction->treasury) }}">
                                    {{ $transaction->treasury->name }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">تصنيف المعاملة</label>
                            <div class="fw-bold">{{ $transaction->transactionType->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">طريقة الدفع</label>
                            <div class="fw-bold">{{ $transaction->payment_method_name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">رقم المستند</label>
                            <div class="fw-bold">{{ $transaction->document_number ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">تاريخ المعاملة</label>
                            <div class="fw-bold">{{ $transaction->created_at->format('Y-m-d H:i:s') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">المستخدم</label>
                            <div class="fw-bold">{{ $transaction->user->name }}</div>
                        </div>
                        @if($transaction->description)
                        <div class="col-12">
                            <label class="form-label text-muted">الوصف</label>
                            <div class="fw-bold">{{ $transaction->description }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">معلومات الخزينة</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">اسم الخزينة</label>
                        <div class="fw-bold">{{ $transaction->treasury->name }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">الرصيد الحالي</label>
                        <div class="fw-bold text-primary fs-5">{{ number_format($transaction->treasury->current_balance, 2) }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">المسؤول المالي</label>
                        <div class="fw-bold">{{ optional($transaction->treasury->responsible)->name ?: '-' }}</div>
                    </div>
                    <div class="d-grid">
                        <a href="{{ route('finance.treasuries.show', $transaction->treasury) }}" class="btn btn-outline-primary">عرض تفاصيل الخزينة</a>
                    </div>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header"><h5 class="mb-0">إحصائيات التصنيف</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">التصنيف</label>
                        <div class="fw-bold">{{ $transaction->transactionType->name }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">النوع</label>
                        <div>
                            <span class="badge bg-{{ $transaction->transactionType->type === 'deposit' ? 'success' : 'danger' }}">
                                {{ $transaction->transactionType->type_name }}
                            </span>
                        </div>
                    </div>
                    @if($transaction->transactionType->for_system)
                    <div class="mb-3">
                        <span class="badge bg-secondary"><i class="fas fa-cog"></i> تصنيف النظام</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
