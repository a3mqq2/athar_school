@extends('layouts.app')

@section('title', 'ترحيل الطلاب')

@push('styles')
@endpush

@section('content')
<div class="container-fluid" id="app">
    <div class="form-card mt-4">
        <div class="form-card-body">
            <student-promotion></student-promotion>
        </div>
    </div>
</div>
@endsection
