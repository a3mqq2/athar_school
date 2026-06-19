@extends('layouts.app')

@section('title','اقساط الطلاب')

@push('styles')
<style>
    :root {
        --primary: #925419;
        --success: #198754;
        --success-light: #d1eddd;
        --danger: #dc3545;
        --danger-light: #f8d7da;
        --warning: #fd7e14;
        --warning-light: #fff3cd;
        --muted: #6c757d;
        --light: #f8f9fa;
        --border: #e1e5eb;
        --success-bg: rgba(25, 135, 84, 0.1);
        --danger-bg: rgba(220, 53, 69, 0.1);
        --warning-bg: rgba(253, 126, 20, 0.1);
    }

    .page-head {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .page-head h3 {
        margin: 0;
        color: var(--primary);
        font-weight: 800;
    }

    .filters-wrap {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 12px 12px 4px;
        box-shadow: 0 2px 6px rgba(0,0,0,.04);
        margin-bottom: 16px;
    }

    .filters {
        display: grid;
        grid-template-columns: repeat(12,1fr);
        gap: 8px;
    }

    .filters .grid-span-3 { grid-column: span 12; }
    .filters .grid-span-2 { grid-column: span 12; }
    .filters .grid-span-1 { grid-column: span 6; }
    .filters .actions { 
        grid-column: span 12;
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    @media(min-width:768px) {
        .filters .grid-span-3 { grid-column: span 3; }
        .filters .grid-span-2 { grid-column: span 2; }
        .filters .grid-span-1 { grid-column: span 1; }
        .filters .actions { grid-column: span 12; }
    }

    .table-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
    }

    .table thead th {
        position: sticky;
        top: 0;
        background: var(--light) !important;
        z-index: 2;
        border-bottom: 2px solid var(--border);
    }

    .badge-dues {
        background: var(--success-bg);
        color: var(--success);
        font-weight: 700;
        border: 1px solid rgba(25, 135, 84, 0.2);
    }

    .badge-overdue {
        background: var(--danger-bg);
        color: var(--danger);
        font-weight: 700;
        border: 1px solid rgba(220, 53, 69, 0.2);
        animation: pulse-danger 2s ease-in-out infinite alternate;
    }

    .badge-warning {
        background: var(--warning-bg);
        color: var(--warning);
        font-weight: 700;
        border: 1px solid rgba(253, 126, 20, 0.2);
    }

    .status-active {
        background: var(--success-bg);
        color: var(--success);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-graduated {
        background: var(--primary);
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-transferred {
        background: var(--warning-bg);
        color: var(--warning);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .row-overdue {
        background: rgba(220, 53, 69, 0.02);
        border-left: 4px solid var(--danger);
    }

    .row-paid {
        background: rgba(25, 135, 84, 0.02);
    }

    .btn-success {
        background: var(--success);
        border-color: var(--success);
        color: white;
    }

    .btn-success:hover {
        background: #157347;
        border-color: #146c43;
    }

    .btn-danger {
        background: var(--danger);
        border-color: var(--danger);
        color: white;
    }

    .btn-danger:hover {
        background: #bb2d3b;
        border-color: #b02a37;
    }

    .filter-active-badge {
        background: var(--warning-bg);
        color: var(--warning);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        border: 1px solid rgba(253, 126, 20, 0.2);
    }

    @keyframes pulse-danger {
        0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
        100% { box-shadow: 0 0 0 4px rgba(220, 53, 69, 0); }
    }

    .cell-right { text-align: end; }
    .index-col { width: 64px; }
    .name-col { min-width: 320px; }
    .phone-col { min-width: 180px; }
    .actions-col { min-width: 130px; }

    .empty {
        padding: 48px;
        text-align: center;
        color: var(--muted);
        background: var(--light);
    }

    .sticky-footer {
        background: linear-gradient(135deg, var(--light) 0%, #ffffff 100%);
        border-top: 2px solid var(--success);
        font-weight: 600;
    }

    .badge-dues-large {
        background: var(--success-bg);
        color: var(--success);
        font-weight: 700;
        border: 1px solid rgba(25, 135, 84, 0.2);
        font-size: 1rem;
        padding: 8px 14px;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="page-head">
        <h3>اقساط الطلاب</h3>
        <div class="d-flex gap-2">
            @if(request()->hasAny(['q','order','order_by','status','min_due','max_due','has_overdue','per_page','due_filter','section_id','stage_id','grade_id','classroom_id','gender']))
                <span class="filter-active-badge">تصفية مفعّلة</span>
                <a href="{{ route('finance.students.index') }}" class="btn btn-light">إلغاء التصفية</a>
            @endif
            
            <!-- زر الطباعة -->
            <a href="{{ route('finance.students.print', request()->all()) }}" 
               class="btn btn-info" 
               target="_blank">
                <i class="fa fa-print"></i> طباعة الكشف
            </a>
        </div>
    </div>

    <form class="filters-wrap" id="filterForm" method="get" action="{{ route('finance.students.index') }}">
        <div class="filters">
            <div class="grid-span-3">
                <div class="input-group">
                    <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="بحث بالاسم / ولي الأمر / الهاتف" id="searchInput">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">مسح</button>
                </div>
            </div>

            <div class="grid-span-2">
                <select name="section_id" class="form-select" id="sectionFilter">
                    <option value="">جميع الأقسام</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" @selected(request('section_id')==$section->id)>{{ $section->type_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid-span-2">
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

            <div class="grid-span-2">
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

            <div class="grid-span-2">
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

            <div class="grid-span-2">
                <select name="gender" class="form-select">
                    <option value="">كل الجنس</option>
                    <option value="male" @selected($gender==='male')>ذكر</option>
                    <option value="female" @selected($gender==='female')>أنثى</option>
                </select>
            </div>

            <div class="grid-span-2">
                <select name="due_filter" class="form-select" id="dueFilterSelect">
                    <option value="">كل الطلاب</option>
                    <option value="zero" @selected($dueFilter==='zero')>مدفوعين كاملا</option>
                    <option value="has_dues" @selected($dueFilter==='has_dues')>عليهم أقساط</option>
                    <option value="only_overdue" @selected($dueFilter==='only_overdue')>متأخرات فقط</option>
                    <option value="non_overdue" @selected($dueFilter==='non_overdue')>أقساط بدون متأخرات</option>
                </select>
            </div>

            <div class="grid-span-2">
                <select name="order" class="form-select" id="orderSelect">
                    <option value="desc" @selected($order==='desc')>تنازلي</option>
                    <option value="asc" @selected($order==='asc')>تصاعدي</option>
                </select>
            </div>

            <div class="grid-span-2">
                <select name="per_page" class="form-select" id="perPageSelect">
                    @foreach([10,20,50,100] as $pp)
                        <option value="{{ $pp }}" @selected((int)$perPage===$pp)>عرض {{ $pp }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid-span-2">
                <input type="number" name="min_due" value="{{ $minDue }}" class="form-control" placeholder="أدنى مستحق">
            </div>

            <div class="grid-span-2">
                <input type="number" name="max_due" value="{{ $maxDue }}" class="form-control" placeholder="أقصى مستحق">
            </div>

            <div class="grid-span-2 form-check align-self-center" style="padding-inline-start: .75rem;">
                <input class="form-check-input" type="checkbox" value="1" id="hasOverdue" name="has_overdue" @checked($hasOverdue)>
                <label class="form-check-label" for="hasOverdue">لديه متأخرات</label>
            </div>

            <div class="actions">
                <button class="btn btn-success" id="applyBtn" type="submit">تطبيق</button>
            </div>
        </div>
    </form>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="index-col">#</th>
                        <th class="name-col">الطالب</th>
                        <th class="phone-col">الهاتف</th>
                        <th>القسم</th>
                        <th>المرحلة</th>
                        <th>الصف</th>
                        <th>الفصل</th>
                        <th class="cell-right">إجمالي المستحق</th>
                        <th class="actions-col cell-right">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                @php $startIndex = ($students->currentPage()-1)*$students->perPage(); @endphp
                @forelse($students as $idx => $s)
                    <tr class="{{ ($s->overdue_sum ?? 0) > 0 ? 'row-overdue' : 'row-paid' }}">
                        <td class="text-muted">{{ $startIndex + $idx + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $s->name }}</div>
                            <div class="small text-muted">
                                @if($s->parent_name) ولي الأمر: {{ $s->parent_name }} • @endif 
                                الحالة: 
                                <span class="status-{{ $s->status }}">
                                    @switch($s->status)
                                        @case('active') نشط @break
                                        @case('graduated') متخرج @break
                                        @case('transferred') منقول @break
                                        @default {{ $s->status }}
                                    @endswitch
                                </span>
                            </div>
                            @if(($s->overdue_sum ?? 0) > 0)
                                <div class="mt-1">
                                    <span class="badge badge-overdue">متأخرات: {{ number_format($s->overdue_sum,2) }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="text-muted">{{ $s->phone }}</td>
                        <td>{{ $s->currentEnrollment->stage->sectionObj->type_name ?? '—' }}</td>
                        <td>{{ $s->currentEnrollment->stage->name ?? '—' }}</td>
                        <td>{{ $s->currentEnrollment->grade->name ?? '—' }}</td>
                        <td>{{ $s->currentEnrollment->classroom->name ?? '—' }}</td>
                        <td class="cell-right">
                            @if($s->due_sum > 0)
                            <span class="badge badge-dues-large">{{ number_format($s->due_sum,2) }}</span>
                            @else
                                <span class="badge" style="background: var(--success-bg); color: var(--success);">مدفوع</span>
                            @endif
                        </td>
                        <td class="cell-right">
                            @if(($s->overdue_sum ?? 0) > 0)
                                <a href="{{ route('finance.students.show',$s) }}" class="btn btn-sm btn-danger">عرض</a>
                            @else
                                <a href="{{ route('finance.students.show',$s) }}" class="btn btn-sm btn-success">عرض</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty">
                            <i class="fas fa-inbox fa-2x mb-3 text-muted"></i>
                            <div>لا توجد بيانات</div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
                @if($students->count())
                <tfoot class="sticky-footer">
                    <tr>
                        <th colspan="7" class="text-end">المجموع في هذه الصفحة</th>
                        <th class="cell-right">
                            @php $pageTotal = $students->sum('due_sum'); @endphp
                            <span class="badge badge-dues fs-6">{{ number_format($pageTotal,2) }}</span>
                        </th>
                        <th></th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="mt-3 d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            عرض {{ $students->firstItem() }}–{{ $students->lastItem() }} من {{ $students->total() }}
        </div>
        {{ $students->appends(request()->except('page'))->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const form = document.getElementById('filterForm');
    const input = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');
    const selects = ['orderSelect','dueFilterSelect','perPageSelect'].map(id=>document.getElementById(id));
    const apply = document.getElementById('applyBtn');

    // Cascading dropdowns
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

    let t;
    input.addEventListener('input', function(){
        clearTimeout(t);
        t = setTimeout(()=> form.requestSubmit(), 400);
    });

    clearBtn.addEventListener('click', function(){
        input.value='';
        document.querySelector('[name="min_due"]').value='';
        document.querySelector('[name="max_due"]').value='';
        form.requestSubmit();
    });

    selects.forEach(s=> s && s.addEventListener('change', ()=> form.requestSubmit()));

    apply.addEventListener('click', function(e){
        e.preventDefault();
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جاري التطبيق...';
        form.requestSubmit();
    });
})();
</script>
@endpush