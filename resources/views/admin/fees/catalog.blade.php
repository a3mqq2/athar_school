{{-- resources/views/admin/fees/catalog.blade.php --}}
@extends('layouts.app')

@section('title','قائمة التسعيرات')

@push('styles')
    @include('partials.page-styles')
    <style>
        .filters-bar { gap: 10px; flex-wrap: wrap; }
        .filters-bar .form-control, .filters-bar .form-select { min-width: 160px; }
        .fees-grid .number-input { width: 160px; }
        .sticky-actions {
            position: sticky; bottom: 0; background: #fff; padding: 12px 16px;
            border-top: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;
        }
        .muted { color:#777; font-size:12px }
        .chip { background:#f5f5f5; border:1px solid #ddd; padding:2px 8px; border-radius:12px; font-size:12px }
        @media (max-width:768px){ .fees-grid .number-input{ width: 120px; } }
    </style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4">
    <h2 class="page-header">قائمة التسعيرات</h2>

    <div class="form-card mb-3">
        <div class="form-card-header d-flex justify-content-between align-items-center">
            
            <div class="d-flex gap-2">
                <button id="reloadBtnTop" class="btn btn-secondary">تحديث</button>
                <button id="saveBtnTop" class="btn btn-dark">حفظ التغييرات</button>
            </div>
        </div>
        <div class="form-card-body">
            <div class="d-flex filters-bar">
                <div>
                    <label class="form-label mb-1">القسم</label>
                    <select id="filterSection" class="form-select">
                        <option value="all">الكل</option>
                        <option value="local">محلي</option>
                        <option value="international">دولي</option>
                    </select>
                </div>

                <div>
                    <label class="form-label mb-1">المرحلة</label>
                    <select id="filterStage" class="form-select">
                        <option value="">الكل</option>
                        @php
                            $localStages = ($stages['local'] ?? collect());
                            $intlStages  = ($stages['international'] ?? collect());
                        @endphp
                        @if($localStages->count())
                            <optgroup label="محلي">
                                @foreach($localStages as $st)
                                    <option value="{{ $st->id }}" data-section="local">{{ $st->name }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if($intlStages->count())
                            <optgroup label="دولي">
                                @foreach($intlStages as $st)
                                    <option value="{{ $st->id }}" data-section="international">{{ $st->name }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>

                <div class="flex-grow-1">
                    <label class="form-label mb-1">بحث بالصف</label>
                    <input id="filterQ" type="text" class="form-control" placeholder="مثال: أول، ثاني، عاشر …">
                </div>

                <div class="form-check align-self-end mb-2">
                    <input id="filterOnlyMissing" class="form-check-input" type="checkbox">
                    <label class="form-check-label">إظهار الصفوف بدون تسعير</label>
                </div>

                <div class="align-self-end">
                    <button id="applyFiltersBtn" class="btn btn-primary">تطبيق</button>
                </div>
            </div>
        </div>
    </div>

    <div class="form-card">
        <div class="form-card-body p-0">
            <div class="table-responsive fees-grid">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th style="width:120px">القسم</th>
                            <th style="width:220px">المرحلة</th>
                            <th>الصف</th>
                            <th style="width:220px">قيمة الفصل</th>
                            <th style="width:220px">قيمة السنة كاملة</th>
                        </tr>
                    </thead>
                    <tbody id="feesBody">
                        <tr><td colspan="4" class="text-center p-4">جاري التحميل...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="sticky-actions">
                <div class="muted">الحفظ ينشئ/يحدّث التسعيرات — ترك الحقل فارغًا يحذف التعريف</div>
                <div class="d-flex gap-2">
                    <button id="reloadBtn" class="btn btn-secondary">تحديث</button>
                    <button id="saveBtn" class="btn btn-dark">حفظ</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const feesBody = document.getElementById('feesBody');
const csrf = '{{ csrf_token() }}';

// عناصر الفلاتر
const filterSection = document.getElementById('filterSection');
const filterStage   = document.getElementById('filterStage');
const filterQ       = document.getElementById('filterQ');
const filterOnlyMissing = document.getElementById('filterOnlyMissing');
const applyFiltersBtn   = document.getElementById('applyFiltersBtn');

// مزامنة مرحلة مع القسم (اختياري: تضييق حسب القسم)
filterSection.addEventListener('change', ()=>{
    const sec = filterSection.value;
    [...filterStage.options].forEach(opt=>{
        if(opt.value===''){ opt.hidden=false; return; }
        const s = opt.getAttribute('data-section');
        opt.hidden = (sec!='all' && s!=sec);
    });
    if (filterStage.selectedOptions.length && filterStage.selectedOptions[0].hidden) {
        filterStage.value = '';
    }
});

function fmt(n){ return n != null && n != undefined ? n : ''; }

function rowTpl(r){
    return `<tr data-grade="${r.grade_id}" data-stage="${r.stage_id ?? ''}" data-section="${r.section_type}">
        <td>${r.section_type === 'international' ? 'دولي' : 'محلي'}</td>
        <td>${r.stage_name ?? ''}</td>
        <td>${r.grade_name}</td>
        <td>
            <input type="number" step="0.01" class="form-control number-input amount"
                   value="${fmt(r.amount)}" placeholder="">
        </td>
        <td>
            <input type="number" step="0.01" class="form-control number-input year_amount"
                   value="${fmt(r.year_amount)}" placeholder="">
        </td>
    </tr>`;
}

function buildQuery(){
    const p = new URLSearchParams();
    const sec = filterSection.value;
    if(sec && sec!='all') p.set('section', sec);
    const st = filterStage.value;
    if(st) p.set('stage_id', st);
    const q  = filterQ.value.trim();
    if(q) p.set('q', q);
    if(filterOnlyMissing.checked) p.set('only_missing', '1');
    return p.toString();
}

async function loadData(){
    feesBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">جاري التحميل...</td></tr>`;
    const qs = buildQuery();
    const url = '{{ route('admin.fees.catalog.data') }}' + (qs ? ('?'+qs) : '');
    try {
        const res = await fetch(url, {headers:{'Accept':'application/json'}});
        if(!res.ok){ throw new Error('HTTP '+res.status); }
        const js  = await res.json();

        console.log("🚀 Received data:", js); // ✅ أضف هذا السطر

        const rows = Array.isArray(js.rows) ? js.rows : (Array.isArray(js) ? js : []);
        feesBody.innerHTML = rows.length ? rows.map(rowTpl).join('')
            : `<tr><td colspan="4" class="text-center p-4">لا توجد نتائج مطابقة للفلاتر</td></tr>`;
    } catch (e) {
        console.error(e);
        feesBody.innerHTML = `<tr><td colspan="4" class="text-center text-danger p-4">تعذر تحميل البيانات</td></tr>`;
        alert('حدث خطأ أثناء تحميل البيانات. تحقق من السيرفر/اللوج.');
    }
}


async function saveData(){
    const rows = document.querySelectorAll('#feesBody tr');
    if(!rows.length || rows[0].querySelector('input') === null){
        alert('لا توجد بيانات للحفظ.');
        return;
    }
    const items = [];
    rows.forEach(tr=>{
        const am = tr.querySelector('.amount');
        if(!am) return;
        items.push({
            grade_id: parseInt(tr.dataset.grade),
            stage_id: tr.dataset.stage ? parseInt(tr.dataset.stage) : null,
            section_type: tr.dataset.section || 'local',
            amount: am.value != '' ? parseFloat(am.value) : null,
            year_amount: tr.querySelector('.year_amount').value != '' ? parseFloat(tr.querySelector('.year_amount').value) : null,
        });
    });

    try {
        const res = await fetch('{{ route('admin.fees.catalog.upsert') }}', {
            method:'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrf, 'Accept':'application/json'},
            body: JSON.stringify({items})
        });
        if(!res.ok){
            const errTxt = await res.text(); throw new Error(errTxt || ('HTTP '+res.status));
        }
        alert('تم الحفظ بنجاح');
        await loadData();
    } catch(e){
        console.error(e);
        alert('فشل الحفظ. تحقق من القيم أو اللوج.');
    }
}

// أزرار
document.getElementById('reloadBtn').addEventListener('click', loadData);
document.getElementById('saveBtn').addEventListener('click', saveData);
document.getElementById('reloadBtnTop').addEventListener('click', loadData);
document.getElementById('saveBtnTop').addEventListener('click', saveData);
applyFiltersBtn.addEventListener('click', loadData);

// تحميل أولي
document.addEventListener('DOMContentLoaded', loadData);
</script>
@endpush
