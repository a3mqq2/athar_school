@extends('layouts.app')

@section('title','تفاصيل الخزينة')

@push('styles')
@include('partials.page-styles')
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="page-header">تفاصيل الخزينة: {{ $treasury->name }}</h3>
        <div>
            <a href="{{ route('finance.transactions.create') }}?treasury_id={{ $treasury->id }}" class="btn btn-success">إضافة معاملة</a>
            <a href="{{ route('finance.treasuries.edit', $treasury) }}" class="btn btn-primary">تعديل</a>
            <a href="{{ route('finance.treasuries.index') }}" class="btn btn-secondary">العودة للقائمة</a>
        </div>
    </div>

    <div class="row">
        <!-- معلومات الخزينة -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">معلومات الخزينة</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">اسم الخزينة</label>
                        <div class="fw-bold">{{ $treasury->name }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">الرصيد الافتتاحي</label>
                        <div class="fw-bold text-info">{{ number_format($treasury->opening_balance, 2) }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">الرصيد الحالي</label>
                        <div class="fw-bold text-primary fs-4">{{ number_format($treasury->current_balance, 2) }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">المسؤول المالي</label>
                        <div class="fw-bold">{{ optional($treasury->responsible)->name ?: '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">تاريخ الإنشاء</label>
                        <div class="fw-bold">{{ $treasury->created_at->format('Y-m-d') }}</div>
                    </div>
                </div>
            </div>

            <!-- إحصائيات سريعة -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">إحصائيات سريعة</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <div class="border rounded p-3">
                                <h6 class="text-success">إجمالي الإيداعات</h6>
                                <h4 class="text-success">{{ number_format($treasury->total_deposits, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="border rounded p-3">
                                <h6 class="text-danger">إجمالي السحوبات</h6>
                                <h4 class="text-danger">{{ number_format($treasury->total_withdrawals, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border rounded p-3">
                                <h6 class="text-info">عدد المعاملات</h6>
                                <h4 class="text-info">{{ $treasury->transactions_count }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- المعاملات الأخيرة -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">المعاملات الأخيرة</h5>
                    <a href="{{ route('finance.transactions.index') }}?treasury_id={{ $treasury->id }}" class="btn btn-sm btn-outline-primary">
                        عرض جميع المعاملات
                    </a>
                </div>
                <div class="card-body">
                    @if($treasury->transactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>المستلم</th>
                                        <th>المبلغ</th>
                                        <th>النوع</th>
                                        <th>التصنيف</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($treasury->transactions()->with(['transactionType', 'user'])->latest()->limit(10)->get() as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('Y-m-d') }}</td>
                                        <td>{{ Str::limit($transaction->payee_name, 20) }}</td>
                                        <td class="text-{{ $transaction->transaction_type === 'deposit' ? 'success' : 'danger' }}">
                                            {{ $transaction->transaction_type === 'deposit' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->transaction_type === 'deposit' ? 'success' : 'danger' }} bg-opacity-25 text-{{ $transaction->transaction_type === 'deposit' ? 'success' : 'danger' }}">
                                                {{ $transaction->transaction_type_name }}
                                            </span>
                                        </td>
                                        <td>{{ Str::limit($transaction->transactionType->name, 15) }}</td>
                                        <td>
                                            <a href="{{ route('finance.transactions.show', $transaction) }}" class="btn btn-sm btn-outline-info">
                                                عرض
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>لا توجد معاملات في هذه الخزينة</p>
                            <a href="{{ route('finance.transactions.create') }}?treasury_id={{ $treasury->id }}" class="btn btn-primary">
                                إضافة أول معاملة
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection