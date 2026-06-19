{{-- resources/views/admin/settings/stages.blade.php --}}
@extends('layouts.app')

@section('title', 'إعدادات المراحل الدراسية')

@push('styles')
<style>
    .section-card { background: white; border: 2px solid #fbc417; border-radius: 8px; padding: 20px; margin-bottom: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .section-header { background: #fbc41720; margin: -20px -20px 20px -20px; padding: 15px 20px; border-bottom: 3px solid #fbc417; border-radius: 8px 8px 0 0; }
    .section-type-badge { background: #925419; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; }
    .stages-wrapper { padding-right: 10px; }
    .stage-item { background: #f8f8f8; border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px; margin-bottom: 15px; margin-right: 20px; position: relative; }
    .stage-header { border-bottom: 2px solid #925419; padding-bottom: 10px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
    .stage-name-input { font-weight: 600; font-size: 18px; width: 70%; border-color: #925419; }
    .stage-toggle { cursor: pointer; background: none; border: none; font-size: 18px; color: #925419; transition: transform .3s; }
    .stage-toggle.expanded { transform: rotate(180deg); }
    .stage-content { display: none; margin-top: 15px; }
    .stage-content.active { display: block; }
    .grades-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin-top: 15px; }
    .grade-card { background: white; border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px; border-right: 4px solid #925419; }
    .grade-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
    .classrooms-container .no-data, .grades-grid .no-data { text-align: center; color: #999; font-style: italic; padding: 14px; background: #f9f9f9; border-radius: 6px; margin: 6px 0; }
    .classroom-item { background: #f9f9f9; padding: 8px 12px; margin: 5px 0; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; border-right: 3px solid #fbc417; }
    .btn { border-radius: 3px; padding: 6px 12px; font-weight: 500; border: none; font-size: 13px; cursor: pointer; }
    .btn-sm { padding: 4px 8px; font-size: 12px; }
    .btn-primary { background: #fbc417; color: #925419; }
    .btn-success { background: #925419; color: #fff; }
    .btn-danger { background: #fff; color: #dc3545; border: 1px solid #dc3545; }
    .btn-info { background: #fbc417; color: #925419; padding: 8px 16px; }
    .page-title { color: #925419; font-weight: 600; margin-bottom: 20px; font-size: 24px; padding-bottom: 10px; border-bottom: 3px solid #fbc417; }
</style>
@endpush

@section('content')
<div class="container">
    <h1 class="page-title">إدارة الأقسام والمراحل الدراسية</h1>

    <form id="stagesForm" method="POST" action="{{ route('admin.stages.update') }}">
        @csrf

        {{-- القسم المحلي --}}
        <div class="section-card">
            <div class="section-header d-flex justify-content-between align-items-center">
                <div>
                    <span class="section-type-badge">محلي</span>
                    <span style="font-weight:600;color:#925419;font-size:18px;margin-right:15px;">القسم المحلي</span>
                </div>
                <button type="button" class="btn btn-info" onclick="addStage('local')">
                    <i class="fas fa-plus"></i> إضافة مرحلة
                </button>
            </div>

            <div id="localStages" class="stages-wrapper">
                @forelse($localStages ?? [] as $stage)
                    <div class="stage-item" data-stage-id="{{ $stage->id }}">
                        <div class="stage-header">
                            <div class="d-flex align-items-center">
                                <input type="text" name="stages[{{ $stage->id }}][name]" value="{{ $stage->name }}" class="form-control stage-name-input" placeholder="اسم المرحلة">
                                <input type="hidden" name="stages[{{ $stage->id }}][section_type]" value="local">
                                <button type="button" class="stage-toggle" onclick="toggleStage({{ $stage->id }})">▼</button>
                            </div>
                            <div>
                                <small style="color:#666;margin-left:15px;">
                                    ({{ $stage->grades->count() }} صف، {{ $stage->grades->sum(fn($g)=>$g->classrooms->count()) }} فصل)
                                </small>
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeStage({{ $stage->id }})"><i class="fas fa-trash"></i> حذف</button>
                            </div>
                        </div>

                        <div class="stage-content" id="stage-{{ $stage->id }}-content">
                            <div class="grades-grid">
                                @forelse($stage->grades as $grade)
                                    <div class="grade-card" data-grade-id="{{ $grade->id }}">
                                        <div class="grade-header">
                                            <input type="text" name="grades[{{ $grade->id }}][name]" value="{{ $grade->name }}" class="form-control" placeholder="اسم الصف" style="width:60%;">
                                            {{-- ✅ ربط الصف بمرحلته للأصناف القديمة --}}
                                            <input type="hidden" name="grades[{{ $grade->id }}][stage_id]" value="{{ $stage->id }}">
                                            <div>
                                                <button type="button" class="btn btn-primary btn-sm" onclick="addClassroom({{ $grade->id }})">+ فصل</button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="removeGrade({{ $grade->id }})">حذف</button>
                                            </div>
                                        </div>

                                        <div class="classrooms-container">
                                            @forelse($grade->classrooms as $classroom)
                                                <div class="classroom-item" data-classroom-id="{{ $classroom->id }}">
                                                    <input type="text" name="classrooms[{{ $classroom->id }}][name]" value="{{ $classroom->name }}" class="form-control" placeholder="اسم الفصل" style="width:60%;">
                                                    {{-- ✅ ربط الفصل بصفه للأصناف القديمة --}}
                                                    <input type="hidden" name="classrooms[{{ $classroom->id }}][grade_id]" value="{{ $grade->id }}">
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeClassroom({{ $classroom->id }})">حذف</button>
                                                </div>
                                            @empty
                                                <div class="no-data">لا توجد فصول</div>
                                            @endforelse
                                        </div>
                                    </div>
                                @empty
                                    <div class="no-data">لا توجد صفوف</div>
                                @endforelse
                            </div>

                            <button type="button" class="btn btn-success" onclick="addGrade({{ $stage->id }})" style="margin-top:15px;">
                                <i class="fas fa-plus"></i> إضافة صف
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="no-data">لا توجد مراحل دراسية في القسم المحلي</div>
                @endforelse
            </div>
        </div>

        {{-- القسم الدولي --}}
        <div class="section-card">
            <div class="section-header d-flex justify-content-between align-items-center">
                <div>
                    <span class="section-type-badge" style="background:#fbc417;color:#925419;">دولي</span>
                    <span style="font-weight:600;color:#925419;font-size:18px;margin-right:15px;">القسم الدولي</span>
                </div>
                <button type="button" class="btn btn-info" onclick="addStage('international')">
                    <i class="fas fa-plus"></i> إضافة مرحلة
                </button>
            </div>

            <div id="internationalStages" class="stages-wrapper">
                @forelse($internationalStages ?? [] as $stage)
                    <div class="stage-item" data-stage-id="{{ $stage->id }}">
                        <div class="stage-header">
                            <div class="d-flex align-items-center">
                                <input type="text" name="stages[{{ $stage->id }}][name]" value="{{ $stage->name }}" class="form-control stage-name-input" placeholder="اسم المرحلة">
                                <input type="hidden" name="stages[{{ $stage->id }}][section_type]" value="international">
                                <button type="button" class="stage-toggle" onclick="toggleStage({{ $stage->id }})">▼</button>
                            </div>
                            <div>
                                <small style="color:#666;margin-left:15px;">
                                    ({{ $stage->grades->count() }} صف، {{ $stage->grades->sum(fn($g)=>$g->classrooms->count()) }} فصل)
                                </small>
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeStage({{ $stage->id }})"><i class="fas fa-trash"></i> حذف</button>
                            </div>
                        </div>

                        <div class="stage-content" id="stage-{{ $stage->id }}-content">
                            <div class="grades-grid">
                                @forelse($stage->grades as $grade)
                                    <div class="grade-card" data-grade-id="{{ $grade->id }}">
                                        <div class="grade-header">
                                            <input type="text" name="grades[{{ $grade->id }}][name]" value="{{ $grade->name }}" class="form-control" placeholder="اسم الصف" style="width:60%;">
                                            {{-- ✅ ربط الصف بمرحلته للأصناف القديمة --}}
                                            <input type="hidden" name="grades[{{ $grade->id }}][stage_id]" value="{{ $stage->id }}">
                                            <div>
                                                <button type="button" class="btn btn-primary btn-sm" onclick="addClassroom({{ $grade->id }})">+ فصل</button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="removeGrade({{ $grade->id }})">حذف</button>
                                            </div>
                                        </div>

                                        <div class="classrooms-container">
                                            @forelse($grade->classrooms as $classroom)
                                                <div class="classroom-item" data-classroom-id="{{ $classroom->id }}">
                                                    <input type="text" name="classrooms[{{ $classroom->id }}][name]" value="{{ $classroom->name }}" class="form-control" placeholder="اسم الفصل" style="width:60%;">
                                                    {{-- ✅ ربط الفصل بصفه للأصناف القديمة --}}
                                                    <input type="hidden" name="classrooms[{{ $classroom->id }}][grade_id]" value="{{ $grade->id }}">
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeClassroom({{ $classroom->id }})">حذف</button>
                                                </div>
                                            @empty
                                                <div class="no-data">لا توجد فصول</div>
                                            @endforelse
                                        </div>
                                    </div>
                                @empty
                                    <div class="no-data">لا توجد صفوف</div>
                                @endforelse
                            </div>

                            <button type="button" class="btn btn-success" onclick="addGrade({{ $stage->id }})" style="margin-top:15px;">
                                <i class="fas fa-plus"></i> إضافة صف
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="no-data">لا توجد مراحل دراسية في القسم الدولي</div>
                @endforelse
            </div>
        </div>

        <div class="text-center" style="margin-top:30px;">
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> حفظ جميع التغييرات</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let stageCounter = 1000;
    let gradeCounter = 1000;
    let classroomCounter = 1000;

    function toggleStage(stageId){
        const content = document.getElementById(`stage-${stageId}-content`);
        const toggle  = content.previousElementSibling.querySelector('.stage-toggle');
        content.classList.toggle('active');
        toggle.classList.toggle('expanded');
    }

    function addStage(sectionType){
        const container = document.getElementById(`${sectionType}Stages`);
        const stageId = stageCounter++;
        const tpl = `
        <div class="stage-item" data-stage-id="${stageId}">
            <div class="stage-header">
                <div class="d-flex align-items-center">
                    <input type="text" name="stages[${stageId}][name]" value="" class="form-control stage-name-input" placeholder="اسم المرحلة الجديدة">
                    <input type="hidden" name="stages[${stageId}][section_type]" value="${sectionType}">
                    <button type="button" class="stage-toggle" onclick="toggleStage(${stageId})">▼</button>
                </div>
                <div>
                    <small style="color:#666;margin-left:15px;">(0 صف، 0 فصل)</small>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeStage(${stageId})"><i class="fas fa-trash"></i> حذف</button>
                </div>
            </div>
            <div class="stage-content" id="stage-${stageId}-content">
                <div class="grades-grid"><div class="no-data">لا توجد صفوف</div></div>
                <button type="button" class="btn btn-success" onclick="addGrade(${stageId})" style="margin-top:15px;"><i class="fas fa-plus"></i> إضافة صف</button>
            </div>
        </div>`;
        container.insertAdjacentHTML('beforeend', tpl);
        const noData = container.querySelector('.no-data');
        if(noData && noData.textContent.includes('لا توجد مراحل دراسية')) noData.remove();
    }

    function removeStage(stageId){
        if(!confirm('هل أنت متأكد من حذف هذه المرحلة وجميع صفوفها وفصولها؟')) return;
        const el = document.querySelector(`[data-stage-id="${stageId}"]`);
        if(el){
            const container = el.closest('.stages-wrapper');
            el.remove();
            if(container && container.children.length === 0){
                const sectionType = container.id.includes('local') ? 'المحلي' : 'الدولي';
                container.innerHTML = `<div class="no-data">لا توجد مراحل دراسية في القسم ${sectionType}</div>`;
            }
        }
    }

    function addGrade(stageId){
        const gradeId = gradeCounter++;
        const grid = document.querySelector(`#stage-${stageId}-content .grades-grid`);
        const tpl = `
        <div class="grade-card" data-grade-id="${gradeId}">
            <div class="grade-header">
                <input type="text" name="grades[${gradeId}][name]" value="" class="form-control" placeholder="اسم الصف الجديد" style="width:60%;">
                <input type="hidden" name="grades[${gradeId}][stage_id]" value="${stageId}">
                <div>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addClassroom(${gradeId})">+ فصل</button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeGrade(${gradeId})">حذف</button>
                </div>
            </div>
            <div class="classrooms-container"><div class="no-data">لا توجد فصول</div></div>
        </div>`;
        const noData = grid.querySelector('.no-data');
        if(noData && noData.textContent.includes('لا توجد صفوف')) noData.remove();
        grid.insertAdjacentHTML('beforeend', tpl);
    }

    function removeGrade(gradeId){
        if(!confirm('هل أنت متأكد من حذف هذا الصف وجميع فصوله؟')) return;
        const el = document.querySelector(`[data-grade-id="${gradeId}"]`);
        if(el){
            const grid = el.closest('.grades-grid');
            el.remove();
            if(grid && grid.children.length === 0){
                grid.innerHTML = '<div class="no-data">لا توجد صفوف</div>';
            }
        }
    }

    function addClassroom(gradeId){
        const classroomId = classroomCounter++;
        const container = document.querySelector(`[data-grade-id="${gradeId}"] .classrooms-container`);
        const tpl = `
        <div class="classroom-item" data-classroom-id="${classroomId}">
            <input type="text" name="classrooms[${classroomId}][name]" value="" class="form-control" placeholder="اسم الفصل الجديد" style="width:60%;">
            <input type="hidden" name="classrooms[${classroomId}][grade_id]" value="${gradeId}">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeClassroom(${classroomId})">حذف</button>
        </div>`;
        const noData = container.querySelector('.no-data');
        if(noData && noData.textContent.includes('لا توجد فصول')) noData.remove();
        container.insertAdjacentHTML('beforeend', tpl);
    }

    function removeClassroom(classroomId){
        if(!confirm('هل أنت متأكد من حذف هذا الفصل؟')) return;
        const el = document.querySelector(`[data-classroom-id="${classroomId}"]`);
        if(el){
            const container = el.closest('.classrooms-container');
            el.remove();
            if(container && container.children.length === 0){
                container.innerHTML = '<div class="no-data">لا توجد فصول</div>';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.stage-content').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.stage-toggle').forEach(t => t.classList.remove('expanded'));
    });
</script>
@endpush
