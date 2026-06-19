{{-- resources/views/finance/teacher-settlements/show.blade.php --}}
@extends('layouts.app')

@section('title','تفاصيل تسوية المعلم')

@section('content')
<div class="container-fluid py-3">
  <div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">تفاصيل تسوية #{{ $settlement->id }}</h4>
    <a href="{{ route('finance.teacher-settlements.list') }}" class="btn btn-secondary">رجوع</a>
  </div>

  <div class="card mb-3">
    <div class="card-body row g-3">
      <div class="col-md-3">المعلم: <strong>{{ $settlement->teacher->name ?? '-' }}</strong></div>
      <div class="col-md-3">الخزينة: <strong>{{ $settlement->treasury->name ?? '-' }}</strong></div>
      <div class="col-md-2">عدد الحصص: <strong>{{ $settlement->total_lessons }}</strong></div>
      <div class="col-md-2">سعر الحصة: <strong>{{ number_format($settlement->session_price,2) }}</strong></div>
      <div class="col-md-2">المحتسب: <strong>{{ number_format($settlement->calculated_amount,2) }}</strong></div>
      <div class="col-md-3">المسوّى: <span class="badge bg-primary">{{ number_format($settlement->settled_amount,2) }}</span></div>
      @if($settlement->notes)
      <div class="col-12">ملاحظات: {{ $settlement->notes }}</div>
      @endif
    </div>
  </div>

  <div class="card">
    <div class="card-header"><strong>السجلات المشمولة في التسوية</strong></div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>التاريخ</th>
              <th>عدد الحصص</th>
              <th>ملاحظة</th>
            </tr>
          </thead>
          <tbody>
            @forelse($settlement->logs as $log)
              <tr>
                <td>{{ \Carbon\Carbon::parse($log->date)->format('Y-m-d') }}</td>
                <td>{{ $log->lessons_count }}</td>
                <td>{{ $log->notes }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center">لا توجد سجلات</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
