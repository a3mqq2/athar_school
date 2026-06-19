{{-- resources/views/finance/teacher-settlements/list.blade.php --}}
@extends('layouts.app')

@section('title','سجل تسويات المعلمين')

@push('styles')
<style>
  :root {
    --primary-color: #925419;
    --primary-dark: #7a4515;
    --accent-color: #fbc417;
    --accent-hover: #e6b115;
    --success-color: #28a745;
    --info-color: #17a2b8;
    --bg-light: #f8f9fa;
    --border-color: #e1e5eb;
    --text-muted: #6c757d;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
  }

  .settlements-list-container {
    background: #f5f7fa;
    min-height: calc(100vh - 80px);
    padding: 20px 0;
  }

  .page-header {
    background: white;
    padding: 20px 25px;
    border-radius: 4px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-sm);
    border-left: 4px solid var(--primary-color);
  }

  .page-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .page-title {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: var(--primary-color);
  }

  .btn-custom-primary {
    background: var(--accent-color);
    color: var(--primary-color);
    border: none;
    font-weight: 500;
    padding: 10px 20px;
    border-radius: 4px;
    text-decoration: none;
    transition: all 0.2s ease;
  }

  .btn-custom-primary:hover {
    background: var(--accent-hover);
    color: var(--primary-color);
    text-decoration: none;
  }

  .btn-custom-outline {
    background: white;
    color: var(--primary-color);
    border: 1px solid var(--primary-color);
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
  }

  .btn-custom-outline:hover {
    background: var(--primary-color);
    color: white;
    text-decoration: none;
  }

  .btn-custom-info {
    background: var(--info-color);
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
  }

  .btn-custom-info:hover {
    background: #138496;
    color: white;
    text-decoration: none;
  }

  .filter-card {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 20px;
  }

  .filter-card .card-header {
    background: var(--bg-light);
    border-bottom: 1px solid var(--border-color);
    padding: 15px 20px;
    font-weight: 600;
    color: var(--primary-color);
    font-size: 15px;
  }

  .filter-card .card-body {
    padding: 20px;
  }

  .data-card {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    box-shadow: var(--shadow-sm);
  }

  .data-card .card-header {
    background: var(--bg-light);
    border-bottom: 1px solid var(--border-color);
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .data-title {
    font-weight: 600;
    color: var(--primary-color);
    margin: 0;
    font-size: 15px;
  }

  .data-subtitle {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
  }

  .form-control-custom,
  .form-select {
    border: 1px solid var(--border-color);
    border-radius: 4px;
    padding: 10px 12px;
    font-size: 14px;
    transition: border-color 0.2s ease;
    background: white;
  }

  .form-control-custom:focus,
  .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(146, 84, 25, 0.1);
    outline: none;
  }

  .form-label-custom {
    color: #2c3e50;
    font-weight: 500;
    margin-bottom: 6px;
    font-size: 14px;
  }

  .table-custom {
    margin-bottom: 0;
    border: none;
  }

  .table-custom th {
    background: var(--bg-light);
    color: var(--primary-color);
    font-weight: 600;
    border-bottom: 1px solid var(--border-color);
    padding: 12px;
    text-align: center;
    font-size: 14px;
    white-space: nowrap;
  }

  .table-custom td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid var(--border-color);
    background: white;
    vertical-align: middle;
  }

  .table-custom tbody tr:hover {
    background: #fafbfc;
  }

  .table-custom tbody tr:last-child td {
    border-bottom: none;
  }

  .badge-custom {
    padding: 6px 12px;
    border-radius: 3px;
    font-size: 13px;
    font-weight: 500;
    display: inline-block;
  }

  .badge-amount {
    background: var(--accent-color);
    color: var(--primary-color);
    font-weight: 600;
  }

  .badge-lessons {
    background: var(--primary-color);
    color: white;
  }

  .empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--text-muted);
    font-style: italic;
    font-size: 15px;
  }

  .stats-row {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    padding: 15px 20px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-sm);
  }

  .stat-item {
    text-align: center;
  }

  .stat-value {
    font-size: 20px;
    font-weight: 600;
    color: var(--primary-color);
    display: block;
  }

  .stat-label {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 4px;
  }

  @media (max-width: 768px) {
    .page-header-content {
      flex-direction: column;
      gap: 15px;
      text-align: center;
    }

    .page-title {
      font-size: 20px;
    }

    .settlements-list-container {
      padding: 15px 0;
    }

    .table-custom {
      font-size: 12px;
    }

    .table-custom th,
    .table-custom td {
      padding: 8px 6px;
    }

    .stat-item {
      margin-bottom: 15px;
    }
  }
</style>
@endpush

@section('content')
<div class="settlements-list-container">
  <div class="container-fluid">
    
    <!-- Page Header -->
    <h1 class="page-title mb-3">سجل تسويات المعلمين</h1>

    <div class=" m-4">
      <a href="{{ route('finance.teacher-settlements.index') }}" class="btn-custom-primary">
         تسوية جديدة
       </a>
    </div>


    <!-- Filters -->
    <div class="filter-card">
      <div class="card-header">
        تصفية البيانات
      </div>
      <div class="card-body">
        <form method="GET" class="row g-3">
          <div class="col-md-3">
            <label class="form-label form-label-custom">المعلم</label>
            <select name="teacher_id" class="form-select form-control-custom">
              <option value="">جميع المعلمين</option>
              @foreach(\App\Models\User::whereHas('roles', fn($q)=>$q->where('name','teacher'))->orderBy('name')->get(['id','name']) as $teacher)
                <option value="{{ $teacher->id }}" @selected(request('teacher_id') == $teacher->id)>
                  {{ $teacher->name }}
                </option>
              @endforeach
            </select>
          </div>
          
          <div class="col-md-3">
            <label class="form-label form-label-custom">الخزينة</label>
            <select name="treasury_id" class="form-select form-control-custom">
              <option value="">جميع الخزائن</option>
              @foreach(\App\Models\Treasury::orderBy('name')->get(['id','name']) as $treasury)
                <option value="{{ $treasury->id }}" @selected(request('treasury_id') == $treasury->id)>
                  {{ $treasury->name }}
                </option>
              @endforeach
            </select>
          </div>
          
          <div class="col-md-2">
            <label class="form-label form-label-custom">من تاريخ</label>
            <input type="date" name="date_from" class="form-control form-control-custom" value="{{ request('date_from') }}">
          </div>
          
          <div class="col-md-2">
            <label class="form-label form-label-custom">إلى تاريخ</label>
            <input type="date" name="date_to" class="form-control form-control-custom" value="{{ request('date_to') }}">
          </div>
          
          <div class="col-md-2 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-custom-primary">تطبيق</button>
            <a href="{{ route('finance.teacher-settlements.list') }}" class="btn btn-custom-outline">إعادة تعيين</a>
          </div>
        </form>
      </div>
    </div>

    <!-- Data Table -->
    <div class="data-card">
      <div class="card-header">
        <div>
          <h5 class="data-title">قائمة التسويات</h5>
          <small class="data-subtitle">{{ $settlements->total() }} تسوية</small>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-custom">
            <thead>
              <tr>
                <th width="15%">المعلم</th>
                <th width="12%">الخزينة</th>
                <th width="8%">الحصص</th>
                <th width="10%">سعر الحصة</th>
                <th width="12%">المحتسب</th>
                <th width="12%">المسوّى</th>
                <th width="15%">التاريخ</th>
                <th width="8%">الإجراء</th>
              </tr>
            </thead>
            <tbody>
              @forelse($settlements as $s)
                <tr>
                  <td class="text-start">{{ $s->teacher->name ?? '-' }}</td>
                  <td>{{ $s->treasury->name ?? '-' }}</td>
                  <td>
                    <span class="badge-custom badge-lessons">{{ $s->total_lessons }}</span>
                  </td>
                  <td>{{ number_format($s->session_price, 2) }}</td>
                  <td>{{ number_format($s->calculated_amount, 2) }}</td>
                  <td>
                    <span class="badge-custom badge-amount">{{ number_format($s->settled_amount, 2) }}</span>
                  </td>
                  <td>{{ $s->created_at->format('Y-m-d H:i') }}</td>
                  <td>
                    <a href="{{ route('finance.teacher-settlements.show', $s) }}" class="btn-custom-info">
                      عرض
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="empty-state">
                    لا توجد تسويات للعرض
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($settlements->hasPages())
        <div class="mt-3">
          {{ $settlements->appends(request()->query())->links() }}
        </div>
        @endif
      </div>
    </div>

  </div>
</div>
@endsection
