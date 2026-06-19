@extends('layouts.app')

@section('title','إضافة خزينة')

@push('styles')
@include('partials.page-styles')
@endpush

@section('content')
<div class="container py-4">
    <h3 class="page-header">إضافة خزينة</h3>
    <form action="{{ route('finance.treasuries.store') }}" method="post" class="form-card" autocomplete="off">
        @csrf
        <div class="form-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">اسم الخزينة</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label required">الرصيد الافتتاحي</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-coins"></i></span>
                        <input type="number" name="opening_balance" step="0.01" min="0" value="{{ old('opening_balance',0) }}" class="form-control" required>
                    </div>
                    @error('opening_balance')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label required">المسؤول المالي</label>
                    <select name="responsible_user_id" class="form-select" required>
                        <option value="">اختر المسؤول</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(old('responsible_user_id')==$user->id)>{{ $user->name }} </option>
                        @endforeach
                    </select>
                    @error('responsible_user_id')<div class="text-danger small">{{ $message }}</div>@enderror
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
