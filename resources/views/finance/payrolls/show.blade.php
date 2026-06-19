@extends('layouts.app')

@section('title', 'تفاصيل الرواتب')

@section('content')
<div class="container-fluid py-3">

  <h4 class="page-header">تفاصيل الرواتب لشهر {{ $payroll->month }}</h4>


  <div class="card">
    <div class="card-body">
      <h5 class="mb-3">قائمة الموظفين</h5>
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>الموظف</th>
              <th>الراتب الأساسي</th>
              <th>المكافآت</th>
              <th>الخصومات</th>
              <th>الصافي</th>
              <th>ملاحظات</th>
            </tr>
          </thead>
          <tbody>
            @forelse($payroll->items as $item)
              <tr>
                <td>{{ $item->user->name ?? '-' }}</td>
                <td>{{ number_format($item->base_salary,2) }}</td>
                <td class="text-success">{{ number_format($item->bonus,2) }}</td>
                <td class="text-danger">{{ number_format($item->deduction,2) }}</td>
                <td class="fw-bold">{{ number_format($item->net_amount,2) }}</td>
                <td>{{ $item->notes }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center">لا توجد بيانات</td>
              </tr>
            @endforelse
          </tbody>

          @if($payroll->items->count())
          <tfoot class="table-light fw-bold">
            <tr>
              <td>الإجمالي</td>
              <td>{{ number_format($payroll->items->sum('base_salary'),2) }}</td>
              <td class="text-success">{{ number_format($payroll->items->sum('bonus'),2) }}</td>
              <td class="text-danger">{{ number_format($payroll->items->sum('deduction'),2) }}</td>
              <td>{{ number_format($payroll->items->sum('net_amount'),2) }}</td>
              <td>-</td>
            </tr>
          </tfoot>
          @endif
        </table>
      </div>
    </div>
  </div>

</div>
@endsection
