@extends('layouts.app')

@section('title','تفاصيل التزامات الطالب')

@push('styles')
<style>
    :root{--primary-color:#925419;--primary-dark:#7a4515;--success-color:#28a745;--danger-color:#dc3545;--bg-light:#f8f9fa;--bg-lighter:#fafbfc;--border-color:#e1e5eb;--text-muted:#6c757d;--shadow-md:0 4px 12px rgba(0,0,0,0.08);--shadow-lg:0 8px 24px rgba(0,0,0,0.12);--transition:.3s cubic-bezier(.4,0,.2,1)}
    body{background:#f5f7fa}
    .page-header-section{background:#fff;padding:25px 30px;border-radius:12px;margin-bottom:30px;box-shadow:var(--shadow-md);border-left:5px solid var(--primary-color)}
    .page-header-content{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px}
    .student-info-header{flex:1}
    .student-name{font-size:28px;font-weight:700;color:var(--primary-color);margin:0 0 8px}
    .student-meta{display:flex;gap:20px;flex-wrap:wrap}
    .meta-item{display:flex;align-items:center;gap:8px;color:var(--text-muted);font-size:14px}
    .header-actions{display:flex;gap:10px;flex-wrap:wrap}
    .btn-primary-custom{background:var(--primary-color);color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;transition:var(--transition)}
    .btn-primary-custom:hover{background:var(--primary-dark);transform:translateY(-2px);box-shadow:var(--shadow-md);color:#fff}
    .btn-outline-custom{background:#fff;color:var(--primary-color);border:2px solid var(--primary-color);padding:10px 20px;border-radius:8px;font-weight:600;transition:var(--transition)}
    .btn-outline-custom:hover{background:var(--primary-color);color:#fff}
    .summary-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px}
    .summary-card{background:#fff;padding:20px;border-radius:12px;box-shadow:var(--shadow-md);transition:var(--transition);border-top:4px solid}
    .summary-card:hover{transform:translateY(-3px);box-shadow:var(--shadow-lg)}
    .summary-card.total{border-color:var(--primary-color)}
    .summary-card.paid{border-color:var(--success-color)}
    .summary-card.remaining{border-color:var(--danger-color)}
    .content-card{background:#fff;border-radius:12px;box-shadow:var(--shadow-md);margin-bottom:30px;overflow:hidden}
    .card-header-custom{background:var(--bg-light);padding:18px 24px;border-bottom:2px solid var(--border-color);display:flex;justify-content:space-between;align-items:center}
    .card-title{font-size:18px;font-weight:700;color:var(--primary-color);margin:0}
    .card-body-custom{padding:24px}
    .custom-table{width:100%;border-collapse:separate;border-spacing:0}
    .custom-table thead th{background:var(--bg-light);color:var(--primary-color);font-weight:600;padding:14px 16px;text-align:right;font-size:14px;border-bottom:2px solid var(--border-color);white-space:nowrap}
    .custom-table tbody td{padding:14px 16px;border-bottom:1px solid var(--border-color);font-size:14px;vertical-align:middle}
    .status-badge{padding:6px 12px;border-radius:6px;font-size:12px;font-weight:600;display:inline-block;white-space:nowrap}
    .status-paid{background:rgba(40,167,69,.1);color:var(--success-color)}
    .status-partial{background:rgba(255,193,7,.1);color:#f39c12}
    .status-overdue{background:rgba(220,53,69,.1);color:var(--danger-color)}
    .badge.bg-success { background-color:#28a745 !important; }
    .badge.bg-danger { background-color:#dc3545 !important; }
    .badge.bg-primary { background-color:#007bff !important; }
    .text-success { color:#28a745 !important; }
    .text-danger { color:#dc3545 !important; }
    .status-refunded {
    background: rgba(108,117,125,.1);
    color: #6c757d;
}

    @media (max-width:768px){.summary-cards{grid-template-columns:1fr 1fr}.custom-table{font-size:12px}.custom-table thead th,.custom-table tbody td{padding:10px 8px}}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="page-header-section">
        <div class="page-header-content">
            <div class="student-info-header">
                <h1 class="student-name">{{ $student->name }}</h1>
                <div class="student-meta">
                    <div class="meta-item"><span>الكود:</span><strong>#{{ $student->id }}</strong></div>
                    <div class="meta-item"><span>الحالة:</span><strong>{{ $student->statues[$student->status] ?? $student->status }}</strong></div>
                    <div class="meta-item"><span>الهاتف:</span><strong>{{ $student->phone }}</strong></div>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addInstallmentModal">إضافة قسط جديد</button>
                <a href="{{ route('finance.students.index') }}" class="btn btn-outline-custom">رجوع</a>
            </div>
        </div>
    </div>

    


    <div class="summary-cards">
        <div class="summary-card total"><div class="summary-label">إجمالي المستحق</div><div class="summary-value">{{ number_format($student->installments->sum('amount_due'),2) }} <small>د.ل</small></div></div>
        <div class="summary-card paid"><div class="summary-label">المدفوع</div><div class="summary-value">{{ number_format($student->installments->sum('paid_amount'),2) }} <small>د.ل</small></div></div>
        <div class="summary-card remaining"><div class="summary-label">المتبقي</div><div class="summary-value">{{ number_format($dueSum,2) }} <small>د.ل</small></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="content-card">
                <div class="card-header-custom">
                    <h5 class="card-title">الأقساط والالتزامات</h5>
                    <span class="text-muted">{{ $student->installments->count() }} قسط</span>
                </div>
                <div class="card-body-custom p-0">
                    @if($student->installments->isNotEmpty())
                        <div class="table-responsive">
                            <table class="custom-table">
                                <thead>
                                <tr>
                                    <th>النوع</th>
                                    <th>الفصل</th>
                                    <th>المستحق</th>
                                    <th>المدفوع</th>
                                    <th>المتبقي</th>
                                    <th>الحالة</th>
                                    <th>إجراء</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php $fmt = fn($v)=>number_format($v,2); @endphp
                                @foreach($student->installments as $i)
                                    @php $remaining = max(0, (float)$i->amount_due - (float)$i->paid_amount); @endphp
                                    <tr>
                                        <td><strong>{{ $i->installmentType?->name }}</strong></td>
                                        <td>{{ $i->semester_name }}</td>
                                        <td><strong>{{ $fmt($i->amount_due) }}</strong></td>
                                        <td>{{ $fmt($i->paid_amount) }}</td>
                                        <td><strong style="color: {{ $remaining>0?'var(--danger-color)':'var(--success-color)' }}">{{ $fmt($remaining) }}</strong></td>
                                        <td>
                                            <span class="status-badge status-{{ $i->status }}">
                                                @switch($i->status)
                                                    @case('due') مستحق @break
                                                    @case('partial') جزئي @break
                                                    @case('paid') مدفوع @break
                                                    @case('overdue') متأخر @break
                                                    @case('refunded') مسترد @break
                                                    @default غير محدد
                                                @endswitch
                                            </span>
                                        </td>
                                        
                                        <td>
                                            @if($remaining>0 && $i->status != 'refunded')
                                                <button type="button" class="btn btn-sm btn-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#payInstallmentModal" 
                                                        data-installment-id="{{ $i->id }}" 
                                                        data-remaining="{{ number_format($remaining,2,'.','') }}" 
                                                        data-kind="{{ $i->installmentType?->name }}" 
                                                        data-due="{{ $i->due_date?->format('Y-m-d') }}">
                                                    دفع
                                                </button>
                                            @endif
                                        
                                            @if($i->paid_amount > 0)
                                                <button type="button" class="btn btn-sm btn-warning"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#refundInstallmentModal"
                                                        data-installment-id="{{ $i->id }}"
                                                        data-paid="{{ number_format($i->paid_amount,2,'.','') }}"
                                                        data-kind="{{ $i->installmentType?->name }}"
                                                        data-due="{{ $i->due_date?->format('Y-m-d') }}">
                                                    استرداد
                                                </button>
                                            @endif
                                        
                                            <!-- زر التعديل الجديد -->
                                           @if (auth()->id() == 1)
                                           <button type="button" class="btn btn-sm btn-info"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editInstallmentModal"
                                                    data-installment-id="{{ $i->id }}"
                                                    data-installment-type-id="{{ $i->installment_type_id }}"
                                                    data-semester-number="{{ $i->semester_number }}"
                                                    data-amount-due="{{ number_format($i->amount_due,2,'.','') }}"
                                                    data-due-date="{{ $i->due_date?->format('Y-m-d') }}"
                                                    data-notes="{{ $i->notes }}"
                                                    data-paid-amount="{{ $i->paid_amount }}"
                                                    title="تعديل القسط">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <button type="button" class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteInstallmentModal"
                                                    data-installment-id="{{ $i->id }}"
                                                    data-installment-name="{{ $i->installmentType?->name }}"
                                                    data-semester="{{ $i->semester_number }}"
                                                    data-amount="{{ number_format($i->amount_due,2) }}"
                                                    title="حذف القسط">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        
                                           @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-5 text-center text-muted">لا توجد أقساط مسجلة لهذا الطالب</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="content-card">
                <div class="card-header-custom"><h5 class="card-title">معلومات الطالب</h5></div>
                <div class="card-body-custom">
                    <div class="mb-2"><div class="text-muted small">اسم ولي الأمر</div><div>{{ $student->parent_name ?: 'غير محدد' }}</div></div>
                    <div class="mb-2"><div class="text-muted small">رقم الهاتف</div><div>{{ $student->phone ?: 'غير محدد' }}</div></div>
                    <div class="mb-2"><div class="text-muted small">العنوان</div><div>{{ $student->address ?: 'غير محدد' }}</div></div>
                    <div class="mb-2"><div class="text-muted small">الحالة</div><div><span class="status-badge {{ $student->status=='active'?'status-paid':'status-partial' }}">{{ $student->statues[$student->status] ?? $student->status }}</span></div></div>
                    <div class="mb-2"><div class="text-muted small">ملاحظات</div><div>{{ $student->notes ?: 'لا توجد ملاحظات' }}</div></div>
                </div>
            </div>
        </div>
    </div>


    <div class="row g-4 mt-1">
      <div class="col-12">
          <div class="card">
              <div class="card-header-custom">
                  <h5 class="m-0">سجل المدفوعات</h5>
                  <span class="text-muted small">إجمالي: {{ $payments->total() }}</span>
              </div>
              <div class="card-body">
               <div class="p-0">
                  @if($payments->count())
                  <div class="table-responsive">
                    <table class="custom-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>التاريخ</th>
                                <th>نوع العملية</th>
                                <th>القسط</th>
                                <th>المبلغ</th>
                                <th>طريقة الدفع</th>
                                <th>الخزينة</th>
                                <th class="text-center">إيصال</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($payments as $k => $p)
                            @php
                                $isRefund = $p->type === 'refund';
                            @endphp
                            <tr>
                                <td>{{ ($payments->firstItem() + $k) }}</td>
                                <td>{{ $p->created_at?->format('Y-m-d H:i') }}</td>
                                <td>
                                    @if($isRefund)
                                        <span class="badge bg-danger">استرداد</span>
                                    @else
                                        <span class="badge bg-success">دفعة</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $p->installment?->installmentType?->name }}
                                    @if($p->installment?->semester_number) — الفصل {{ $p->installment->semester_number }} @endif
                                    <div class="text-muted small">#{{ $p->student_installment_id }}</div>
                                </td>
                                <td>
                                    <strong class="{{ $isRefund ? 'text-danger' : 'text-success' }}">
                                        {{ $fmt($p->amount) }}
                                    </strong>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        @switch($p->payment_method)
                                            @case('cash') نقدي @break
                                            @case('bank') إيداع بنكي @break
                                            @case('transfer') تحويل @break
                                            @case('pos') نقاط بيع @break
                                            @default أخرى
                                        @endswitch
                                    </span>
                                    @if($p->bank_name || $p->account_number)
                                        <div class="small text-muted mt-1">
                                            {{ $p->bank_name }} {{ $p->account_number }}
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $p->treasury?->name }}</td>
                                <td class="text-center">
                                    @if($p->transaction_id)
                                        <a href="{{ route('finance.transactions.receipt',$p->transaction_id) }}" 
                                           class="btn btn-outline-success btn-sm" title="طباعة إيصال" target="_blank">
                                           <i class="fas fa-print"></i>
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                
                      <div class="p-3">
                          {{ $payments->appends(request()->except('page'))->links() }}
                      </div>
                  @else
                      <div class="p-4 text-center text-muted">لا توجد مدفوعات لهذا الطالب</div>
                  @endif
              </div>
              </div>
          </div>
      </div>
  </div>
</div>
</div>

<div class="modal fade" id="payInstallmentModal" tabindex="-1" aria-labelledby="payInstallmentModalLabel" aria-hidden="true">
   <div class="modal-dialog">
       <form method="post" action="{{ route('finance.students.pay',$student) }}" class="modal-content" id="payInstallmentForm">
           @csrf
           <div class="modal-header">
               <h5 class="modal-title" id="payInstallmentModalLabel">دفع قسط</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <div class="modal-body">
               <input type="hidden" name="student_installment_id" id="pay_installment_id">
               <div class="mb-3">
                   <label class="form-label">القسط</label>
                   <input type="text" class="form-control" id="pay_installment_info" readonly>
               </div>
               <div class="mb-3">
                   <label class="form-label">المبلغ</label>
                   <input type="number" name="amount" id="pay_amount" class="form-control" step="0.01" min="0.01" required>
               </div>
               <div class="mb-3">
                   <label class="form-label">طريقة الدفع</label>
                   <select name="payment_method" id="pay_method" class="form-select" required>
                       <option value="cash">نقدي</option>
                       <option value="bank">إيداع بنكي</option>
                   </select>
               </div>

               <div id="bankFields" style="display: none;">
                <div class="mb-3">
                    <label class="form-label">اسم المصرف</label>
                    <input type="text" name="bank_name" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">رقم الحساب</label>
                    <input type="text" name="account_number" class="form-control">
                </div>
            </div>

            

               <div class="mb-3">
                   <label class="form-label">الخزينة</label>
                   <select name="treasury_id" id="pay_treasury_id" class="form-select" required>
                       <option value="">اختر الخزينة</option>
                       @foreach($treasuries as $t)
                           <option value="{{ $t->id }}">{{ $t->name }} — {{ number_format($t->current_balance,2) }}</option>
                       @endforeach
                   </select>
               </div>
           </div>
           <div class="modal-footer">
               <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
               <button type="submit" class="btn btn-primary" id="paySubmitBtn">تأكيد الدفع</button>
           </div>
       </form>
   </div>
</div>

<div class="modal fade" id="addInstallmentModal" tabindex="-1" aria-labelledby="addInstallmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="{{ route('finance.students.installments.store',$student) }}" class="modal-content" id="addInstallmentForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addInstallmentModalLabel">إضافة قسط جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">نوع القسط</label>
                    <select name="installment_type_id" class="form-select" required>
                        <option value="">اختر نوع القسط</option>
                        @foreach($installmentTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    
                </div>
                <div class="mb-3">
                    <label class="form-label">الفصل الدراسي</label>
                    <input type="number" name="semester_number" class="form-control" min="1" max="8" placeholder="رقم الفصل (اختياري)">
                </div>
                <div class="mb-3">
                    <label class="form-label">المبلغ المستحق</label>
                    <input type="number" name="amount_due" class="form-control" step="0.01" min="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">تاريخ الاستحقاق</label>
                    <input type="date" name="due_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="اختياري"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-primary-custom" id="addInstallmentSubmit">حفظ القسط</button>
            </div>
        </form>
    </div>
</div>


<!-- إضافة هذا Modal في نهاية الصفحة مع باقي الـ Modals -->

<!-- Modal تأكيد حذف القسط -->
<div class="modal fade" id="deleteInstallmentModal" tabindex="-1" aria-labelledby="deleteInstallmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteInstallmentModalLabel">تأكيد حذف القسط</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>تحذير:</strong> هذا الإجراء لا يمكن التراجع عنه!
                </div>
                
                <p>هل أنت متأكد من حذف القسط التالي؟</p>
                
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-4"><strong>نوع القسط:</strong></div>
                            <div class="col-sm-8" id="delete_installment_name">-</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-sm-4"><strong>الفصل:</strong></div>
                            <div class="col-sm-8" id="delete_semester">-</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-sm-4"><strong>المبلغ:</strong></div>
                            <div class="col-sm-8 text-danger" id="delete_amount">-</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form method="post" action="" id="deleteInstallmentForm" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="deleteSubmitBtn">
                        <i class="fas fa-trash me-2"></i>حذف القسط
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="editInstallmentModal" tabindex="-1" aria-labelledby="editInstallmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="" class="modal-content" id="editInstallmentForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="editInstallmentModalLabel">تعديل القسط</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">نوع القسط</label>
                    <select name="installment_type_id" id="edit_installment_type_id" class="form-select" required>
                        <option value="">اختر نوع القسط</option>
                        @foreach($installmentTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">الفصل الدراسي</label>
                    <input type="number" name="semester_number" id="edit_semester_number" class="form-control" min="1" max="8" placeholder="رقم الفصل (اختياري)">
                </div>
                <div class="mb-3">
                    <label class="form-label">المبلغ المستحق</label>
                    <input type="number" name="amount_due" id="edit_amount_due" class="form-control" step="0.01" min="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">تاريخ الاستحقاق</label>
                    <input type="date" name="due_date" id="edit_due_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" id="edit_notes" class="form-control" rows="3" placeholder="اختياري"></textarea>
                </div>
                
                <!-- تحذير في حالة وجود مدفوعات -->
                <div id="edit_warning" class="alert alert-warning" style="display: none;">
                    <strong>تنبيه:</strong> هذا القسط يحتوي على مدفوعات. تعديل المبلغ قد يؤثر على حالة القسط.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-primary-custom" id="editInstallmentSubmit">حفظ التعديل</button>
            </div>
        </form>
    </div>
</div>



<div class="modal fade" id="refundInstallmentModal" tabindex="-1" aria-labelledby="refundInstallmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="{{ route('finance.students.refund',$student) }}" class="modal-content" id="refundInstallmentForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="refundInstallmentModalLabel">استرداد قيمة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="student_installment_id" id="refund_installment_id">

                <div class="mb-3">
                    <label class="form-label">القسط</label>
                    <input type="text" class="form-control" id="refund_installment_info" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">المبلغ المسترد</label>
                    <input type="number" name="amount" id="refund_amount" class="form-control" step="0.01" min="0.01" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">طريقة الدفع</label>
                    <select name="payment_method" id="refund_method" class="form-select" required>
                        <option value="cash">نقدي</option>
                        <option value="bank">إيداع بنكي</option>
                    </select>
                </div>

                <div id="refundBankFields" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">اسم المصرف</label>
                        <input type="text" name="bank_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رقم الحساب</label>
                        <input type="text" name="account_number" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">الخزينة</label>
                    <select name="treasury_id" class="form-select" required>
                        <option value="">اختر الخزينة</option>
                        @foreach($treasuries as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} — {{ number_format($t->current_balance,2) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-warning" id="refundSubmitBtn">تأكيد الاسترداد</button>
            </div>
        </form>
    </div>
</div>



@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){
    const payModalEl=document.getElementById('payInstallmentModal');
    const payForm=document.getElementById('payInstallmentForm');
    const installmentIdInput=document.getElementById('pay_installment_id');
    const installmentInfoInput=document.getElementById('pay_installment_info');
    const amountInput=document.getElementById('pay_amount');
    const submitBtn=document.getElementById('paySubmitBtn');

    payModalEl.addEventListener('show.bs.modal',function(event){
        const button=event.relatedTarget;
        const installmentId=button.getAttribute('data-installment-id');
        const remaining=parseFloat(button.getAttribute('data-remaining')||'0');
        const kind=button.getAttribute('data-kind')||'';
        const due=button.getAttribute('data-due')||'';
        installmentIdInput.value=installmentId;
        installmentInfoInput.value=kind+(due?' | '+due:'')+' | المتبقي: '+remaining.toFixed(2)+' د.ل';
        amountInput.value=remaining.toFixed(2);
        amountInput.setAttribute('max',remaining.toFixed(2));
        amountInput.setAttribute('min','0.01');
    });

    payForm.addEventListener('submit',function(e){
        if(!amountInput.value||parseFloat(amountInput.value)<=0){e.preventDefault();return;}
        submitBtn.disabled=true;
        submitBtn.innerHTML='<span class="spinner-border spinner-border-sm me-2"></span>جاري المعالجة...';
    });

    const addForm=document.getElementById('addInstallmentForm');
    const addBtn=document.getElementById('addInstallmentSubmit');
    addForm.addEventListener('submit',function(e){
        addBtn.disabled=true;
        addBtn.innerHTML='<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...';
    });
});
</script>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const methodSelect = document.getElementById('pay_method');
    const bankFields = document.getElementById('bankFields');

    function toggleBankFields() {
        if (['bank','transfer'].includes(methodSelect.value)) {
            bankFields.style.display = 'block';
        } else {
            bankFields.style.display = 'none';
        }
    }

    methodSelect.addEventListener('change', toggleBankFields);
    toggleBankFields(); // عند التحميل
});
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){
    const refundModal=document.getElementById('refundInstallmentModal');
    const refundIdInput=document.getElementById('refund_installment_id');
    const refundInfoInput=document.getElementById('refund_installment_info');
    const refundAmountInput=document.getElementById('refund_amount');

    refundModal.addEventListener('show.bs.modal',function(event){
        const button=event.relatedTarget;
        const installmentId=button.getAttribute('data-installment-id');
        const paid=parseFloat(button.getAttribute('data-paid')||'0');
        const kind=button.getAttribute('data-kind')||'';
        const due=button.getAttribute('data-due')||'';

        refundIdInput.value=installmentId;
        refundInfoInput.value=kind+(due?' | '+due:'')+' | المدفوع: '+paid.toFixed(2)+' د.ل';
        refundAmountInput.value=paid.toFixed(2);
        refundAmountInput.setAttribute('max',paid.toFixed(2));
    });
});
</script>
<script>
// إضافة هذا JavaScript في نهاية الصفحة مع باقي الـ scripts

document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editInstallmentModal');
    const editForm = document.getElementById('editInstallmentForm');
    const editWarning = document.getElementById('edit_warning');
    const editSubmitBtn = document.getElementById('editInstallmentSubmit');

    editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        // استخراج البيانات من الزر
        const installmentId = button.getAttribute('data-installment-id');
        const installmentTypeId = button.getAttribute('data-installment-type-id');
        const semesterNumber = button.getAttribute('data-semester-number');
        const amountDue = button.getAttribute('data-amount-due');
        const dueDate = button.getAttribute('data-due-date');
        const notes = button.getAttribute('data-notes');
        const paidAmount = parseFloat(button.getAttribute('data-paid-amount') || '0');
        
        // تحديث action الخاص بالـ form
        editForm.action = `{{ route('finance.students.installments.update', ['student' => $student->id, 'installment' => 'INSTALLMENT_ID']) }}`.replace('INSTALLMENT_ID', installmentId);
        
        // ملء الحقول
        document.getElementById('edit_installment_type_id').value = installmentTypeId;
        document.getElementById('edit_semester_number').value = semesterNumber;
        document.getElementById('edit_amount_due').value = amountDue;
        document.getElementById('edit_due_date').value = dueDate;
        document.getElementById('edit_notes').value = notes || '';
        
        // إظهار التحذير إذا كان هناك مدفوعات
        if (paidAmount > 0) {
            editWarning.style.display = 'block';
        } else {
            editWarning.style.display = 'none';
        }
    });

    // معالجة إرسال النموذج
    editForm.addEventListener('submit', function(e) {
        editSubmitBtn.disabled = true;
        editSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...';
    });
});
</script>

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // معالجة نافذة حذف القسط
    const deleteModal = document.getElementById('deleteInstallmentModal');
    const deleteForm = document.getElementById('deleteInstallmentForm');
    const deleteSubmitBtn = document.getElementById('deleteSubmitBtn');

    deleteModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        // استخراج البيانات من الزر
        const installmentId = button.getAttribute('data-installment-id');
        const installmentName = button.getAttribute('data-installment-name');
        const semester = button.getAttribute('data-semester');
        const amount = button.getAttribute('data-amount');
        
        // تحديث action الخاص بالـ form
        deleteForm.action = `{{ route('finance.students.installments.destroy', ['student' => $student->id, 'installment' => 'INSTALLMENT_ID']) }}`.replace('INSTALLMENT_ID', installmentId);
        
        // ملء البيانات في النافذة
        document.getElementById('delete_installment_name').textContent = installmentName || '-';
        document.getElementById('delete_semester').textContent = semester ? `الفصل ${semester}` : '-';
        document.getElementById('delete_amount').textContent = amount ? `${amount} د.ل` : '-';
    });

    // معالجة إرسال النموذج
    deleteForm.addEventListener('submit', function(e) {
        deleteSubmitBtn.disabled = true;
        deleteSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جاري الحذف...';
    });
});
</script>

@endpush
