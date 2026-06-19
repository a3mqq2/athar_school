@extends('layouts.app')

@section('title', 'الرواتب المصروفة')

@section('content')
<div class="container-fluid py-3">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-header mb-0">قائمة الرواتب المصروفة</h4>
    <a href="{{ route('finance.payrolls.index') }}" class="btn btn-success">
      <i class="ph-duotone ph-plus-circle"></i>
      إدارة الرواتب
    </a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="GET" class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">الشهر</label>
          <input type="month" name="month" value="{{ request('month') }}" class="form-control">
        </div>
    
        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-primary me-2" type="submit">بحث</button>
          <a href="{{ route('finance.payrolls.list') }}" class="btn btn-secondary">إعادة تعيين</a>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>الشهر</th>
              <th>الإجمالي</th>
              <th>أنشئ بواسطة</th>
              <th>تاريخ الإنشاء</th>
              <th>التحكم</th>
            </tr>
          </thead>
          <tbody>
            @forelse($payrolls as $p)
              <tr>
                <td>{{ $p->month }}</td>
                <td><span class="badge bg-primary">{{ number_format($p->total_amount, 2) }}</span></td>
                <td>{{ $p->creator->name ?? '-' }}</td>
                <td>{{ $p->created_at->format('Y-m-d H:i') }}</td>
                <td>
                  <a href="{{ route('finance.payrolls.show', $p) }}" class="btn btn-sm btn-outline-info">عرض التفاصيل</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center">لا توجد بيانات</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $payrolls->withQueryString()->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
