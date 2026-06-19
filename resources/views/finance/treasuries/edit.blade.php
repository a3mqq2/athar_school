@extends('layouts.app')

@section('title','تعديل خزينة')

@push('styles')
@include('partials.page-styles')
@endpush

@section('content')
<div class="container py-4">
    <h3 class="page-header">تعديل خزينة</h3>
    <form action="{{ route('finance.treasuries.update',$treasury) }}" method="post" class="form-card" autocomplete="off">
        @csrf
        @method('PUT')
        <div class="form-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">اسم الخزينة</label>
                    <input type="text" name="name" value="{{ old('name',$treasury->name) }}" class="form-control" required>
                    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">الرصيد الافتتاحي</label>
                    <input type="number" class="form-control" value="{{ number_format($treasury->opening_balance,2,'.','') }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label required">المسؤول المالي</label>
                    <select name="responsible_user_id" class="form-select" required>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(old('responsible_user_id',$treasury->responsible_user_id)==$user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('responsible_user_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">الرصيد الحالي</label>
                    <input type="number" class="form-control" value="{{ number_format($treasury->current_balance,2,'.','') }}" disabled>
                </div>
            </div>
        </div>
        <div class="form-card-footer d-flex gap-2">
            <button class="btn btn-primary">حفظ</button>
            <a href="{{ route('finance.treasuries.index') }}" class="btn btn-secondary">رجوع</a>
        </div>
    </form>
</div>
@endsection
