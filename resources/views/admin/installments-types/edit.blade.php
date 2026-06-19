{{-- resources/views/admin/installments-types/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'تعديل نوع القسط')

@push('styles')
@include('partials.page-styles')
@endpush

@section('content')
<div class="container py-4">
    <h3 class="page-header">تعديل نوع القسط</h3>

    <div class="form-card">
        <form action="{{ route('admin.installments-types.update', $installmentType) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label required">الاسم</label>
                    <input type="text" name="name" 
                           value="{{ old('name', $installmentType->name) }}" 
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                <input type="hidden" name="status" value="{{$installmentType->status}}">
            </div>

            <div class="form-card-footer d-flex justify-content-between">
                <a href="{{ route('admin.installments-types.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-right"></i> رجوع
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> تحديث
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
