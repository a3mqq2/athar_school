{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('title','لوحة الإدارة')

@push('styles')
<style>
    :root{--primary:#3b82f6;--muted:#6b7280;--light:#f8fafc;--border:#e5e7eb;--success:#10b981;--danger:#ef4444;--warning:#f59e0b}
    .container, .container-fluid{overflow:visible}
    .wrap{display:grid;gap:16px}
    .banner{position:relative;border-radius:16px;overflow:hidden;background:linear-gradient(135deg,#e0efff 0%,#f4f7ff 100%);border:1px solid var(--border)}
    .banner-inner{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:24px;flex-wrap:wrap}
    .banner h2{margin:0;color:#0f172a;font-weight:800}
    .banner .sub{color:var(--muted)}
    .chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
    .chip{background:#fff;border:1px solid var(--border);padding:6px 10px;border-radius:999px;font-size:.875rem}

    .auto-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;align-items:stretch}

    .grid-6{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px;align-items:start}
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
    .kpi .val.warning{color:var(--warning)}
    .pill{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;border:1px solid var(--border);background:#fff;white-space:nowrap}

    .cardx{background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden;display:flex;flex-direction:column;min-height:280px}
    .cardx-h{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:var(--light);border-bottom:1px solid var(--border)}
    .cardx-h h5{margin:0;color:#0f172a;font-weight:800}
    .cardx-b{padding:12px;display:flex;flex-direction:column;height:100%}
    .cardx-b canvas{width:100% !important;max-width:100%;aspect-ratio: 16/9}
    .badge-soft{background:rgba(59,130,246,.1);color:var(--primary);font-weight:700}

    .table-wrap{overflow:auto;border-radius:12px;border:1px solid var(--border)}
    .table{margin-bottom:0}
    .table thead th{position:sticky;top:0;background:var(--light)!important;z-index:1}
</style>
@endpush

@section('content')
@php
    $u = auth()->user();
    $name = $u->name ?? 'مسؤول';
    $metrics = $metrics ?? [
        'users_count' => $users_count ?? 0,
        'students_count' => $students_count ?? 0,
        'active_students' => $active_students ?? 0,
        'today_attendance' => $today_attendance ?? 0,
        'classes_count' => $classes_count ?? 0,
        'sections_count' => $sections_count ?? 0,
        'overdue_dues' => $overdue_dues ?? 0,
        'pending_settlements' => $pending_settlements ?? 0,
    ];
    $seriesUsersMonthly = $seriesUsersMonthly ?? ['labels'=>[],'values'=>[]];
    $seriesStudentsMonthly = $seriesStudentsMonthly ?? ['labels'=>[],'values'=>[]];
    $seriesAttendanceDaily = $seriesAttendanceDaily ?? ['labels'=>[],'present'=>[],'absent'=>[]];
    $seriesTxTypes = $seriesTxTypes ?? ['labels'=>[],'values'=>[]];
    $recentUsers = $recentUsers ?? collect();
    $recentLogs = $recentLogs ?? collect();
@endphp

<div class="container-fluid py-4 wrap">
    <div class="banner">
        <div class="banner-inner">
            <div>
                <h2>مرحباً {{ $name }}</h2>
                <div class="sub">لوحة إدارة عامة لإحصاءات المنظومة والمتابعة اليومية</div>
                <div class="chips">
                    <span class="chip">اليوم: {{ \Carbon\Carbon::now()->format('Y-m-d') }}</span>
                    @if (auth()->id() == 1)
                    <span class="chip">عدد المستخدمين: {{ number_format($metrics['users_count'] ?? 0) }}</span>
                    <span class="chip">عدد الطلاب: {{ number_format($metrics['students_count'] ?? 0) }}</span>
                    @endif
                </div>
            </div>
            @if (auth()->id() == 1)
                <div class="d-flex gap-2 flex-wrap">
                    @if(Route::has('admin.users.index'))
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">المستخدمون</a>
                    @endif
                    @if(Route::has('finance.dashboard'))
                        <a href="{{ route('finance.dashboard') }}" class="btn btn-primary">لوحة المالية</a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if (auth()->id() == 1)

    <div class="auto-grid">
        <div class="kpi">
            <div>
                <h6>المستخدمون</h6>
                <div class="val primary">{{ number_format($metrics['users_count'] ?? 0) }}</div>
            </div>
            <div class="pill"><span>آخر شهر</span><strong>{{ number_format($seriesUsersMonthly['values'][count($seriesUsersMonthly['values'])-1] ?? 0) }}</strong></div>
        </div>
        <div class="kpi">
            <div>
                <h6>الطلاب</h6>
                <div class="val success">{{ number_format($metrics['students_count'] ?? 0) }}</div>
            </div>
            <div class="pill"><span>نشط</span><strong>{{ number_format($metrics['active_students'] ?? 0) }}</strong></div>
        </div>
        <div class="kpi">
            <div>
                <h6>حضور اليوم</h6>
                <div class="val">{{ number_format($metrics['today_attendance'] ?? 0) }}</div>
            </div>
            <div class="pill"><span>أقسام</span><strong>{{ number_format(($metrics['sections_count'] ?? 0)) }}</strong></div>
        </div>
        <div class="kpi">
            <div>
                <h6>متأخرات أقساط</h6>
                <div class="val danger">{{ number_format($metrics['overdue_dues'] ?? 0,2) }} د.ل</div>
            </div>
            <div class="pill"><span>تسويات معلقة</span><strong>{{ number_format($metrics['pending_settlements'] ?? 0) }}</strong></div>
        </div>
    </div>

    <div class="grid-6">
        <div class="cardx span-3">
            <div class="cardx-h">
                <h5>تسجيل المستخدمين خلال 12 شهراً</h5>
                <span class="badge badge-soft">Users</span>
            </div>
            <div class="cardx-b">
                <canvas id="chartUsersMonthly"></canvas>
            </div>
        </div>

        <div class="cardx span-3">
            <div class="cardx-h">
                <h5>الطلاب الجدد خلال 12 شهراً</h5>
                <span class="badge badge-soft">Students</span>
            </div>
            <div class="cardx-b">
                <canvas id="chartStudentsMonthly"></canvas>
            </div>
        </div>

        <div class="cardx span-3">
            <div class="cardx-h">
                <h5>حضور آخر 30 يوم</h5>
                <span class="badge badge-soft">Attendance</span>
            </div>
            <div class="cardx-b">
                <canvas id="chartAttendanceDaily"></canvas>
            </div>
        </div>

        <div class="cardx span-3">
            <div class="cardx-h">
                <h5>توزيع حسب نوع الحركة المالية</h5>
                <span class="badge badge-soft">Transaction Types</span>
            </div>
            <div class="cardx-b">
                <canvas id="chartTxTypes"></canvas>
            </div>
        </div>

        <div class="cardx span-3">
            <div class="cardx-h">
                <h5>أحدث المستخدمين</h5>
                <span class="badge badge-soft">{{ $recentUsers->count() }} عنصر</span>
            </div>
            <div class="cardx-b">
                <div class="table-wrap">
                    <table class="table align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>البريد</th>
                            <th>الدور</th>
                            <th>التاريخ</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentUsers as $i => $u)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $u->name }}</td>
                                <td class="text-muted">{{ $u->email }}</td>
                                <td class="text-muted">{{ implode(', ', $u->roles->pluck('name')->toArray() ?? []) }}</td>
                                <td>{{ \Carbon\Carbon::parse($u->created_at)->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">لا توجد بيانات</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="cardx span-3">
            <div class="cardx-h">
                <h5>آخر سجلات النظام</h5>
                <span class="badge badge-soft">{{ $recentLogs->count() }} عنصر</span>
            </div>
            <div class="cardx-b">
                <div class="table-wrap">
                    <table class="table align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>التاريخ</th>
                            <th>المستخدم</th>
                            <th>الحدث</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentLogs as $i => $log)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</td>
                                <td>{{ $log->user->name ?? '—' }}</td>
                                <td class="text-muted">{{ $log->description ?? '—' }}</td>
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
                <h5>نظرة عامة سريعة</h5>
                <span class="badge badge-soft">ملخص</span>
            </div>
            <div class="cardx-b">
                <div class="auto-grid">
                    <div class="kpi">
                        <div>
                            <h6>الفصول</h6>
                            <div class="val">{{ number_format($metrics['classes_count'] ?? 0) }}</div>
                        </div>
                        <div class="pill"><span>الأقسام</span><strong>{{ number_format($metrics['sections_count'] ?? 0) }}</strong></div>
                    </div>
                    <div class="kpi">
                        <div>
                            <h6>طلاب نشطون</h6>
                            <div class="val success">{{ number_format($metrics['active_students'] ?? 0) }}</div>
                        </div>
                        <div class="pill"><span>إجمالي</span><strong>{{ number_format($metrics['students_count'] ?? 0) }}</strong></div>
                    </div>
                    <div class="kpi">
                        <div>
                            <h6>متأخرات الأقساط</h6>
                            <div class="val danger">{{ number_format($metrics['overdue_dues'] ?? 0,2) }} د.ل</div>
                        </div>
                        <div class="pill"><span>تسويات معلقة</span><strong>{{ number_format($metrics['pending_settlements'] ?? 0) }}</strong></div>
                    </div>
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
    const usersMonthly = @json($seriesUsersMonthly);
    const studentsMonthly = @json($seriesStudentsMonthly);
    const attendanceDaily = @json($seriesAttendanceDaily);
    const txTypes = @json($seriesTxTypes);

    const el1 = document.getElementById('chartUsersMonthly');
    if (el1) new Chart(el1, {
        type:'line',
        data:{ labels: usersMonthly.labels||[], datasets:[{ label:'مستخدمون', data: usersMonthly.values||[], tension:.35 }] },
        options:{ responsive:true, scales:{ y:{ beginAtZero:true } }, plugins:{ legend:{ rtl:true } } }
    });

    const el2 = document.getElementById('chartStudentsMonthly');
    if (el2) new Chart(el2, {
        type:'bar',
        data:{ labels: studentsMonthly.labels||[], datasets:[{ label:'طلاب', data: studentsMonthly.values||[], borderWidth:1 }] },
        options:{ responsive:true, scales:{ y:{ beginAtZero:true } }, plugins:{ legend:{ display:false } } }
    });

    const el3 = document.getElementById('chartAttendanceDaily');
    if (el3) new Chart(el3, {
        type:'bar',
        data:{
            labels: attendanceDaily.labels||[],
            datasets:[
                { label:'حاضر', data: attendanceDaily.present||[] },
                { label:'غائب', data: attendanceDaily.absent||[] }
            ]
        },
        options:{ responsive:true, scales:{ y:{ beginAtZero:true } }, plugins:{ legend:{ rtl:true } } }
    });

    const el4 = document.getElementById('chartTxTypes');
    if (el4) new Chart(el4, {
        type:'doughnut',
        data:{ labels: txTypes.labels||[], datasets:[{ data: txTypes.values||[] }] },
        options:{ responsive:true, plugins:{ legend:{ position:'bottom', rtl:true } } }
    });
})();
</script>
@endpush
