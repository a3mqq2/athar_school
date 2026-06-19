@extends('layouts.app')
@section('title', 'سجلات الحضور')

@push('styles')
<style>
    .filter-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 24px;
        border: 1px solid #dee2e6;
    }
    
    .filter-row {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: end;
    }
    
    .filter-item {
        flex: 1;
        min-width: 200px;
    }
    
    .filter-item label {
        color: #333;
        font-weight: 600;
        margin-bottom: 6px;
        font-size: 14px;
        display: block;
    }
    
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .stat-card {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 16px;
        text-align: center;
        transition: transform 0.2s;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
    
    .stat-card.primary {
        border-color: #fbd02b;
        background: #fffbf0;
    }
    
    .stat-card.secondary {
        border-color: #998965;
        background: #f9f8f6;
    }
    
    .stat-number {
        font-size: 28px;
        font-weight: bold;
        color: #333;
        margin-bottom: 4px;
    }
    
    .stat-label {
        color: #6c757d;
        font-size: 14px;
    }
    
    .table-container {
        background: white;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        overflow: hidden;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead {
        background: #fbd02b;
    }
    
    .table thead th {
        color: #333;
        font-weight: 600;
        padding: 14px;
        border: none;
        font-size: 14px;
    }
    
    .table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        color: #495057;
        border-color: #dee2e6;
    }
    
    .table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .badge {
        padding: 6px 12px;
        border-radius: 4px;
        font-weight: 500;
        font-size: 12px;
    }
    
    .badge-teacher {
        background: #e8f5e9;
        color: #2e7d32;
    }
    
    .badge-employee {
        background: #e3f2fd;
        color: #1565c0;
    }
    
    .badge-student {
        background: #fff3e0;
        color: #e65100;
    }
    
    .lessons-badge {
        background: #998965;
        color: white;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 13px;
    }
    
    .btn-icon-sm {
        padding: 6px 10px;
        font-size: 14px;
    }
    
    .btn-export {
        background: #998965;
        color: white;
        border: none;
    }
    
    .btn-export:hover {
        background: #87785a;
        color: white;
    }
    
    .btn-filter {
        background: #fbd02b;
        color: #333;
        border: none;
        padding: 10px 24px;
    }
    
    .btn-filter:hover {
        background: #e6c028;
    }
    
    .btn-reset {
        background: white;
        color: #6c757d;
        border: 1px solid #dee2e6;
        padding: 10px 24px;
    }
    
    .btn-reset:hover {
        background: #f8f9fa;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 64px;
        color: #dee2e6;
        margin-bottom: 16px;
    }
    
    .pagination {
        margin-top: 20px;
    }
    
    .page-link {
        color: #998965;
        border-color: #dee2e6;
    }
    
    .page-link:hover {
        background: #f8f9fa;
        border-color: #dee2e6;
    }
    
    .page-item.active .page-link {
        background: #fbd02b;
        border-color: #fbd02b;
        color: #333;
    }
    
    @media (max-width: 768px) {
        .filter-item {
            min-width: 100%;
        }
        
        .stats-cards {
            grid-template-columns: 1fr;
        }
        
        .table {
            font-size: 13px;
        }
        
        .table thead th,
        .table tbody td {
            padding: 8px 6px;
        }
        
        .hide-mobile {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">سجلات الحضور</h3>
                <div>
                    <button class="btn btn-export btn-icon-sm" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-1"></i> تصدير Excel
                    </button>
                    <button class="btn btn-export btn-icon-sm ms-2" onclick="printTable()">
                        <i class="fas fa-print me-1"></i> طباعة
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form id="filter-form" method="GET" action="{{ route('supervisor.logs') }}">
            <div class="filter-row">
                <div class="filter-item">
                    <label for="date_from">من تاريخ</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="filter-item">
                    <label for="date_to">إلى تاريخ</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="filter-item">
                    <label for="user_search">البحث بالاسم/الكود</label>
                    <input type="text" class="form-control" id="user_search" name="search" placeholder="اسم المستخدم أو الكود" value="{{ request('search') }}">
                </div>
                <div class="filter-item">
                    <label for="role_filter">الصلاحية</label>
                    <select class="form-control" id="role_filter" name="role">
                        <option value="">الكل</option>
                        <option value="teacher" {{ request('role') == 'teacher' ? 'selected' : '' }}>معلم</option>
                        <option value="employee" {{ request('role') == 'employee' ? 'selected' : '' }}>موظف</option>
                        <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>طالب</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label>&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-filter">
                            <i class="fas fa-filter me-1"></i> تطبيق
                        </button>
                        <a href="{{ route('supervisor.logs') }}" class="btn btn-reset ms-2">
                            <i class="fas fa-redo me-1"></i> إعادة تعيين
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-cards">
        <div class="stat-card primary">
            <div class="stat-number">{{ $todayCount ?? 0 }}</div>
            <div class="stat-label">الحضور اليوم</div>
        </div>
        <div class="stat-card secondary">
            <div class="stat-number">{{ $weekCount ?? 0 }}</div>
            <div class="stat-label">هذا الأسبوع</div>
        </div>
        <div class="stat-card primary">
            <div class="stat-number">{{ $monthCount ?? 0 }}</div>
            <div class="stat-label">هذا الشهر</div>
        </div>
        <div class="stat-card secondary">
            <div class="stat-number">{{ $totalLessons ?? 0 }}</div>
            <div class="stat-label">إجمالي الحصص</div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        @if($logs->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>الكود</th>
                    <th>الصلاحية</th>
                    <th>وقت الحضور</th>
                    <th class="hide-mobile">التاريخ</th>
                    <th>الحصص</th>
                    <th class="hide-mobile">المسجل</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>{{ $loop->iteration + (($logs->currentPage() - 1) * $logs->perPage()) }}</td>
                    <td>
                        <strong>{{ $log->user->name }}</strong>
                        <div class="small text-muted hide-mobile">{{ $log->user->email }}</div>
                    </td>
                    <td><code>{{ $log->user->code }}</code></td>
                    <td>
                        @foreach($log->user->roles as $role)
                            <span class="badge badge-{{ $role->name }}">{{ $role->display_name ?? $role->name }}</span>
                        @endforeach
                    </td>
                    <td>{{ $log->check_in_time->format('h:i A') }}</td>
                    <td class="hide-mobile">{{ $log->date->format('Y-m-d') }}</td>
                    <td>
                        @if($log->lessons_count)
                            <span class="lessons-badge">{{ $log->lessons_count }} حصة</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="hide-mobile">{{ $log->supervisor->name }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewDetails({{ $log->id }})" title="عرض التفاصيل">
                            <i class="fas fa-eye"></i>
                        </button>
                        @if($log->created_at->isToday())
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteLog({{ $log->id }})" title="حذف">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $logs->links() }}
        </div>
        @else
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h5>لا توجد سجلات</h5>
            <p>لم يتم العثور على أي سجلات حضور للفترة المحددة</p>
        </div>
        @endif
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #fbd02b;">
                <h5 class="modal-title">تفاصيل السجل</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-content">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function viewDetails(id) {
        fetch(`/supervisor/logs/${id}`)
            .then(response => response.json())
            .then(data => {
                const content = `
                    <div class="mb-3">
                        <strong>الاسم:</strong> ${data.user.name}
                    </div>
                    <div class="mb-3">
                        <strong>البريد الإلكتروني:</strong> ${data.user.email}
                    </div>
                    <div class="mb-3">
                        <strong>الكود:</strong> <code>${data.user.code}</code>
                    </div>
                    <div class="mb-3">
                        <strong>وقت الحضور:</strong> ${data.check_in_time}
                    </div>
                    <div class="mb-3">
                        <strong>التاريخ:</strong> ${data.date}
                    </div>
                    ${data.lessons_count ? `<div class="mb-3"><strong>عدد الحصص:</strong> ${data.lessons_count}</div>` : ''}
                    <div class="mb-3">
                        <strong>سجل بواسطة:</strong> ${data.supervisor.name}
                    </div>
                    ${data.notes ? `<div class="mb-3"><strong>ملاحظات:</strong> ${data.notes}</div>` : ''}
                `;
                document.getElementById('modal-content').innerHTML = content;
                new bootstrap.Modal(document.getElementById('detailsModal')).show();
            });
    }
    
    function deleteLog(id) {
        if (!confirm('هل أنت متأكد من حذف هذا السجل؟')) return;
        
        fetch(`/supervisor/logs/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'حدث خطأ');
            }
        });
    }
    
    function exportToExcel() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = `/supervisor/logs/export?${params.toString()}`;
    }
    
    function printTable() {
        window.print();
    }
</script>
@endpush