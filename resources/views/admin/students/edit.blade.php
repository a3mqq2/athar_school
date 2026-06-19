{{-- resources/views/admin/students/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'تعديل بيانات الطالب')

@push('styles')
   @include('partials.page-styles')
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="page-header">تعديل بيانات الطالب: {{ $student->name }}</h2>
        <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline-secondary">
            <i class="fas fa-eye me-1"></i> عرض التفاصيل
        </a>
    </div>

    <form action="{{ route('admin.students.update', $student) }}" method="POST">
        @csrf
        @method('PUT')
        
        {{-- Personal Information --}}
        <div class="form-card mb-4">
            <div class="form-card-header">البيانات الشخصية</div>
            <div class="form-card-body p-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">اسم الطالب</label>
                        <input type="text" name="name" class="form-control" required
                               value="{{ old('name', $student->name) }}" placeholder="الاسم الثلاثي">
                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label required">الجنس</label>
                        <select name="gender" class="form-select" required>
                            <option value="">اختر</option>
                            <option value="male" {{ old('gender', $student->gender) == 'male' ? 'selected' : '' }}>ذكر</option>
                            <option value="female" {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>أنثى</option>
                        </select>
                        @error('gender')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">الرقم الوطني</label>
                        <input type="text" name="national_id" class="form-control"
                               value="{{ old('national_id', $student->national_id) }}" placeholder="رقم البطاقة الوطنية">
                        @error('national_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">رقم القيد</label>
                        <input type="text" name="registration_number" class="form-control"
                               value="{{ old('registration_number', $student->registration_number) }}" placeholder="رقم قيد الطالب">
                        @error('registration_number')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">الجنسية</label>
                        <input type="text" name="nationality" class="form-control"
                               value="{{ old('nationality', $student->nationality) }}" placeholder="الجنسية">
                        @error('nationality')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">اسم ولي الأمر</label>
                        <input type="text" name="parent_name" class="form-control" required
                               value="{{ old('parent_name', $student->parent_name) }}" placeholder="الاسم الثلاثي">
                        @error('parent_name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="tel" name="phone" class="form-control" 
                               value="{{ old('phone', $student->phone) }}" placeholder="05XXXXXXXX">
                        @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>



                    <div class="col-md-3">
                        <label class="form-label">اسم  الام</label>
                        <input type="tel" name="mother_name" class="form-control" 
                               value="{{ old('mother_name', $student->mother_name) }}" placeholder="09XXXXXXXX">
                        @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
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
                                  placeholder="عنوان السكن">{{ old('address', $student->address) }}</textarea>
                        @error('address')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">حالة الطالب</label>
                        <select name="status" class="form-select" required>
                            <option value="active" {{ old('status', $student->status) == 'active' ? 'selected' : '' }}>نشط</option>
                            <option value="inactive" {{ old('status', $student->status) == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                            <option value="graduated" {{ old('status', $student->status) == 'graduated' ? 'selected' : '' }}>متخرج</option>
                            <option value="transferred" {{ old('status', $student->status) == 'transferred' ? 'selected' : '' }}>منقول</option>
                        </select>
                        @error('status')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="أي ملاحظات إضافية عن الطالب">{{ old('notes', $student->notes) }}</textarea>
                        @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Current Academic Enrollment --}}
        @if($student->currentEnrollment)
        <div class="form-card mb-4">
            <div class="form-card-header">
                بيانات التسجيل الأكاديمي الحالي
                <small class="text-muted">({{ $student->currentEnrollment->academicYear->name }})</small>
            </div>
            <div class="form-card-body p-3">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label required">القسم</label>
                        <select name="section_id" id="sectionSelect" class="form-select" required>
                            <option value="">اختر القسم</option>
                            @foreach($sections as $section)
                                @php
                                    $currentSectionId = old('section_id', $student->currentEnrollment->stage->sectionObj->id ?? '');
                                @endphp
                                <option value="{{ $section->id }}" {{ $currentSectionId == $section->id ? 'selected' : '' }}>
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
                            @php
                                $currentSectionId = old('section_id', $student->currentEnrollment->stage->sectionObj->id ?? '');
                                $currentStageId = old('stage_id', $student->currentEnrollment->stage_id ?? '');
                            @endphp
                            @if($currentSectionId)
                                @php
                                    $selectedSection = $sections->find($currentSectionId);
                                @endphp
                                @if($selectedSection)
                                    @foreach($selectedSection->stages as $stage)
                                        <option value="{{ $stage->id }}" {{ $currentStageId == $stage->id ? 'selected' : '' }}>
                                            {{ $stage->name }}
                                        </option>
                                    @endforeach
                                @endif
                            @endif
                        </select>
                        @error('stage_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">الصف</label>
                        <select name="grade_id" id="gradeSelect" class="form-select" required>
                            <option value="">اختر الصف</option>
                            @php
                                $currentGradeId = old('grade_id', $student->currentEnrollment->grade_id ?? '');
                            @endphp
                            @if($currentStageId)
                                @php
                                    $selectedStage = null;
                                    foreach($sections as $section) {
                                        $selectedStage = $section->stages->find($currentStageId);
                                        if($selectedStage) break;
                                    }
                                @endphp
                                @if($selectedStage)
                                    @foreach($selectedStage->grades as $grade)
                                        <option value="{{ $grade->id }}" {{ $currentGradeId == $grade->id ? 'selected' : '' }}>
                                            {{ $grade->name }}
                                        </option>
                                    @endforeach
                                @endif
                            @endif
                        </select>
                        @error('grade_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">الفصل</label>
                        <select name="classroom_id" id="classroomSelect" class="form-select" required>
                            <option value="">اختر الفصل</option>
                            @php
                                $currentClassroomId = old('classroom_id', $student->currentEnrollment->classroom_id ?? '');
                            @endphp
                            @if($currentGradeId)
                                @php
                                    $selectedGrade = null;
                                    foreach($sections as $section) {
                                        foreach($section->stages as $stage) {
                                            $selectedGrade = $stage->grades->find($currentGradeId);
                                            if($selectedGrade) break 2;
                                        }
                                    }
                                @endphp
                                @if($selectedGrade)
                                    @foreach($selectedGrade->classrooms as $classroom)
                                        <option value="{{ $classroom->id }}" {{ $currentClassroomId == $classroom->id ? 'selected' : '' }}>
                                            {{ $classroom->name }}
                                        </option>
                                    @endforeach
                                @endif
                            @endif
                        </select>
                        @error('classroom_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    تعديل هذه البيانات سيؤثر على التسجيل الأكاديمي الحالي للطالب فقط.
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            لا يوجد تسجيل أكاديمي حالي لهذا الطالب! يجب إنشاء تسجيل أكاديمي أولاً.
        </div>
        @endif

        {{-- Action Buttons --}}
        <div class="form-card-footer p-3 text-end">
            <a href="{{ route('admin.students.show', $student) }}" class="btn btn-secondary me-2">إلغاء</a>
            <button type="submit" class="btn btn-dark">
                <i class="fas fa-save me-1"></i> حفظ التعديلات
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

    // عند تغيير القسم
    sectionSelect.addEventListener('change', function() {
        const sectionId = this.value;
        
        // مسح المراحل والصفوف والفصول
        stageSelect.innerHTML = '<option value="">اختر المرحلة</option>';
        gradeSelect.innerHTML = '<option value="">اختر الصف</option>';
        classroomSelect.innerHTML = '<option value="">اختر الفصل</option>';
        
        if (sectionId) {
            const section = sections.find(s => s.id == sectionId);
            if (section && section.stages) {
                section.stages.forEach(stage => {
                    stageSelect.innerHTML += `<option value="${stage.id}">${stage.name}</option>`;
                });
            }
        }
    });

    // عند تغيير المرحلة
    stageSelect.addEventListener('change', function() {
        const stageId = this.value;
        
        // مسح الصفوف والفصول
        gradeSelect.innerHTML = '<option value="">اختر الصف</option>';
        classroomSelect.innerHTML = '<option value="">اختر الفصل</option>';
        
        if (stageId) {
            let stage = null;
            sections.forEach(section => {
                if (section.stages) {
                    const found = section.stages.find(s => s.id == stageId);
                    if (found) stage = found;
                }
            });
            
            if (stage && stage.grades) {
                stage.grades.forEach(grade => {
                    gradeSelect.innerHTML += `<option value="${grade.id}">${grade.name}</option>`;
                });
            }
        }
    });

    // عند تغيير الصف
    gradeSelect.addEventListener('change', function() {
        const gradeId = this.value;
        
        // مسح الفصول
        classroomSelect.innerHTML = '<option value="">اختر الفصل</option>';
        
        if (gradeId) {
            let grade = null;
            sections.forEach(section => {
                if (section.stages) {
                    section.stages.forEach(stage => {
                        if (stage.grades) {
                            const found = stage.grades.find(g => g.id == gradeId);
                            if (found) grade = found;
                        }
                    });
                }
            });
            
            if (grade && grade.classrooms) {
                grade.classrooms.forEach(classroom => {
                    classroomSelect.innerHTML += `<option value="${classroom.id}">${classroom.name}</option>`;
                });
            }
        }
    });
});
</script>
@endpush