{{-- resources/views/finance/teacher-settlements/index.blade.php --}}
@extends('layouts.app')

@section('title','تسوية حصص المعلمين')

@push('styles')
<style>
  /* Root variables for consistent theming */
  :root {
    --primary-color: #925419;
    --primary-dark: #7a4515;
    --primary-light: #a66322;
    --accent-color: #fbc417;
    --accent-hover: #e6b115;
    --success-color: #28a745;
    --info-color: #17a2b8;
    --danger-color: #dc3545;
    --bg-light: #f8f9fa;
    --bg-lighter: #fafbfc;
    --border-color: #e1e5eb;
    --text-muted: #6c757d;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
    --shadow-md: 0 2px 6px rgba(0,0,0,0.1);
  }

  /* Base container */
  .settlements-container {
    background: #f5f7fa;
    min-height: calc(100vh - 80px);
    padding: 20px 0;
  }

  /* Page Header */
  .page-header-section {
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

  /* Buttons */
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

  .btn-custom-primary {
    background: var(--accent-color);
    color: var(--primary-color);
    border: none;
    font-weight: 500;
    padding: 10px 20px;
    border-radius: 4px;
    transition: all 0.2s ease;
  }

  .btn-custom-primary:hover {
    background: var(--accent-hover);
    color: var(--primary-color);
  }

  .btn-custom-success {
    background: var(--success-color);
    border: none;
    color: white;
    padding: 10px 20px;
    border-radius: 4px;
    font-weight: 500;
    transition: all 0.2s ease;
  }

  .btn-custom-success:hover:not(:disabled) {
    background: #218838;
  }

  .btn-custom-success:disabled {
    background: #ccc;
    color: #666;
    cursor: not-allowed;
    opacity: 0.6;
  }

  /* Cards */
  .filter-card,
  .summary-card,
  .records-card {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 20px;
    overflow: hidden;
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

  /* Summary Card */
  .summary-card .card-header {
    background: var(--primary-color);
    color: white;
    padding: 15px 20px;
    font-weight: 600;
    font-size: 15px;
  }

  .summary-card .card-body {
    padding: 20px;
  }

  .summary-info {
    margin-bottom: 20px;
  }

  .summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-color);
  }

  .summary-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }

  .summary-label {
    color: var(--text-muted);
    font-weight: 500;
    font-size: 14px;
    min-width: 140px;
  }

  .summary-value {
    font-weight: 600;
    font-size: 15px;
    color: #2c3e50;
    text-align: right;
  }

  /* Badges */
  .badge-custom {
    padding: 6px 12px;
    border-radius: 3px;
    font-size: 13px;
    font-weight: 500;
    display: inline-block;
  }

  .badge-lessons {
    background: var(--primary-color);
    color: white;
  }

  .badge-price {
    background: var(--info-color);
    color: white;
  }

  .badge-amount {
    background: var(--accent-color);
    color: var(--primary-color);
    font-size: 15px;
    padding: 8px 14px;
    font-weight: 600;
  }

  /* Settlement Form */
  .settlement-form {
    border-top: 2px solid var(--accent-color);
    padding-top: 20px;
    margin-top: 20px;
  }

  /* Form Controls */
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

  /* Records Card */
  .records-card .card-header {
    background: var(--bg-light);
    border-bottom: 1px solid var(--border-color);
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .records-title {
    font-weight: 600;
    color: var(--primary-color);
    margin: 0;
    font-size: 15px;
  }

  .records-subtitle {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
    font-style: italic;
  }

  /* Alert */
  .alert-custom {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-left: 4px solid var(--primary-color);
    color: #856404;
    padding: 12px 16px;
    border-radius: 4px;
    margin-bottom: 20px;
  }

  .alert-custom strong {
    color: var(--primary-color);
  }

  /* Table */
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
  }

  .table-custom td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid var(--border-color);
    background: white;
  }

  .table-custom tbody tr:hover {
    background: var(--bg-lighter);
  }

  .table-custom tbody tr:last-child td {
    border-bottom: none;
  }

  .badge-lesson-count {
    background: var(--accent-color);
    color: var(--primary-color);
    padding: 4px 8px;
    border-radius: 3px;
    font-weight: 600;
    font-size: 12px;
  }

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--text-muted);
    font-style: italic;
    font-size: 15px;
  }

  /* Responsive Design */
  @media (max-width: 992px) {
    .summary-label {
      min-width: 120px;
    }
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

    .settlements-container {
      padding: 15px 0;
    }

    .summary-item {
      flex-direction: column;
      align-items: flex-start;
      gap: 6px;
    }

    .summary-label {
      min-width: auto;
    }

    .summary-value {
      text-align: left;
    }

    .records-card .card-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 8px;
    }

    .table-custom {
      font-size: 13px;
    }

    .table-custom th,
    .table-custom td {
      padding: 8px;
    }
  }

  /* Focus visible for accessibility */
  button:focus-visible,
  a:focus-visible,
  select:focus-visible,
  input:focus-visible,
  textarea:focus-visible {
    outline: 2px solid var(--accent-color);
    outline-offset: 2px;
  }
</style>
@endpush

@section('content')
<div class="settlements-container">
  <div class="container-fluid">
    
    <!-- Page Header -->
    <div class="page-header-section">
      <div class="page-header-content">
        <h1 class="page-title">تسوية حصص المعلمين</h1>
        <a href="{{ route('finance.teacher-settlements.list') }}" class="btn-custom-outline">
          سجل التسويات
        </a>
      </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-card">
      <div class="card-header">
        تصفية البيانات
      </div>
      <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
          <div class="col-md-8">
            <label class="form-label form-label-custom">المعلم</label>
            <select name="teacher_id" class="form-select form-control-custom" required>
              <option value="">اختر المعلم</option>
              @foreach($teachers as $t)
                <option value="{{ $t->id }}" @selected(optional($teacher)->id == $t->id)>
                  {{ $t->name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <button class="btn btn-custom-primary w-100" type="submit">
              عرض غير المسوّى
            </button>
          </div>
        </form>
      </div>
    </div>

    @if($teacher)
    <div class="row">
      <!-- Summary Section -->
      <div class="col-lg-5 col-xl-4 mb-4">
        <div class="summary-card">
          <div class="card-header">
            الملخص
          </div>
          <div class="card-body">
            <div class="summary-info">
              <div class="summary-item">
                <span class="summary-label">المعلم:</span>
                <span class="summary-value">{{ $teacher->name }}</span>
              </div>
              <div class="summary-item">
                <span class="summary-label">عدد الحصص غير المسوّاة:</span>
                <span class="badge-custom badge-lessons">{{ $summary['total_lessons'] }}</span>
              </div>
              <div class="summary-item">
                <span class="summary-label">سعر الحصة:</span>
                <span class="badge-custom badge-price">{{ number_format($summary['session_price'], 2) }}</span>
              </div>
              <div class="summary-item">
                <span class="summary-label">المبلغ المحتسب:</span>
                <span class="badge-custom badge-amount">{{ number_format($summary['calculated_amount'], 2) }}</span>
              </div>
            </div>

            <!-- Settlement Form -->
            <div class="settlement-form">
              <form action="{{ route('finance.teacher-settlements.store') }}" method="POST">
                @csrf
                <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">

         
                <div class="mb-3">
                  <label class="form-label form-label-custom">المبلغ المراد تسويته</label>
                  <input type="number" 
                         step="0.01" 
                         min="0" 
                         name="settled_amount" 
                         class="form-control form-control-custom"
                         value="{{ old('settled_amount', $summary['calculated_amount']) }}" 
                         required>
                </div>

                <div class="alert alert-warning">
                  سيتم اضافة المبلغ الى رصيد المعلم.
                </div>

                <div class="mb-3">
                  <label class="form-label form-label-custom">ملاحظات</label>
                  <textarea name="notes" 
                            class="form-control form-control-custom" 
                            rows="3" 
                            placeholder="ملاحظات اختيارية...">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" 
                        class="btn btn-custom-success w-100" 
                        {{ $summary['total_lessons'] === 0 ? 'disabled' : '' }}>
                  تسوية الآن
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Records Section -->
      <div class="col-lg-7 col-xl-8 mb-4">
        <div class="records-card">
          <div class="card-header">
            <div>
              <h5 class="records-title">السجلات غير المسوّاة</h5>
            </div>
            <small class="records-subtitle">سيتم تسوية كل السجلات الظاهرة</small>
          </div>
          <div class="card-body">
            
            @if($summary['total_lessons'] > 0)
            <div class="alert-custom">
              <strong>ملاحظة:</strong> سيتم تسوية جميع السجلات المعروضة أدناه عند الضغط على "تسوية الآن"
            </div>
            @endif
            
            <div class="table-responsive">
              <table class="table table-custom">
                <thead>
                  <tr>
                    <th width="30%">التاريخ</th>
                    <th width="25%">عدد الحصص</th>
                    <th width="45%">ملاحظة</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($logs as $log)
                    <tr>
                      <td>{{ \Carbon\Carbon::parse($log->date)->format('Y-m-d') }}</td>
                      <td>
                        <span class="badge-lesson-count">{{ $log->lessons_count }}</span>
                      </td>
                      <td>{{ $log->notes ?: '-' }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="3" class="empty-state">
                        لا توجد سجلات غير مسوّاة
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif

  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Simple form validation
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#dc3545';
                    isValid = false;
                } else {
                    field.style.borderColor = '#e1e5eb';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('يرجى ملء جميع الحقول المطلوبة');
            }
        });
    });
    
    // Simple loading state for settlement form
    const settlementForm = document.querySelector('form[action*="teacher-settlements"]');
    if (settlementForm) {
        settlementForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.innerHTML = 'جاري التسوية...';
                submitBtn.disabled = true;
            }
        });
    }
});
</script>
@endpush