{{-- resources/views/admin/students/create.blade.php --}}
@extends('layouts.app')

@section('title', 'إضافة طالب جديد')

@push('styles')
   @include('partials.page-styles')
   <style>
   .installment-row {
       background: #f8f9fa;
       border: 1px solid #dee2e6 !important;
       position: relative;
       transition: all 0.3s ease;
   }
   .installment-row:hover {
       box-shadow: 0 4px 8px rgba(0,0,0,0.1);
       transform: translateY(-1px);
   }
   .remove-installment {
       border: none;
       background: none;
       color: #dc3545;
       cursor: pointer;
       padding: 5px 10px;
       border-radius: 4px;
       transition: all 0.2s;
   }
   .remove-installment:hover {
       color: #c82333;
       background: rgba(220, 53, 69, 0.1);
   }
   .status-preview {
       font-weight: 600;
   }
   #previousInstallmentsSection {
       border-top: 2px solid #dee2e6;
       margin-top: 15px;
   }
   .form-check-input:checked {
       background-color: #198754;
       border-color: #198754;
   }
   .installment-header {
       background: #e9ecef;
       margin: -15px -15px 15px -15px;
       padding: 10px 15px;
       border-radius: 8px 8px 0 0;
   }
   .add-installment-btn {
       border: 2px dashed #007bff;
       background: transparent;
       color: #007bff;
       padding: 15px;
       border-radius: 8px;
       transition: all 0.3s;
   }
   .add-installment-btn:hover {
       background: rgba(0, 123, 255, 0.1);
       border-color: #0056b3;
   }
   .installment-counter {
       background: #007bff;
       color: white;
       border-radius: 50%;
       width: 25px;
       height: 25px;
       display: inline-flex;
       align-items: center;
       justify-content: center;
       font-size: 0.8rem;
       margin-left: 10px;
   }
   </style>
@endpush

@section('content')
@php
    $prefill = [
        'section_id'   => old('section_id', request('section_id')),
        'stage_id'     => old('stage_id', request('stage_id')),
        'grade_id'     => old('grade_id', request('grade_id')),
        'classroom_id' => old('classroom_id', request('classroom_id')),
    ];
@endphp

<div class="container-fluid px-3 px-md-4">
    <h2 class="page-header">إضافة طالب جديد</h2>

    <form action="{{ route('admin.students.store') }}" method="POST" id="studentForm">
        @csrf
        
        {{-- Personal Information --}}
        <div class="form-card mb-4">
            <div class="form-card-header">
                <i class="fas fa-user me-2"></i>البيانات الشخصية
            </div>
            <div class="form-card-body p-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">اسم الطالب</label>
                        <input type="text" name="name" class="form-control" required
                               value="{{ old('name') }}" placeholder="الاسم الثلاثي">
                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label required">الجنس</label>
                        <select name="gender" class="form-select" required>
                            <option value="">اختر</option>
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>ذكر</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>أنثى</option>
                        </select>
                        @error('gender')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">الرقم الوطني</label>
                        <input type="text" name="national_id" class="form-control"
                               value="{{ old('national_id') }}" placeholder="رقم البطاقة الوطنية">
                        @error('national_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">رقم القيد</label>
                        <input type="text" name="registration_number" class="form-control"
                               value="{{ old('registration_number') }}" placeholder="رقم قيد الطالب">
                        @error('registration_number')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">الجنسية</label>
                        <input type="text" name="nationality" class="form-control"
                               value="{{ old('nationality') }}" placeholder="الجنسية">
                        @error('nationality')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">اسم ولي الأمر</label>
                        <input type="text" name="parent_name" class="form-control" required
                               value="{{ old('parent_name') }}" placeholder="الاسم الثلاثي">
                        @error('parent_name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">رقم هاتف ولي الامر</label>
                        <input type="tel" name="phone" class="form-control" 
                               value="{{ old('phone') }}" placeholder="05XXXXXXXX">
                        @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>


                    <div class="col-md-3">
                        <label class="form-label"> اسم الام كاملاََ </label>
                        <input type="text" name="mother_name" class="form-control" 
                               value="{{ old('mother_name') }}" placeholder="">
                        @error('mother_name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">رقم هاتف الام</label>
                        <input type="tel" name="phone2" class="form-control" 
                               value="{{ old('phone2') }}" placeholder="09XXXXXXXX">
                        @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">العنوان</label>
                        <textarea name="address" class="form-control" rows="2" 
                                  placeholder="عنوان السكن">{{ old('address') }}</textarea>
                        @error('address')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>


                    <div class="col-12">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="أي ملاحظات إضافية عن الطالب">{{ old('notes') }}</textarea>
                        @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Academic Enrollment --}}
        <div class="form-card mb-4">
            <div class="form-card-header">
                <i class="fas fa-graduation-cap me-2"></i>بيانات التسجيل الأكاديمي
            </div>
            <div class="form-card-body p-3">
                @if($currentAcademicYear)
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        سيتم تسجيل الطالب في السنة الدراسية الحالية: <strong>{{ $currentAcademicYear->name }}</strong>
                    </div>
                @else
                    <div class="alert alert-danger mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        تحذير: لا توجد سنة دراسية حالية! يجب إنشاء وتعيين سنة دراسية أولاً.
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label required">القسم</label>
                        <select name="section_id" id="sectionSelect" class="form-select" required>
                            <option value="">اختر القسم</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">
                                    {{ $section->type_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('section_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">المرحلة</label>
                        <select name="stage_id" id="stageSelect" class="form-select" required>
                            <option value="">اختر المرحلة</option>
                        </select>
                        @error('stage_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">الصف</label>
                        <select name="grade_id" id="gradeSelect" class="form-select" required>
                            <option value="">اختر الصف</option>
                        </select>
                        @error('grade_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">الفصل</label>
                        <select name="classroom_id" id="classroomSelect" class="form-select" required>
                            <option value="">اختر الفصل</option>
                        </select>
                        @error('classroom_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">نوع الفوترة</label>
                        <select name="billing_cycle" id="billingCycle" class="form-select" required>
                          <option value="year" {{ old('billing_cycle','year')=='year'?'selected':'' }}>سنة كاملة</option>
                          <option value="semester" {{ old('billing_cycle')=='semester'?'selected':'' }}>فصل</option>
                        </select>
                        @error('billing_cycle')<div class="text-danger small">{{ $message }}</div>@enderror
                      </div>                
                </div>
            </div>
        </div>

        {{-- Previous Installments Section --}}
        <div class="form-card mb-4">
            <div class="form-card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-money-bill-wave me-2"></i>الأقساط السابقة (اختياري)
                    </span>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="hasPreviousInstallments" 
                               name="has_previous_installments" value="1" 
                               {{ old('has_previous_installments') ? 'checked' : '' }}>
                        <label class="form-check-label" for="hasPreviousInstallments">
                            يوجد أقساط سابقة للطالب
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-card-body p-3" id="previousInstallmentsSection" 
                 style="{{ old('has_previous_installments') ? 'display: block;' : 'display: none;' }}">
                
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>ملاحظة:</strong> يمكنك إضافة الأقساط السابقة للطالب من سنوات دراسية أخرى أو رسوم إضافية مثل الكتب والنقل والأنشطة.
                </div>

                <div id="installmentsContainer">
                    @if(old('previous_installments'))
                        @foreach(old('previous_installments') as $index => $installment)
                            <div class="installment-row border rounded p-3 mb-3" data-index="{{ $index }}">
                                <div class="installment-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <span class="installment-counter">{{ $index + 1 }}</span>
                                            القسط رقم {{ $index + 1 }}
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-installment">
                                            <i class="fas fa-trash me-1"></i> حذف
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label required">نوع القسط</label>
                                        <select name="previous_installments[{{ $index }}][installment_type_id]" 
                                                class="form-select" required>
                                            <option value="">اختر نوع القسط</option>
                                            @foreach($installmentTypes as $type)
                                                <option value="{{ $type->id }}" 
                                                    {{ ($installment['installment_type_id'] ?? '') == $type->id ? 'selected' : '' }}>
                                                    {{ $type->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("previous_installments.{$index}.installment_type_id")
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    
                                    <div class="col-md-2">
                                        <label class="form-label required">المبلغ المستحق</label>
                                        <input type="number" name="previous_installments[{{ $index }}][amount_due]" 
                                               class="form-control amount-due" step="0.01" min="0.01" required
                                               value="{{ $installment['amount_due'] ?? '' }}" placeholder="0.00">
                                        @error("previous_installments.{$index}.amount_due")<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label class="form-label">المبلغ المدفوع</label>
                                        <input type="number" name="previous_installments[{{ $index }}][paid_amount]" 
                                               class="form-control paid-amount" step="0.01" min="0" 
                                               value="{{ $installment['paid_amount'] ?? '0' }}" placeholder="0.00">
                                        @error("previous_installments.{$index}.paid_amount")<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label class="form-label">تاريخ الاستحقاق</label>
                                        <input type="date" name="previous_installments[{{ $index }}][due_date]" 
                                               class="form-control due-date" value="{{ $installment['due_date'] ?? '' }}">
                                        @error("previous_installments.{$index}.due_date")<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">رقم المرجع</label>
                                        <input type="text" name="previous_installments[{{ $index }}][reference]" 
                                               class="form-control" placeholder="رقم الفاتورة أو المرجع"
                                               value="{{ $installment['reference'] ?? '' }}">
                                        @error("previous_installments.{$index}.reference")<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>
                                    
                                    <div class="col-12">
                                        <label class="form-label">ملاحظات</label>
                                        <textarea name="previous_installments[{{ $index }}][notes]" 
                                                  class="form-control" rows="2" 
                                                  placeholder="ملاحظات إضافية حول هذا القسط">{{ $installment['notes'] ?? '' }}</textarea>
                                        @error("previous_installments.{$index}.notes")<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="alert alert-light mb-0">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <small class="text-muted">
                                                        <strong>الحالة المتوقعة:</strong> 
                                                        <span class="status-preview">سيتم حسابها تلقائياً</span>
                                                    </small>
                                                </div>
                                                <div class="col-md-6 text-end">
                                                    <small class="text-muted">
                                                        <strong>المبلغ المتبقي:</strong> 
                                                        <span class="remaining-preview">0.00</span> د.ل
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="text-center">
                    <button type="button" class="btn add-installment-btn w-100" id="addInstallmentBtn">
                        <i class="fas fa-plus me-2"></i> إضافة قسط جديد
                    </button>
                </div>

                {{-- Summary --}}
                <div class="mt-4 pt-3 border-top">
                    <div class="row" id="installmentsSummary" style="display: none;">
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-2">
                                <div class="h5 mb-1 text-primary" id="totalInstallments">0</div>
                                <div class="small text-muted">عدد الأقساط</div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-2">
                                <div class="h5 mb-1 text-info" id="totalDue">0.00</div>
                                <div class="small text-muted">إجمالي المستحق</div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-2">
                                <div class="h5 mb-1 text-success" id="totalPaid">0.00</div>
                                <div class="small text-muted">إجمالي المدفوع</div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-2">
                                <div class="h5 mb-1 text-warning" id="totalRemaining">0.00</div>
                                <div class="small text-muted">إجمالي المتبقي</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="form-card-footer p-3 text-end">
            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-times me-1"></i> إلغاء
            </a>
            <button type="submit" class="btn btn-dark" {{ !$currentAcademicYear ? 'disabled' : '' }}>
                <i class="fas fa-save me-1"></i> حفظ الطالب
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sectionSelect = document.getElementById('sectionSelect');
    const stageSelect = document.getElementById('stageSelect');
    const gradeSelect = document.getElementById('gradeSelect');
    const classroomSelect = document.getElementById('classroomSelect');
    
    const sections = @json($sections);
    const prefill = @json($prefill);

    function clearSelect(select, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
    }

    function setSelectValue(select, value) {
        if (!value) return;
        const opt = select.querySelector(`option[value="${value}"]`);
        if (opt) {
            select.value = String(value);
        }
    }

    function populateStages(sectionId) {
        clearSelect(stageSelect, 'اختر المرحلة');
        clearSelect(gradeSelect, 'اختر الصف');
        clearSelect(classroomSelect, 'اختر الفصل');

        if (!sectionId) return;

        const section = sections.find(s => String(s.id) === String(sectionId));
        if (section && Array.isArray(section.stages)) {
            section.stages.forEach(stage => {
                stageSelect.insertAdjacentHTML('beforeend',
                    `<option value="${stage.id}">${stage.name}</option>`);
            });
        }
    }

    function populateGrades(stageId) {
        clearSelect(gradeSelect, 'اختر الصف');
        clearSelect(classroomSelect, 'اختر الفصل');
        if (!stageId) return;

        let stage = null;
        sections.forEach(section => {
            (section.stages || []).forEach(st => {
                if (String(st.id) === String(stageId)) stage = st;
            });
        });

        if (stage && Array.isArray(stage.grades)) {
            stage.grades.forEach(grade => {
                gradeSelect.insertAdjacentHTML('beforeend',
                    `<option value="${grade.id}">${grade.name}</option>`);
            });
        }
    }

    function populateClassrooms(gradeId) {
        clearSelect(classroomSelect, 'اختر الفصل');
        if (!gradeId) return;

        let grade = null;
        sections.forEach(section => {
            (section.stages || []).forEach(stage => {
                (stage.grades || []).forEach(gr => {
                    if (String(gr.id) === String(gradeId)) grade = gr;
                });
            });
        });

        if (grade && Array.isArray(grade.classrooms)) {
            grade.classrooms.forEach(cls => {
                classroomSelect.insertAdjacentHTML('beforeend',
                    `<option value="${cls.id}">${cls.name}</option>`);
            });
        }
    }

    // Events
    sectionSelect.addEventListener('change', function() {
        populateStages(this.value);
    });

    stageSelect.addEventListener('change', function() {
        populateGrades(this.value);
    });

    gradeSelect.addEventListener('change', function() {
        populateClassrooms(this.value);
    });

    // Initialize with prefill (query string or old inputs)
    (function initPrefill() {
        // Section
        setSelectValue(sectionSelect, prefill.section_id);
        populateStages(sectionSelect.value);
        // Stage
        setSelectValue(stageSelect, prefill.stage_id);
        populateGrades(stageSelect.value);
        // Grade
        setSelectValue(gradeSelect, prefill.grade_id);
        populateClassrooms(gradeSelect.value);
        // Classroom
        setSelectValue(classroomSelect, prefill.classroom_id);
    })();

    // Previous Installments Management
    const hasPreviousInstallments = document.getElementById('hasPreviousInstallments');
    const previousInstallmentsSection = document.getElementById('previousInstallmentsSection');
    const installmentsContainer = document.getElementById('installmentsContainer');
    const addInstallmentBtn = document.getElementById('addInstallmentBtn');
    
    let installmentCounter = {{ count(old('previous_installments', [])) }};

    hasPreviousInstallments.addEventListener('change', function() {
        if (this.checked) {
            previousInstallmentsSection.style.display = 'block';
            if (installmentCounter === 0) {
                addInstallment();
            }
        } else {
            previousInstallmentsSection.style.display = 'none';
            installmentsContainer.innerHTML = '';
            installmentCounter = 0;
            updateSummary();
        }
    });

    addInstallmentBtn.addEventListener('click', function() {
        addInstallment();
    });

    function addInstallment() {
        const installmentHtml = `
            <div class="installment-row border rounded p-3 mb-3" data-index="${installmentCounter}">
                <div class="installment-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <span class="installment-counter">${installmentCounter + 1}</span>
                            القسط رقم ${installmentCounter + 1}
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-installment">
                            <i class="fas fa-trash me-1"></i> حذف
                        </button>
                    </div>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label required">نوع القسط</label>
                        <select name="previous_installments[${installmentCounter}][installment_type_id]" class="form-select" required>
                            <option value="">اختر نوع القسط</option>
                            @foreach($installmentTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="col-md-2">
                        <label class="form-label required">المبلغ المستحق</label>
                        <input type="number" name="previous_installments[${installmentCounter}][amount_due]" 
                               class="form-control amount-due" step="0.01" min="0.01" required placeholder="0.00">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">المبلغ المدفوع</label>
                        <input type="number" name="previous_installments[${installmentCounter}][paid_amount]" 
                               class="form-control paid-amount" step="0.01" min="0" value="0" placeholder="0.00">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">تاريخ الاستحقاق</label>
                        <input type="date" name="previous_installments[${installmentCounter}][due_date]" 
                               class="form-control due-date">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">رقم المرجع</label>
                        <input type="text" name="previous_installments[${installmentCounter}][reference]" 
                               class="form-control" placeholder="رقم الفاتورة أو المرجع">
                    </div>
                    <div class="col-12">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="previous_installments[${installmentCounter}][notes]" 
                                  class="form-control" rows="2" placeholder="ملاحظات إضافية حول هذا القسط"></textarea>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-light mb-0">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <strong>الحالة المتوقعة:</strong> 
                                        <span class="status-preview">مستحق</span>
                                    </small>
                                </div>
                                <div class="col-md-6 text-end">
                                    <small class="text-muted">
                                        <strong>المبلغ المتبقي:</strong> 
                                        <span class="remaining-preview">0.00</span> د.ل
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        installmentsContainer.insertAdjacentHTML('beforeend', installmentHtml);
        
        const newInstallment = installmentsContainer.querySelector(`[data-index="${installmentCounter}"]`);
        
        newInstallment.querySelector('.remove-installment').addEventListener('click', function() {
            newInstallment.remove();
            updateInstallmentNumbers();
            updateSummary();
        });
        
        const amountDueInput = newInstallment.querySelector('.amount-due');
        const paidAmountInput = newInstallment.querySelector('.paid-amount');
        const dueDateInput = newInstallment.querySelector('.due-date');
        const statusPreview = newInstallment.querySelector('.status-preview');
        const remainingPreview = newInstallment.querySelector('.remaining-preview');
        
        function updateStatusPreview() {
            const amountDue = parseFloat(amountDueInput.value) || 0;
            const paidAmount = parseFloat(paidAmountInput.value) || 0;
            const dueDate = dueDateInput.value;
            const remaining = Math.max(0, amountDue - paidAmount);
            
            let status = '';
            let statusClass = '';
            
            if (paidAmount >= amountDue && amountDue > 0) {
                status = 'مدفوع';
                statusClass = 'text-success';
            } else if (paidAmount > 0) {
                status = 'مدفوع جزئياً';
                statusClass = 'text-warning';
            } else if (dueDate && new Date(dueDate) < new Date()) {
                status = 'متأخر';
                statusClass = 'text-danger';
            } else {
                status = 'مستحق';
                statusClass = 'text-primary';
            }
            
            statusPreview.textContent = status;
            statusPreview.className = `status-preview ${statusClass}`;
            remainingPreview.textContent = remaining.toFixed(2);
            
            updateSummary();
        }
        
        amountDueInput.addEventListener('input', updateStatusPreview);
        paidAmountInput.addEventListener('input', updateStatusPreview);
        dueDateInput.addEventListener('change', updateStatusPreview);
        
        installmentCounter++;
        updateSummary();
    }

    function updateInstallmentNumbers() {
        const installments = installmentsContainer.querySelectorAll('.installment-row');
        installments.forEach((installment, index) => {
            const title = installment.querySelector('h6');
            if (title) {
                title.innerHTML = `<span class="installment-counter">${index + 1}</span> القسط رقم ${index + 1}`;
            }
        });
    }

    function updateSummary() {
        const installments = installmentsContainer.querySelectorAll('.installment-row');
        const summary = document.getElementById('installmentsSummary');
        
        if (installments.length === 0) {
            summary.style.display = 'none';
            return;
        }
        
        summary.style.display = 'flex';
        
        let totalCount = installments.length;
        let totalDue = 0;
        let totalPaid = 0;
        
        installments.forEach(installment => {
            const amountDue = parseFloat(installment.querySelector('.amount-due').value) || 0;
            const paidAmount = parseFloat(installment.querySelector('.paid-amount').value) || 0;
            
            totalDue += amountDue;
            totalPaid += paidAmount;
        });
        
        const totalRemaining = totalDue - totalPaid;
        
        document.getElementById('totalInstallments').textContent = totalCount;
        document.getElementById('totalDue').textContent = totalDue.toFixed(2);
        document.getElementById('totalPaid').textContent = totalPaid.toFixed(2);
        document.getElementById('totalRemaining').textContent = totalRemaining.toFixed(2);
        
        const remainingElement = document.getElementById('totalRemaining');
        remainingElement.className = 'h5 mb-1 ' + (totalRemaining > 0 ? 'text-warning' : 'text-success');
    }

    if (installmentCounter > 0) {
        const existingInstallments = installmentsContainer.querySelectorAll('.installment-row');
        existingInstallments.forEach((installment) => {
            installment.querySelector('.remove-installment').addEventListener('click', function() {
                installment.remove();
                updateInstallmentNumbers();
                updateSummary();
            });
            
            const amountDueInput = installment.querySelector('.amount-due');
            const paidAmountInput = installment.querySelector('.paid-amount');
            const dueDateInput = installment.querySelector('.due-date');
            const statusPreview = installment.querySelector('.status-preview');
            const remainingPreview = installment.querySelector('.remaining-preview');
            
            function updateStatusPreview() {
                const amountDue = parseFloat(amountDueInput.value) || 0;
                const paidAmount = parseFloat(paidAmountInput.value) || 0;
                const dueDate = dueDateInput.value;
                const remaining = Math.max(0, amountDue - paidAmount);
                
                let status = '';
                let statusClass = '';
                
                if (paidAmount >= amountDue && amountDue > 0) {
                    status = 'مدفوع';
                    statusClass = 'text-success';
                } else if (paidAmount > 0) {
                    status = 'مدفوع جزئياً';
                    statusClass = 'text-warning';
                } else if (dueDate && new Date(dueDate) < new Date()) {
                    status = 'متأخر';
                    statusClass = 'text-danger';
                } else {
                    status = 'مستحق';
                    statusClass = 'text-primary';
                }
                
                statusPreview.textContent = status;
                statusPreview.className = `status-preview ${statusClass}`;
                remainingPreview.textContent = remaining.toFixed(2);
                
                updateSummary();
            }
            
            amountDueInput.addEventListener('input', updateStatusPreview);
            paidAmountInput.addEventListener('input', updateStatusPreview);
            dueDateInput.addEventListener('change', updateStatusPreview);
            updateStatusPreview();
        });
        
        updateSummary();
    }

    document.getElementById('studentForm').addEventListener('submit', function(e) {
        if (hasPreviousInstallments.checked) {
            const installments = installmentsContainer.querySelectorAll('.installment-row');
            let hasValidInstallment = false;
            
            installments.forEach(installment => {
                const amountDue = parseFloat(installment.querySelector('.amount-due').value) || 0;
                if (amountDue > 0) {
                    hasValidInstallment = true;
                }
            });
            
            if (!hasValidInstallment && installments.length > 0) {
                e.preventDefault();
                alert('يرجى إدخال مبلغ صحيح للأقساط أو إلغاء تفعيل الأقساط السابقة');
                return false;
            }
        }
    });

    if (installmentCounter > 0) {
        updateSummary();
    }
});
</script>
@endpush
