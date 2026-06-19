{{-- resources/views/finance/employee-balances/advances.blade.php --}}
@extends('layouts.app')

@section('title', 'إدارة سلف الموظف')

@push('styles')
<style>
:root {
    --primary-color: #925419;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --info-color: #17a2b8;
}

.advance-card {
    background: white;
    border: 1px solid #e1e5eb;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    transition: all 0.3s;
}

.advance-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.advance-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.advance-amount {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-color);
}

.advance-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.status-active {
    background: #fff3cd;
    color: #856404;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.progress-bar-custom {
    height: 25px;
    border-radius: 12px;
    background: #e9ecef;
    overflow: hidden;
    margin: 15px 0;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--success-color), var(--info-color));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 13px;
    transition: width 0.5s ease;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin: 15px 0;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 5px;
}

.info-value {
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.employee-summary {
    background: linear-gradient(135deg, var(--primary-color), #b67643);
    color: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.summary-item {
    text-align: center;
}

.summary-label {
    font-size: 13px;
    opacity: 0.9;
    margin-bottom: 8px;
}

.summary-value {
    font-size: 26px;
    font-weight: 700;
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>إدارة سلف: {{ $user->name }}</h3>
        <div>
            <button type="button" class="btn btn-success" onclick="openAddAdvanceModal()">
                <i class="fas fa-plus"></i> إضافة سلفة جديدة
            </button>
            <a href="{{ route('finance.employee-balances.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> العودة
            </a>
        </div>
    </div>

    <!-- ملخص الموظف -->
    <div class="employee-summary">
        <h4 class="mb-0">{{ $user->name }}</h4>
        <p class="mb-0 opacity-75">{{ $user->email }}</p>
        
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">الرصيد الحالي</div>
                <div class="summary-value">{{ number_format($user->balance, 2) }} د.ل</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">إجمالي السلف النشطة</div>
                <div class="summary-value">{{ number_format($totalActive, 2) }} د.ل</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">عدد السلف</div>
                <div class="summary-value">{{ $advances->total() }}</div>
            </div>
        </div>
    </div>

    <!-- قائمة السلف -->
    @forelse($advances as $advance)
        <div class="advance-card">
            <div class="advance-header">
                <div>
                    <div class="advance-amount">{{ number_format($advance->amount, 2) }} د.ل</div>
                    <small class="text-muted">{{ $advance->advance_date->format('Y-m-d') }}</small>
                </div>
                <div>
                    <span class="advance-status status-{{ $advance->status }}">
                        {{ $advance->status == 'active' ? 'نشطة' : 'مكتملة' }}
                    </span>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">المبلغ الأصلي</span>
                    <span class="info-value">{{ number_format($advance->amount, 2) }} د.ل</span>
                </div>
                <div class="info-item">
                    <span class="info-label">المتبقي</span>
                    <span class="info-value text-danger">{{ number_format($advance->remaining_amount, 2) }} د.ل</span>
                </div>
                <div class="info-item">
                    <span class="info-label">المدفوع</span>
                    <span class="info-value text-success">{{ number_format($advance->paid_amount, 2) }} د.ل</span>
                </div>
                <div class="info-item">
                    <span class="info-label">الاستقطاع الشهري</span>
                    <span class="info-value">{{ number_format($advance->monthly_deduction, 2) }} د.ل</span>
                </div>
                <div class="info-item">
                    <span class="info-label">الأشهر المتبقية</span>
                    <span class="info-value">{{ $advance->months_required }} شهر</span>
                </div>
                <div class="info-item">
                    <span class="info-label">الخزينة</span>
                    <span class="info-value">{{ $advance->treasury->name }}</span>
                </div>
            </div>

            <!-- شريط التقدم -->
            <div class="progress-bar-custom">
                @php
                    $percentage = $advance->amount > 0 ? (($advance->amount - $advance->remaining_amount) /$advance->amount) * 100 : 0;
                @endphp
                <div class="progress-fill" style="width: {{ $percentage }}%">
                    {{ number_format($percentage, 1) }}%
                </div>
            </div>

            <div class="mb-3">
                <span class="info-label">الوصف:</span>
                <p class="mb-0 mt-1">{{ $advance->description }}</p>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                @if($advance->status == 'active')
                    <button class="btn btn-sm btn-primary" 
                            onclick="openEditAdvanceModal({{ $advance->id }}, 'increase')">
                        <i class="fas fa-plus"></i> زيادة المبلغ
                    </button>
                    <button class="btn btn-sm btn-warning" 
                            onclick="openEditAdvanceModal({{ $advance->id }}, 'decrease')">
                        <i class="fas fa-minus"></i> تقليل المبلغ
                    </button>
                    <button class="btn btn-sm btn-info" 
                            onclick="openEditMonthlyModal({{ $advance->id }}, {{ $advance->monthly_deduction }}, {{ $advance->remaining_amount }})">
                        <i class="fas fa-edit"></i> تعديل الاستقطاع
                    </button>
                @endif
                
                    <button class="btn btn-sm btn-danger" 
                            onclick="deleteAdvance({{ $advance->id }})">
                        <i class="fas fa-trash"></i> حذف
                    </button>
            </div>
        </div>
    @empty
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-2x mb-3"></i>
            <p class="mb-0">لا توجد سلف مسجلة لهذا الموظف</p>
        </div>
    @endforelse

    <!-- Pagination -->
    @if($advances->hasPages())
        <div class="mt-4">
            {{ $advances->links() }}
        </div>
    @endif
</div>

<!-- Modal إضافة سلفة جديدة -->
<div class="modal fade" id="addAdvanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة سلفة جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAdvanceForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> يمكن إضافة أكثر من سلفة للموظف
                    </div>

                    <div class="mb-3">
                        <label class="form-label">مبلغ السلفة <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="newAdvanceAmount" 
                               class="form-control" step="0.01" min="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">الاستقطاع الشهري <span class="text-danger">*</span></label>
                        <input type="number" name="monthly_deduction" id="newMonthlyDeduction" 
                               class="form-control" step="0.01" min="0.01" required>
                        <small class="text-muted">المبلغ الذي سيتم استقطاعه شهرياً</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">الخزينة <span class="text-danger">*</span></label>
                        <select name="treasury_id" id="newTreasuryId" class="form-select" required>
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
                        <textarea name="description" id="newDescription" 
                                  class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">
                        <span class="spinner-border spinner-border-sm d-none"></span>
                        إضافة السلفة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal تعديل مبلغ السلفة -->
<div class="modal fade" id="editAdvanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalTitle">تعديل السلفة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAdvanceForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editAdvanceId">
                <input type="hidden" id="editAdjustmentType" name="adjustment_type">
                
                <div class="modal-body">
                    <div class="alert alert-warning" id="editWarning"></div>

                    <div class="mb-3">
                        <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                        <input type="number" name="adjustment_amount" id="editAdjustmentAmount" 
                               class="form-control" step="0.01" min="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">الخزينة <span class="text-danger">*</span></label>
                        <select name="treasury_id" id="editTreasuryId" class="form-select" required>
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
                        <textarea name="description" id="editDescription" 
                                  class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none"></span>
                        تحديث
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal تعديل الاستقطاع الشهري -->
<div class="modal fade" id="editMonthlyModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل الاستقطاع الشهري</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editMonthlyForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="monthlyAdvanceId">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الاستقطاع الشهري الجديد <span class="text-danger">*</span></label>
                        <input type="number" name="new_monthly_deduction" id="newMonthlyAmount" 
                               class="form-control" step="0.01" min="0.01" required>
                        <small class="text-muted" id="monthlyHint"></small>
                    </div>

                    <input type="hidden" name="update_monthly_deduction" value="1">
                    <input type="hidden" name="adjustment_amount" value="0.01">
                    <input type="hidden" name="adjustment_type" value="increase">
                    <input type="hidden" name="treasury_id" value="{{ $treasuries->first()->id ?? '' }}">
                    <input type="hidden" name="description" value="تعديل الاستقطاع الشهري">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-info">
                        <span class="spinner-border spinner-border-sm d-none"></span>
                        تحديث
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
    // Initialize Modals
    const addAdvanceModal = new bootstrap.Modal(document.getElementById('addAdvanceModal'));
    const editAdvanceModal = new bootstrap.Modal(document.getElementById('editAdvanceModal'));
    const editMonthlyModal = new bootstrap.Modal(document.getElementById('editMonthlyModal'));

    // فتح modal إضافة سلفة
    window.openAddAdvanceModal = function() {
        document.getElementById('addAdvanceForm').reset();
        addAdvanceModal.show();
    };

    // فتح modal تعديل مبلغ السلفة
    window.openEditAdvanceModal = function(advanceId, type) {
        document.getElementById('editAdvanceId').value = advanceId;
        document.getElementById('editAdjustmentType').value = type;
        
        const title = type === 'increase' ? 'زيادة مبلغ السلفة' : 'تقليل مبلغ السلفة';
        const warning = type === 'increase' 
            ? '<i class="fas fa-arrow-up"></i> سيتم زيادة المبلغ وخصمه من الخزينة ورصيد الموظف'
            : '<i class="fas fa-arrow-down"></i> سيتم تقليل المبلغ وإرجاعه للخزينة ورصيد الموظف';
        
        document.getElementById('editModalTitle').textContent = title;
        document.getElementById('editWarning').innerHTML = warning;
        document.getElementById('editAdvanceForm').reset();
        document.getElementById('editAdjustmentType').value = type;
        
        editAdvanceModal.show();
    };

    // فتح modal تعديل الاستقطاع الشهري
    window.openEditMonthlyModal = function(advanceId, currentMonthly, remaining) {
        document.getElementById('monthlyAdvanceId').value = advanceId;
        document.getElementById('newMonthlyAmount').value = currentMonthly;
        document.getElementById('monthlyHint').textContent = 
            `المتبقي: ${parseFloat(remaining).toFixed(2)} د.ل - الحالي: ${parseFloat(currentMonthly).toFixed(2)} د.ل`;
        
        editMonthlyModal.show();
    };

    // إضافة سلفة جديدة
    document.getElementById('addAdvanceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const spinner = submitBtn.querySelector('.spinner-border');
        const formData = new FormData(this);
        
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        
        fetch('{{ route("finance.employee-balances.advances.store", $user) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                addAdvanceModal.hide();
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
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

    // تعديل مبلغ السلفة
    document.getElementById('editAdvanceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const spinner = submitBtn.querySelector('.spinner-border');
        const formData = new FormData(this);
        const advanceId = document.getElementById('editAdvanceId').value;
        
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        
        fetch(`/finance/employee-balances/advances/${advanceId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PUT'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                editAdvanceModal.hide();
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
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

    // تعديل الاستقطاع الشهري
    document.getElementById('editMonthlyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const spinner = submitBtn.querySelector('.spinner-border');
        const formData = new FormData(this);
        const advanceId = document.getElementById('monthlyAdvanceId').value;
        
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        
        fetch(`/finance/employee-balances/advances/${advanceId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PUT'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                editMonthlyModal.hide();
                showAlert('success', 'تم تحديث الاستقطاع الشهري بنجاح');
                setTimeout(() => location.reload(), 1500);
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

    // حذف سلفة
    window.deleteAdvance = function(advanceId) {
        if (!confirm('هل أنت متأكد من حذف هذه السلفة؟\nسيتم إرجاع المبلغ للخزينة ورصيد الموظف.')) {
            return;
        }
        
        fetch(`/finance/employee-balances/advances/${advanceId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'حدث خطأ أثناء الحذف');
        });
    };

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