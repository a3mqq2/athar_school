@extends('layouts.app')

@section('title','الخزائن المالية')

@push('styles')
@include('partials.page-styles')
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="page-header">الخزائن المالية</h3>
        <a href="{{ route('finance.treasuries.create') }}" class="btn btn-primary">إضافة خزينة</a>
    </div>

    <div class="filter-card mb-3 p-3 border rounded">
        <form method="get" action="{{ route('finance.treasuries.index') }}" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="ابحث باسم الخزينة">
            </div>
            <div class="col-md-4">
                <select name="responsible_user_id" class="form-select">
                    <option value="">المسؤول المالي</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected($responsibleId==$user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex">
                <button class="btn btn-secondary me-2">تصفية</button>
                <a href="{{ route('finance.treasuries.index') }}" class="btn btn-light">إلغاء</a>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-card table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>الاسم</th>
                    <th>الرصيد الافتتاحي</th>
                    <th>الرصيد الحالي</th>
                    <th>المسؤول المالي</th>
                    <th>أضيف بتاريخ</th>
                    <th class="text-end">إجراءات</th>
                </tr>
            </thead>
            <tbody>
            @forelse($treasuries as $treasury)
                <tr id="row-{{ $treasury->id }}">
                    <td><a href="{{ route('finance.treasuries.show',$treasury) }}">{{ $treasury->name }}</a></td>
                    <td>{{ number_format($treasury->opening_balance,2) }}</td>
                    <td>{{ number_format($treasury->current_balance,2) }}</td>
                    <td>{{ optional($treasury->responsible)->name }}</td>
                    <td>{{ $treasury->created_at->format('Y-m-d') }}</td>
                    <td class="text-end">
                        <a href="{{ route('finance.treasuries.edit',$treasury) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                        <form method="POST" action="{{ route('finance.treasuries.destroy',$treasury) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('تأكيد الحذف؟')">حذف</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">لا توجد خزائن</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $treasuries->links() }}
    </div>
</div>
@endsection
