{{-- resources/views/admin/students/index.blade.php --}}
@extends('layouts.app')

@section('title', 'إدارة الطلاب')

@push('styles')
@include('partials.page-styles')
<style>
    .filter-card{background:#fff;border:2px solid #fbc417;border-radius:12px;margin-bottom:20px}
    .filter-card .card-header{background:#fbc41720;border-bottom:2px solid #fbc417;border-radius:12px 12px 0 0;color:#925419;font-weight:800}
    .badge-code{font-family:ui-monospace,Menlo,Consolas,monospace;background:#fff7d6;color:#7a4a14;border:1px solid #f3d26b;padding:.15rem .45rem;border-radius:.35rem}
    .status-badge{padding:.25rem .6rem;border-radius:999px;font-size:.75rem;font-weight:800;white-space:nowrap}
    .status-active{background:#d4edda;color:#155724;border:1px solid #b7dfb9}
    .status-inactive{background:#f8d7da;color:#721c24;border:1px solid #f2b4b9}
    .status-graduated{background:#d1ecf1;color:#0c5460;border:1px solid #b3e1e7}
    .status-transferred{background:#fff3cd;color:#856404;border:1px solid #ffe08a}
    .table-card .table thead th{vertical-align:middle;white-space:nowrap}
    .table-card .table tbody td{vertical-align:middle}
    
    /* Print styles */
    @media print {
        .no-print { display: none !important; }
        .print-title { text-align: center; margin-bottom: 20px; }
        .table { font-size: 12px; }
        .table th, .table td { padding: 4px !important; }
        body { background: white !important; }
        .table-card { border: none !important; box-shadow: none !important; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h2 class="page-header mb-0">إدارة الطلاب</h2>
        <div class="d-flex gap-2">
            <!-- Export Buttons -->
            <div class="btn-group">
                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa fa-download"></i> تصدير
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.students.export.excel', request()->all()) }}">
                            <i class="fa fa-file-excel"></i> تصدير إكسل (جميع البيانات)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.students.export.contacts', request()->all()) }}">
                            <i class="fa fa-address-book"></i> تصدير جهات الاتصال فقط
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Print Button -->
            <a href="{{route('admin.students.print', request()->all())}}" class="btn btn-info">
                <i class="fa fa-print"></i> طباعة
            </a>
            
            <a href="{{ route('admin.students.create', request()->all()) }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> إضافة طالب جديد
            </a>
        </div>
    </div>

    <!-- Print Title (only visible when printing) -->
    <div class="print-title" style="display: none;">
        <h2>قائمة الطلاب</h2>
        <p>تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}</p>
        @if(request()->hasAny(['search','section_id','stage_id','grade_id','classroom_id']))
            <p><strong>مفلتر حسب:</strong>
                @if(request('search')) البحث: "{{ request('search') }}" @endif
                @if(request('section_id')) | القسم: {{ $sections->find(request('section_id'))->type_name ?? '' }} @endif
                @if(request('stage_id')) | المرحلة @endif
                @if(request('grade_id')) | الصف @endif
                @if(request('classroom_id')) | الفصل @endif
            </p>
        @endif
    </div>

    <div class="filter-card no-print">
        <div class="card-header px-3 py-2">
            <i class="fas fa-filter me-1"></i> تصفية النتائج
        </div>
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.students.index') }}" id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">البحث</label>
                    <input type="text" name="search" class="form-control" placeholder="اسم الطالب، ولي الأمر، أو الهاتف" value="{{ request('search') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">القسم</label>
                    <select name="section_id" class="form-select" id="sectionFilter">
                        <option value="">جميع الأقسام</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" @selected(request('section_id')==$section->id)>{{ $section->type_name }}</option>
                        @endforeach
                    </select>
                </div>


                <div class="col-md-2">
                    <label class="form-label">الجنس</label>
                    <select name="gender" class="form-select" id="sectionFilter">
                        <option value=""> الكل </option>
                        <option value="male" {{request('gender') == "male" ? "selected"  : ""}}> ذكر </option>
                        <option value="female" {{request('gender') == "female" ? "selected"  : ""}}> انثى </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="">جميع الحالات</option>
                        <option value="active" @selected(request('status') == 'active')>نشط</option>
                        <option value="inactive" @selected(request('status') == 'inactive')>غير نشط</option>
                        <option value="graduated" @selected(request('status') == 'graduated')>متخرج</option>
                        <option value="transferred" @selected(request('status') == 'transferred')>منتقل</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">المرحلة</label>
                    <select name="stage_id" class="form-select" id="stageFilter">
                        <option value="">جميع المراحل</option>
                        @if(request('section_id'))
                            @php $selectedSection = $sections->find(request('section_id')); @endphp
                            @if($selectedSection)
                                @foreach($selectedSection->stages as $stage)
                                    <option value="{{ $stage->id }}" @selected(request('stage_id')==$stage->id)>{{ $stage->name }}</option>
                                @endforeach
                            @endif
                        @endif
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">الصف</label>
                    <select name="grade_id" class="form-select" id="gradeFilter">
                        <option value="">جميع الصفوف</option>
                        @if(request('stage_id'))
                            @php
                                $selectedStage = null;
                                foreach($sections as $sec){ $selectedStage = $sec->stages->find(request('stage_id')); if($selectedStage) break; }
                            @endphp
                            @if($selectedStage)
                                @foreach($selectedStage->grades as $grade)
                                    <option value="{{ $grade->id }}" @selected(request('grade_id')==$grade->id)>{{ $grade->name }}</option>
                                @endforeach
                            @endif
                        @endif
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">الفصل</label>
                    <select name="classroom_id" class="form-select" id="classroomFilter">
                        <option value="">جميع الفصول</option>
                        @if(request('grade_id'))
                            @php
                                $selectedGrade = null;
                                foreach($sections as $sec){
                                    foreach($sec->stages as $stg){
                                        $selectedGrade = $stg->grades->find(request('grade_id'));
                                        if($selectedGrade) break 2;
                                    }
                                }
                            @endphp
                            @if($selectedGrade)
                                @foreach($selectedGrade->classrooms as $classroom)
                                    <option value="{{ $classroom->id }}" @selected(request('classroom_id')==$classroom->id)>{{ $classroom->name }}</option>
                                @endforeach
                            @endif
                        @endif
                    </select>
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">تصفية</button>
                </div>

                <div class="col-12">
                    <a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-outline-secondary">مسح الفلاتر</a>
                </div>
            </form>
        </div>
    </div>

    <div class="mb-2 text-muted no-print">
        عرض {{ $students->count() }} من أصل {{ $students->total() }} طالب
        @if(request()->hasAny(['search','section_id','stage_id','grade_id','classroom_id'])) (مفلتر) @endif
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:70px">الكود</th>
                        <th style="width:100px">الرقم الوطني</th>
                        <th style="width:100px">رقم القيد</th>
                        <th>الاسم</th>
                        <th>ولي الأمر</th>
                        <th>الهاتف</th>
                        <th>اسم الام</th>
                        <th>رقم الهاتف</th>
                        <th style="width:80px">الجنس</th>
                        <th style="width:110px">الحالة</th>
                        <th>القسم</th>
                        <th>المرحلة</th>
                        <th>الصف</th>
                        <th>الفصل</th>
                        <th class="text-center no-print" style="width:150px">التحكم</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr id="row-{{ $student->id }}">
                            <td>
                                @if(!empty($student->code))
                                    <span class="badge-code">{{ $student->code }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if(!empty($student->national_id))
                                    <span class="badge-code">{{ $student->national_id }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if(!empty($student->registration_number))
                                    <span class="badge-code">{{ $student->registration_number }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="fw-semibold">
                                <a href="{{ route('admin.students.show', $student) }}">{{ $student->name }}</a>
                            </td>
                            <td>{{ $student->parent_name ?? '—' }}</td>
                            <td>{{ $student->phone ?? '—' }}</td>
                            <td>{{ $student->mother_name ?? '—' }}</td>
                            <td>{{ $student->phone2 ?? '—' }}</td>
                            <td>
                                @if($student->gender === 'male') ذكر
                                @elseif($student->gender === 'female') أنثى
                                @else — @endif
                            </td>
                            <td>
                                @php $st = $student->status; @endphp
                                <span class="status-badge status-{{ $st }}">
                                    @switch($st)
                                        @case('active') نشط @break
                                        @case('inactive') غير نشط @break
                                        @case('graduated') متخرج @break
                                        @case('transferred') منتقل @break
                                        @default غير معروف
                                    @endswitch
                                </span>
                            </td>
                            <td>{{ $student->currentEnrollment->stage->sectionObj->type_name ?? '—' }}</td>
                            <td>{{ $student->currentEnrollment->stage->name ?? '—' }}</td>
                            <td>{{ $student->currentEnrollment->grade->name ?? '—' }}</td>
                            <td>{{ $student->currentEnrollment->classroom->name ?? '—' }}</td>
                            <td class="text-center no-print">
                                <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-info">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-secondary">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="d-inline" onsubmit="return confirm('تأكيد حذف الطالب؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="15" class="text-center">لا يوجد طلاب</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 no-print">
            {{ $students->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sectionFilter   = document.getElementById('sectionFilter');
    const stageFilter     = document.getElementById('stageFilter');
    const gradeFilter     = document.getElementById('gradeFilter');
    const classroomFilter = document.getElementById('classroomFilter');
    const sections        = @json($sections);

    const reset = (el, ph) => el && (el.innerHTML = `<option value="">${ph}</option>`);

    sectionFilter?.addEventListener('change', function(){
        reset(stageFilter,'جميع المراحل');
        reset(gradeFilter,'جميع الصفوف');
        reset(classroomFilter,'جميع الفصول');

        const sId = this.value;
        if(!sId) return;
        const s = sections.find(x => String(x.id)===String(sId));
        s?.stages?.forEach(st => stageFilter.insertAdjacentHTML('beforeend', `<option value="${st.id}">${st.name}</option>`));
    });

    stageFilter?.addEventListener('change', function(){
        reset(gradeFilter,'جميع الصفوف');
        reset(classroomFilter,'جميع الفصول');

        const stId = this.value;
        if(!stId) return;
        let stage=null;
        sections.forEach(sec=>{ const f=sec.stages?.find(st=>String(st.id)===String(stId)); if(f) stage=f; });
        stage?.grades?.forEach(gr => gradeFilter.insertAdjacentHTML('beforeend', `<option value="${gr.id}">${gr.name}</option>`));
    });

    gradeFilter?.addEventListener('change', function(){
        reset(classroomFilter,'جميع الفصول');

        const gId = this.value;
        if(!gId) return;
        let grade=null;
        sections.forEach(sec=>{
            sec.stages?.forEach(st=>{
                const f=st.grades?.find(gr=>String(gr.id)===String(gId));
                if(f) grade=f;
            });
        });
        grade?.classrooms?.forEach(cr => classroomFilter.insertAdjacentHTML('beforeend', `<option value="${cr.id}">${cr.name}</option>`));
    });

    // Show print title when printing
    window.addEventListener('beforeprint', function() {
        document.querySelector('.print-title').style.display = 'block';
    });
    
    window.addEventListener('afterprint', function() {
        document.querySelector('.print-title').style.display = 'none';
    });
});
</script>
@endpush