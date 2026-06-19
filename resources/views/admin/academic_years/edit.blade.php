{{-- resources/views/admin/academic_years/create.blade.php --}}
@extends('layouts.app')

@section('title','إضافة سنة دراسية')

@push('styles')
   @include('partials.page-styles')
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4">
    <h2 class="page-header">تعديل سنة دراسية</h2>

    <div class="form-card">
        <div class="form-card-header">البيانات</div>
        <div class="form-card-body p-3">
            <form action="{{ route('admin.academic_years.update',$year) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">اسم السنة</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name',$year->name) }}">
                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">تاريخ البداية</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($year->start_date)->format('Y-m-d')) }}">
                        @error('start_date')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">تاريخ النهاية</label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date', optional($year->end_date)->format('Y-m-d')) }}">
                        @error('end_date')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="hidden" id="is_current" name="is_current" @checked(old('is_current', $year->is_current))>
                        </div>
                    </div>
                </div>

                <div class="form-card-footer p-3 text-end">
                    <a href="{{ route('admin.academic_years.index') }}" class="btn btn-secondary">رجوع</a>
                    <button class="btn btn-dark">تحديث</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
