{{-- resources/views/finance/dashboard.blade.php --}}
@extends('layouts.app')

@section('title','لوحة المالية')

@push('styles')
<style>
    :root{--primary:#925419;--primary-2:#7a4515;--muted:#6c757d;--light:#f8f9fa;--border:#e1e5eb;--success:#28a745;--danger:#dc3545}
    .container, .container-fluid{overflow:visible}
    .wrap{display:grid;gap:16px}
    .banner{position:relative;border-radius:16px;overflow:hidden;background:linear-gradient(135deg,#f6e7d7 0%,#f3efe7 100%);border:1px solid var(--border)}
    .banner-inner{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:24px;flex-wrap:wrap}
    .banner h2{margin:0;color:#533114;font-weight:800}
    .banner .sub{color:var(--muted)}
    .chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
    .chip{background:#fff;border:1px solid var(--border);padding:6px 10px;border-radius:999px;font-size:.875rem}

    .auto-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;align-items:stretch}

    .grid-6{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px;align-items:start}
    .grid-6{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;align-items:start}
    .span-6{grid-column:span 6}
    .span-5{grid-column:span 5}
    .span-4{grid-column:span 4}
    .span-3{grid-column:span 3}
    .span-2{grid-column:span 2}
    .span-1{grid-column:span 1}
    @media (max-width:1199.98px){ .grid-6{grid-template-columns:repeat(3,1fr)} .span-4,.span-5,.span-6{grid-column:span 3} }
    @media (max-width:768px){ .grid-6{grid-template-columns:repeat(2,1fr)} .span-3{grid-column:span 2} }
    @media (max-width:576px){ .grid-6{grid-template-columns:1fr} .span-2,.span-3{grid-column:span 1} }

    .kpi{background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px;display:flex;justify-content:space-between;align-items:center;min-height:96px}
    .kpi h6{margin:0;color:var(--muted);font-weight:700}
    .kpi .val{font-size:1.35rem;font-weight:800}
    .kpi .val.primary{color:var(--primary)}
    .kpi .val.success{color:var(--success)}
    .kpi .val.danger{color:var(--danger)}
    .pill{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;border:1px solid var(--border);background:#fff;white-space:nowrap}

    .cardx{background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden;display:flex;flex-direction:column;min-height:280px}
    .cardx-h{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:var(--light);border-bottom:1px solid var(--border)}
    .cardx-h h5{margin:0;color:#533114;font-weight:800}
    .cardx-b{padding:12px;display:flex;flex-direction:column;height:100%}
    .cardx-b canvas{width:100% !important;max-width:100%;aspect-ratio: 16/9}
    .badge-soft{background:rgba(146,84,25,.08);color:var(--primary);font-weight:700}

    .table-wrap{overflow:auto;border-radius:12px;border:1px solid var(--border)}
    .table{margin-bottom:0}
    .table thead th{position:sticky;top:0;background:var(--light)!important;z-index:1}
</style>
@endpush

@section('content')
@php
    $u = auth()->user();
    $name = $u->name ?? 'زائر';
    $metrics = $metrics ?? [];
    $monthly = $seriesMonthlyTreasury ?? ['labels'=>[],'deposits'=>[],'withdrawals'=>[],'net'=>[]];
    $byMethod = $seriesPaymentsByMethod ?? ['labels'=>[],'values'=>[]];
    $duesPaid = $seriesDuesVsPaid ?? ['labels'=>['المستحق','المدفوع'],'values'=>[0,0]];
    $byType  = $seriesByTransactionType ?? ['labels'=>[],'values'=>[]];
    $depWithDaily = $seriesDepVsWithDaily ?? ['labels'=>[],'deposits'=>[],'withdrawals'=>[]];
    $topDebtors = $topDebtors ?? collect();
    $recentTx   = $recentTransactions ?? collect();
@endphp

<div class="container-fluid py-4 wrap">
    <div class="banner">
        <div class="banner-inner">
            <div>
                <h2>مرحباً {{ $name }}</h2>
                <div class="sub">لوحة متابعة الأداء المالي والالتزامات</div>
                <div class="chips">
                    <span class="chip">اليوم: {{ \Carbon\Carbon::now()->format('Y-m-d') }}</span>
                    @if (auth()->id() == 1)
                        <span class="chip">الرصيد الحالي: {{ number_format($metrics['total_balance'] ?? 0,2) }} د.ل</span>
                    @endif
                    @if (auth()->id() == 1)
                        <span class="chip"> اجمالي المستحقات  : {{ number_format($metrics['due_amount'] ?? 0,2) }} د.ل</span>
                    @endif
                    @if (auth()->id() == 1)
                    <span class="chip"> اجمالي السحوبات  : {{ $totalWithdrawal }} د.ل</span>
                @endif
                </div>
            </div>
            @if (auth()->id() == 1)
            <div class="d-flex gap-2 flex-wrap">
                @if(Route::has('finance.students.index'))
                    <a href="{{ route('finance.students.index') }}" class="btn btn-outline-secondary">أقساط الطلاب</a>
                @endif
                @if(Route::has('finance.reports.index'))
                    <a href="{{ route('finance.reports.index') }}" class="btn btn-primary">التقارير</a>
                @endif
            </div>
            @endif
        </div>
    </div>

 
   @if (auth()->id() == 1)
   <div class="grid-6">
    <div class="cardx span-3">
        <div class="cardx-h">
            <h5>حركة الخزينة خلال آخر 12 شهراً</h5>
            <span class="badge badge-soft">صافي شهري</span>
        </div>
        <div class="cardx-b">
            <canvas id="chartMonthly"></canvas>
        </div>
    </div>

    <div class="cardx span-3">
        <div class="cardx-h">
            <h5>توزيع المبالغ حسب نوع المعاملة</h5>
        </div>
        <div class="cardx-b">
            <canvas id="chartByType"></canvas>
        </div>
    </div>

    <div class="cardx span-3">
        <div class="cardx-h">
            <h5>إيداع مقابل سحب (آخر 30 يوم)</h5>
            <span class="badge badge-soft">يومي</span>
        </div>
        <div class="cardx-b">
            <canvas id="chartDepVsWithDaily"></canvas>
        </div>
    </div>

    <div class="cardx span-3">
        <div class="cardx-h">
            <h5>مدفوعات الطلاب حسب الطريقة</h5>
            <span class="badge badge-soft">تراكمي</span>
        </div>
        <div class="cardx-b">
            <canvas id="chartMethods"></canvas>
        </div>
    </div>

    <div class="cardx span-3">
        <div class="cardx-h">
            <h5>مستحق مقابل مدفوع (طلاب)</h5>
        </div>
        <div class="cardx-b">
            <canvas id="chartDuesPaid"></canvas>
        </div>
    </div>

    <div class="cardx span-3">
        <div class="cardx-h">
            <h5>أعلى طلاب مديونية</h5>
            <span class="badge badge-soft">أول 10</span>
        </div>
        <div class="cardx-b">
            <div class="table-wrap">
                <table class="table align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>الطالب</th>
                        <th class="text-end">المستحق</th>
                        <th>إجراء</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($topDebtors as $i => $s)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $s->name }}</td>
                            <td class="text-end"><strong>{{ number_format($s->due_sum,2) }}</strong></td>
                            <td>
                                @if(Route::has('finance.students.show'))
                                    <a href="{{ route('finance.students.show', $s->id) }}" class="btn btn-sm btn-primary">عرض</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">لا توجد بيانات</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="cardx span-6">
        <div class="cardx-h">
            <h5>آخر الحركات المالية</h5>
            <span class="badge badge-soft">{{ $recentTx->count() }} عنصر</span>
        </div>
        <div class="cardx-b">
            <div class="table-wrap">
                <table class="table align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>التاريخ</th>
                        <th>البيان</th>
                        <th>النوع</th>
                        <th>الخزينة</th>
                        <th class="text-end">المبلغ</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($recentTx as $i => $t)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($t->created_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ $t->description }}</td>
                            <td>
                                <span class="badge {{ $t->transaction_type==='deposit' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $t->transaction_type==='deposit' ? 'إيداع' : 'سحب' }}
                                </span>
                            </td>
                            <td>{{ $t->treasury->name ?? '—' }}</td>
                            <td class="text-end"><strong>{{ number_format($t->amount,2) }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">لا توجد حركات</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
   @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function(){
    const monthly = @json($monthly);
    const byMethod = @json($byMethod);
    const duesPaid = @json($duesPaid);
    const byType = @json($byType);
    const depWithDaily = @json($depWithDaily);

    const el1 = document.getElementById('chartMonthly');
    if (el1) new Chart(el1, { type:'line', data:{
            labels: monthly.labels||[],
            datasets:[
                { label:'إيداعات', data: monthly.deposits||[], tension:.35 },
                { label:'سحوبات', data: monthly.withdrawals||[], tension:.35 },
                { label:'صافي', data: monthly.net||[], tension:.35 }
            ]},
        options:{responsive:true,interaction:{mode:'index',intersect:false},plugins:{legend:{rtl:true}},scales:{y:{beginAtZero:true}}}
    });

    const el2 = document.getElementById('chartByType');
    if (el2) new Chart(el2, { type:'doughnut', data:{ labels: byType.labels||[], datasets:[{ data: byType.values||[] }] }, options:{responsive:true,plugins:{legend:{position:'bottom',rtl:true}}} });

    const el3 = document.getElementById('chartDepVsWithDaily');
    if (el3) new Chart(el3, { type:'bar', data:{
            labels: depWithDaily.labels||[],
            datasets:[
                { label:'إيداع', data: depWithDaily.deposits||[] },
                { label:'سحب', data: depWithDaily.withdrawals||[] }
            ]},
        options:{responsive:true,plugins:{legend:{rtl:true}},scales:{y:{beginAtZero:true}}}
    });

    const el4 = document.getElementById('chartMethods');
    if (el4) new Chart(el4, { type:'doughnut', data:{ labels: byMethod.labels||[], datasets:[{ data: byMethod.values||[] }] }, options:{responsive:true,plugins:{legend:{position:'bottom',rtl:true}}} });

    const el5 = document.getElementById('chartDuesPaid');
    if (el5) new Chart(el5, { type:'bar', data:{ labels: duesPaid.labels||[], datasets:[{ label:'قيمة', data: duesPaid.values||[], borderWidth:1 }] }, options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}} });
})();
</script>
@endpush
