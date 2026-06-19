{{-- resources/views/finance/employee-balances/index.blade.php --}}
@extends('layouts.app')

@section('title', 'أرصدة الموظفين')

@push('styles')
<style>
:root {
    --primary-color: #925419;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --bg-light: #f8f9fa;
    --border-color: #e1e5eb;
    --text-muted: #6c757d;
}

.balance-card {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.balance-positive {
    color: var(--success-color);
    font-weight: 600;
}

.balance-negative {
    color: var(--danger-color);
    font-weight: 600;
}

.balance-zero {
    color: var(--text-muted);
    font-weight: 600;
}

.advances-total {
    color: var(--warning-color);
    font-weight: 600;
    font-size: 0.9rem;
}

.btn-balance {
    padding: 6px 12px;
    font-size: 14px;
    border-radius: 4px;
    margin: 0 2px;
}

.employee-name {
    font-weight: 600;
    color: var(--primary-color);
}

.employee-meta {
    font-size: 12px;
    color: var(--text-muted);
}

.filter-card {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>أرصدة الموظفين</h3>
        <a href="{{ route('finance.dashboard') }}" class="btn btn-secondary">العودة للوحة المالية</a>
    </div>

    <!-- فلاتر البحث -->
    <div class="filter-card">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">البحث</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       class="form-control" placeholder="اسم الموظف أو البريد أو الكود">
            </div>
            <div class="col-md-3">
                <label class="form-label">الدور</label>
                <select name="role" class="form-select">
                    <option value="">جميع الأدوار</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" @selected(request('role') == $role->name)>
                            {{ $role->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> بحث
                </button>
                <a href="{{ route('finance.employee-balances.index') }}" class="btn btn-light">
                    <i class="fas fa-redo"></i> إعادة تعيين
                </a>
                {{-- 🆕 زر الطباعة --}}
                <a href="{{ route('finance.employee-balances.print', request()->query()) }}" 
                   class="btn btn-info" 
                   target="_blank">
                    <i class="fas fa-print"></i> طباعة
                </a>
            </div>
        </form>
    </div>

    <!-- جدول الموظفين -->
    <div class="balance-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>الموظف</th>
                        <th>الدور</th>
                        <th>الرصيد الحالي</th>
                        <th>إجمالي السلف</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="employee-name">{{ $user->name }}</div>
                                <div class="employee-meta">
                                    {{ $user->email }}
                                    @if($user->code)
                                        • {{ $user->code }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-secondary">{{ $role->display_name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="balance-display {{ $user->balance > 0 ? 'balance-positive' : ($user->balance < 0 ? 'balance-negative' : 'balance-zero') }}" 
                                      id="balance-{{ $user->id }}">
                                    {{ number_format($user->balance, 2) }} د.ل
                                </span>
                            </td>
                            <td>
                                <span class="advances-total" id="advances-{{ $user->id }}">
                                    {{ number_format($user->total_advances ?? 0, 2) }} د.ل
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-success btn-balance btn-sm" 
                                        onclick="openBalanceModal({{ $user->id }}, '{{ $user->name }}', 'add')">
                                    + إضافة
                                </button>
                                <button type="button" class="btn btn-danger btn-balance btn-sm" 
                                        onclick="openBalanceModal({{ $user->id }}, '{{ $user->name }}', 'subtract')">
                                    - خصم
                                </button>
                                
                                @if ($user && $user->hasAnyRole(['supervisor', 'admin', 'finance']))
                                    {{-- 🆕 زر إدارة السلف بدلاً من زر سلفة واحد --}}
                                    <a href="{{ route('finance.employee-balances.advances', $user) }}" 
                                       class="btn btn-warning btn-balance btn-sm">
                                        <i class="fas fa-wallet"></i> السلف
                                    </a>
                                @endif
                            
                                <a href="{{ route('finance.employee-balances.statement', $user) }}" 
                                   class="btn btn-outline-primary btn-balance btn-sm">
                                    كشف الحساب
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                لا توجد بيانات موظفين
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="p-3 border-top">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal تحديث الرصيد -->
<div class="modal fade" id="balanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">تحديث رصيد الموظف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="balanceForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="userId" name="user_id">
                    <input type="hidden" id="operationType" name="type">
                    
                    <div class="mb-3">
                        <label class="form-label">اسم الموظف</label>
                        <input type="text" id="employeeName" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="amount" class="form-control" 
                               step="0.01" min="0.01" required>
                    </div>

                    <div class="mb-3" id="treasuryField" style="display:none;">
                        <label class="form-label">الخزينة <span class="text-danger">*</span></label>
                        <select name="treasury_id" id="treasury_id" class="form-select">
                            <option value="">اختر الخزينة</option>
                            @foreach($treasuries as $treasury)
                                <option value="{{ $treasury->id }}">
                                    {{ $treasury->name }} - {{ number_format($treasury->current_balance, 2) }} د.ل
                                </option>
                            @endforeach
                        </select>
                    </div>
                    

                    <div class="mb-3">
                        <label class="form-label">الوصف <span class="text-danger">*</span></label>
                        <textarea name="description" id="description" class="form-control" 
                                  rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        تحديث الرصيد
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal السلفة -->
<div class="modal fade" id="advanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة سلفة للموظف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="advanceForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="advanceUserId">
                    
                    <div class="mb-3">
                        <label class="form-label">اسم الموظف</label>
                        <input type="text" id="advanceEmployeeName" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">مبلغ السلفة <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="advanceAmount" class="form-control" 
                               step="0.01" min="0.01" required>
                    </div>

                    <div class="mb-3">
                     <label class="form-label">الاستقطاع الشهري <span class="text-danger">*</span></label>
                     <input type="number" name="monthly_deduction" id="monthlyDeduction" class="form-control" 
                            step="0.01" min="0.01" required>
                     <small class="text-muted">المبلغ الذي سيتم استقطاعه شهرياً من راتب الموظف</small>
                 </div>

                    <div class="mb-3">
                        <label class="form-label">الخزينة <span class="text-danger">*</span></label>
                        <select name="treasury_id" id="advanceTreasuryId" class="form-select" required>
                            <option value="">اختر الخزينة</option>
                            @foreach($treasuries as $treasury)
                                <option value="{{ $treasury->id }}">
                                    {{ $treasury->name }} - {{ number_format($treasury->current_balance, 2) }} د.ل
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">الوصف <span class="text-danger">*</span></label>
                        <textarea name="description" id="advanceDescription" class="form-control" 
                                  rows="3" required placeholder="سبب السلفة..."></textarea>
                    </div>

                    <div class="alert alert-warning">
                        <small><i class="fas fa-info-circle"></i> ستظهر السلفة كحركة سالبة في رصيد الموظف</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning" id="advanceSubmitBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        إضافة السلفة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentUserId = null;
    let currentOperation = null;
    
    // التأكد من تحميل Bootstrap
    if (typeof bootstrap == 'undefined') {
        console.error('Bootstrap JS not loaded');
        return;
    }
    
    const balanceModalEl = document.getElementById('balanceModal');
    const advanceModalEl = document.getElementById('advanceModal');
    
    let balanceModal, advanceModal;
    
    try {
        balanceModal = new bootstrap.Modal(balanceModalEl);
        advanceModal = new bootstrap.Modal(advanceModalEl);
    } catch (error) {
        console.error('Error initializing modals:', error);
        return;
    }

    window.openBalanceModal = function(userId, userName, operation) {
    currentUserId = userId;
    currentOperation = operation;
    
    document.getElementById('userId').value = userId;
    document.getElementById('employeeName').value = userName;
    document.getElementById('operationType').value = operation;
    
    const title = operation == 'add' ? 'إضافة رصيد للموظف' : 'خصم رصيد من الموظف';
    document.getElementById('modalTitle').textContent = title;
    
    // مسح القيم السابقة
    document.getElementById('amount').value = '';
    document.getElementById('description').value = '';
    document.getElementById('treasury_id').value = '';
    
    // إظهار/إخفاء حقل الخزينة
    const treasuryField = document.getElementById('treasuryField');
    if (operation == 'subtract') {
        treasuryField.style.display = 'block';
        document.getElementById('treasury_id').setAttribute('required', 'required');
    } else {
        treasuryField.style.display = 'none';
        document.getElementById('treasury_id').removeAttribute('required');
    }
    
    balanceModal.show();
};

    window.openAdvanceModal = function(userId, userName) {
        document.getElementById('advanceUserId').value = userId;
        document.getElementById('advanceEmployeeName').value = userName;
        
        // مسح القيم السابقة
        document.getElementById('advanceAmount').value = '';
        document.getElementById('advanceTreasuryId').value = '';
        document.getElementById('advanceDescription').value = '';
        
        advanceModal.show();
    };

      // تحديث الرصيد
      document.getElementById('balanceForm').addEventListener('submit', function(e) {
         e.preventDefault();
         
         const submitBtn = document.getElementById('submitBtn');
         const spinner = submitBtn.querySelector('.spinner-border');
         const formData = new FormData(this);
         
         submitBtn.disabled = true;
         spinner.classList.remove('d-none');
         
         fetch(`/finance/employee-balances/${currentUserId}/update-balance`, {
            method: 'POST',
            headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                  'Accept': 'application/json'
            },
            body: formData
         })
         .then(response => response.json())
         .then(data => {
            if (data.success) {
                  // تحديث الرصيد في الجدول
                  const balanceElement = document.getElementById(`balance-${currentUserId}`);
                  if (balanceElement) {
                     balanceElement.textContent = data.new_balance + ' د.ل';
                     
                     // تحديث لون الرصيد
                     const balance = parseFloat(data.new_balance.replace(/,/g, ''));
                     balanceElement.className = `balance-display ${balance > 0 ? 'balance-positive' : (balance < 0 ? 'balance-negative' : 'balance-zero')}`;
                  }
                  
                  balanceModal.hide();
                  
                  // إضافة رابط طباعة الإيصال إذا كان transaction_id موجود
                  let message = data.message;
                  if (data.transaction_id) {
                     message += `<br><a href="/finance/transactions/${data.transaction_id}/receipt" class="btn btn-success btn-sm mt-2" target="_blank"><i class="fas fa-print"></i> طباعة الإيصال</a>`;
                  }
                  
                  showAlert('success', message);
            } else {
                  showAlert('danger', data.message);
            }
         })
         .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'حدث خطأ أثناء معالجة الطلب');
         })
         .finally(() => {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
         });
      });

      // إضافة السلفة
      document.getElementById('advanceForm').addEventListener('submit', function(e) {
         e.preventDefault();
         
         const submitBtn = document.getElementById('advanceSubmitBtn');
         const spinner = submitBtn.querySelector('.spinner-border');
         const formData = new FormData(this);
         const userId = document.getElementById('advanceUserId').value;
         
         submitBtn.disabled = true;
         spinner.classList.remove('d-none');
         
         fetch(`/finance/employee-balances/${userId}/add-advance`, {
            method: 'POST',
            headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                  'Accept': 'application/json'
            },
            body: formData
         })
         .then(response => response.json())
         .then(data => {
            if (data.success) {
                  // تحديث الرصيد في الجدول
                  const balanceElement = document.getElementById(`balance-${userId}`);
                  if (balanceElement) {
                     balanceElement.textContent = data.new_balance + ' د.ل';
                     
                     // تحديث لون الرصيد
                     const balance = parseFloat(data.new_balance.replace(/,/g, ''));
                     balanceElement.className = `balance-display ${balance > 0 ? 'balance-positive' : (balance < 0 ? 'balance-negative' : 'balance-zero')}`;
                  }
                  
                  // تحديث إجمالي السلف
                  const advancesElement = document.getElementById(`advances-${userId}`);
                  if (advancesElement) {
                     advancesElement.textContent = data.total_advances + ' د.ل';
                  }
                  
                  advanceModal.hide();
                  
                  // إضافة رابط طباعة الإيصال إذا كان transaction_id موجود
                  let message = data.message;
                  if (data.transaction_id) {
                     message += `<br><a href="/finance/transactions/${data.transaction_id}/receipt" class="btn btn-success btn-sm mt-2" target="_blank"><i class="fas fa-print"></i> طباعة الإيصال</a>`;
                  }
                  
                  showAlert('success', message);
            } else {
                  showAlert('danger', data.message);
            }
         })
         .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'حدث خطأ أثناء معالجة الطلب');
         })
         .finally(() => {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
         });
      });
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.container');
        if (container) {
            container.insertBefore(alertDiv, container.children[1]);
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    }
});
</script>
@endpush