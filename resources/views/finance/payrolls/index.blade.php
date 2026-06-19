@extends('layouts.app')

@section('title', 'ادارة صرف الرواتب')

@push('styles')
@endpush

@section('content')
<div class="container-fluid" id="app">
    <div class="form-card mt-4">
        <div class="form-card-body">
            <payroll-manager></payroll-manager>
        </div>
    </div>
</div>
@endsection
