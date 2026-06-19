{{-- resources/views/finance/reports/teachers/view.blade.php --}}
@extends('layouts.app')

@section('title', 'تقرير تسوية المعلمين')

@push('styles')
<style>
    :root {
        --primary: #925419;
        --success: #28a745;
        --warning: #ffc107;
        --danger: #dc3545;
        --info: #17a2b8;
        --light: #f8f9fa;
        --border: #e1e5eb;
        --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .report-header {
        background: linear-gradient(135deg, var(--success), #1e7e34);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        text-align: center;
    }

    .report-title {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }

    .report-subtitle {
        opacity: 0.9;
        font-size: 1.1rem;
    }

    .filters-summary {
        background: rgba(255, 255, 255, 0.1);
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
        font-size: 0.9rem;
    }

    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border-left: 4px solid;
        box-shadow: var(--shadow);
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: -20px;
        right: -20px;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        opacity: 0.1;
    }

    .stats-card.lessons { 
        border-left-color: var(--info);
    }
    .stats-card.lessons::before { background: var(--info); }

    .stats-card.calculated { 
        border-left-color: var(--primary);
    }
    .stats-card.calculated::before { background: var(--primary); }

    .stats-card.settled { 
        border-left-color: var(--success);
    }
    .stats-card.settled::before { background: var(--success); }

    .stats-card.pending { 
        border-left-color: var(--warning);
    }
    .stats-card.pending::before { background: var(--warning); }

    .stats-icon {
        font-size: 2rem;
        margin-bottom: 1rem;
        opacity: 0.7;
    }

    .stats-card.lessons .stats-icon { color: var(--info); }
    .stats-card.calculated .stats-icon { color: var(--primary); }
    .stats-card.settled .stats-icon { color: var(--success); }
    .stats-card.pending .stats-icon { color: var(--warning); }

    .stats-value {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .stats-value .currency {
        font-size: 1rem;
        font-weight: 500;
        opacity: 0.7;
        margin-right: 3px;
    }

    .stats-label {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .report-table-container {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow);
    }

    .table-header {
        background: var(--light);
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--primary);
        margin: 0;
    }

    .table-responsive {
        max-height: 600px;
        overflow-y: auto;
    }

    .table {
        margin: 0;
    }

    .table th {
        background: var(--light);
        border-top: none;
        font-weight: 600;
        color: #495057;
        position: sticky;
        top: 0;
        z-index: 10;
        white-space: nowrap;
    }

    .table td {
        vertical-align: middle;
    }

    .teacher-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--success);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        margin-left: 0.75rem;
    }

    .teacher-info {
        display: flex;
        align-items: center;
    }

    .teacher-name {
        font-weight: 600;
        color: #495057;
    }

    .progress-bar {
        height: 20px;
        border-radius: 10px;
        background: #e9ecef;
        overflow: hidden;
        position: relative;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--success), #20c997);
        border-radius: 10px;
        transition: width 0.3s ease;
        position: relative;
    }

    .progress-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 0.75rem;
        font-weight: 700;
        color: #333;
        z-index: 2;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-badge.completed {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success);
        border: 1px solid rgba(40, 167, 69, 0.2);
    }

    .status-badge.pending {
        background: rgba(255, 193, 7, 0.1);
        color: #856404;
        border: 1px solid rgba(255, 193, 7, 0.2);
    }

    .status-badge.partial {
        background: rgba(23, 162, 184, 0.1);
        color: var(--info);
        border: 1px solid rgba(23, 162, 184, 0.2);
    }

    .actions-bar {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        justify-content: space-between;
        align-items: center;
    }

    .filters-display {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-tag {
        background: var(--light);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        border: 1px solid var(--border);
        color: #495057;
    }

    .btn-group {
        display: flex;
        gap: 0.5rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary { background: var(--primary); color: white; }
    .btn-success { background: var(--success); color: white; }
    .btn-secondary { background: #6c757d; color: white; }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow);
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .amount-cell {
        font-weight: 700;
        font-size: 0.95rem;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }

    .amount-cell .currency {
        font-size: 0.78rem;
        font-weight: 500;
        opacity: 0.65;
        margin-right: 2px;
    }

    .amount-words {
        display: block;
        font-size: 0.72rem;
        font-weight: 400;
        color: #6c757d;
        margin-top: 2px;
        white-space: normal;
        line-height: 1.3;
    }

    .stats-card .amount-words {
        font-size: 0.8rem;
        margin-top: 4px;
        color: rgba(0,0,0,0.45);
    }

    .pending-danger {
        color: var(--danger) !important;
    }

    tfoot td {
        font-weight: 700;
        background: #f1f3f5;
        border-top: 2px solid var(--border);
    }

    @media (max-width: 768px) {
        .stats-cards {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .actions-bar {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }
        
        .filters-display {
            justify-content: center;
        }
        
        .btn-group {
            width: 100%;
        }
        
        .btn {
            flex: 1;
            justify-content: center;
        }

        .teacher-avatar {
            width: 30px;
            height: 30px;
            font-size: 0.8rem;
        }
    }

    @media print {
        .actions-bar, .btn-group { display: none !important; }
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="report-header">
        <h1 class="report-title">تقرير تسوية المعلمين</h1>
        <p class="report-subtitle">متابعة حسابات الحصص المحسوبة والمدفوعة للمعلمين</p>
        <div class="filters-summary">
            الفترة: {{ Carbon\Carbon::parse($filters['from'])->format('Y/m/d') }} - {{ Carbon\Carbon::parse($filters['to'])->format('Y/m/d') }}
            @if($filters['teacher_id'])
                | معلم محدد
            @endif
            @if($filters['status'])
                | حالة: {{ $filters['status'] === 'pending' ? 'مستحق' : 'مسدد' }}
            @endif
        </div>
    </div>

    <div class="actions-bar">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('finance.reports.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-right"></i>العودة للتقارير
            </a>
            <div class="filters-display">
                <span class="filter-tag">{{ $report['rows']->count() }} معلم</span>
                @if($filters['status'])
                    <span class="filter-tag">{{ $filters['status'] === 'pending' ? 'مستحق فقط' : 'مسدد فقط' }}</span>
                @endif
            </div>
        </div>
        <div class="btn-group">
            <a href="{{ request()->fullUrlWithQuery(['format' => 'print']) }}" 
               class="btn btn-success" target="_blank">
                <i class="fa fa-print"></i>طباعة
            </a>
        </div>
    </div>

    <div class="stats-cards">
        <div class="stats-card lessons">
            <div class="stats-icon">
                <i class="fa fa-chalkboard"></i>
            </div>
            <div class="stats-value" style="color: var(--info)">{{ number_format($report['totals']['total_lessons']) }}</div>
            <div class="stats-label">إجمالي الدروس</div>
        </div>
        <div class="stats-card calculated">
            <div class="stats-icon">
                <i class="fa fa-calculator"></i>
            </div>
            <div class="stats-value" style="color: var(--primary)">
                {{ number_format($report['totals']['calculated_amount'], 2) }}
                <span class="currency">د.ل</span>
                <span class="amount-words">{{ numberToArabicWords($report['totals']['calculated_amount']) }}</span>
            </div>
            <div class="stats-label">المبلغ المحسوب</div>
        </div>
        <div class="stats-card settled">
            <div class="stats-icon">
                <i class="fa fa-check-circle"></i>
            </div>
            <div class="stats-value text-success">
                {{ number_format($report['totals']['settled_amount'], 2) }}
                <span class="currency">د.ل</span>
                <span class="amount-words">{{ numberToArabicWords($report['totals']['settled_amount']) }}</span>
            </div>
            <div class="stats-label">المبلغ المسدد</div>
        </div>
        <div class="stats-card pending">
            <div class="stats-icon">
                <i class="fa fa-clock"></i>
            </div>
            <div class="stats-value" style="color: var(--warning)">
                {{ number_format($report['totals']['pending_amount'], 2) }}
                <span class="currency">د.ل</span>
                <span class="amount-words">{{ numberToArabicWords($report['totals']['pending_amount']) }}</span>
            </div>
            <div class="stats-label">المتبقي</div>
        </div>
    </div>

    <div class="report-table-container">
        <div class="table-header">
            <h3 class="table-title">تفاصيل التسويات</h3>
            <span class="badge bg-success">{{ $report['rows']->count() }} معلم</span>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="25%">المعلم</th>
                        <th width="10%" class="text-center">الدروس</th>
                        <th width="15%" class="text-end">محسوب</th>
                        <th width="15%" class="text-end">مسدد</th>
                        <th width="15%" class="text-end">متبقي</th>
                        <th width="10%" class="text-center">التقدم</th>
                        <th width="5%" class="text-center">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['rows'] as $index => $teacher)
                        @php
                            $progress = $teacher['calculated_amount'] > 0 ? ($teacher['settled_amount'] / $teacher['calculated_amount']) * 100 : 0;
                            $status = $teacher['pending_amount'] == 0 ? 'completed' : ($teacher['settled_amount'] > 0 ? 'partial' : 'pending');
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="teacher-info">
                                    <div class="teacher-avatar">
                                        {{ substr($teacher['name'], 0, 1) }}
                                    </div>
                                    <div class="teacher-name">{{ $teacher['name'] }}</div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ number_format($teacher['total_lessons']) }}</span>
                            </td>
                            <td class="text-end amount-cell">
                                {{ number_format($teacher['calculated_amount'], 2) }}
                                <span class="currency">د.ل</span>
                                <span class="amount-words">{{ numberToArabicWords($teacher['calculated_amount']) }}</span>
                            </td>
                            <td class="text-end amount-cell text-success">
                                {{ number_format($teacher['settled_amount'], 2) }}
                                <span class="currency">د.ل</span>
                                <span class="amount-words">{{ numberToArabicWords($teacher['settled_amount']) }}</span>
                            </td>
                            <td class="text-end amount-cell {{ $teacher['pending_amount'] > 0 ? 'pending-danger' : 'text-success' }}">
                                {{ number_format($teacher['pending_amount'], 2) }}
                                <span class="currency">د.ل</span>
                                <span class="amount-words">{{ numberToArabicWords($teacher['pending_amount']) }}</span>
                            </td>
                            <td class="text-center">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $progress }}%"></div>
                                    <div class="progress-text">{{ number_format($progress, 0) }}%</div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($status === 'completed')
                                    <span class="status-badge completed">
                                        <i class="fa fa-check"></i>
                                        مكتمل
                                    </span>
                                @elseif($status === 'partial')
                                    <span class="status-badge partial">
                                        <i class="fa fa-hourglass-half"></i>
                                        جزئي
                                    </span>
                                @else
                                    <span class="status-badge pending">
                                        <i class="fa fa-clock"></i>
                                        مستحق
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fa fa-chalkboard-teacher"></i>
                                    <p>لا توجد بيانات تسويات للمعلمين في الفترة المحددة</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($report['rows']->count() > 0)
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-end">الإجمالي</td>
                        <td class="text-center">
                            <span class="badge bg-info">{{ number_format($report['totals']['total_lessons']) }}</span>
                        </td>
                        <td class="text-end amount-cell">
                            {{ number_format($report['totals']['calculated_amount'], 2) }}
                            <span class="currency">د.ل</span>
                            <span class="amount-words">{{ numberToArabicWords($report['totals']['calculated_amount']) }}</span>
                        </td>
                        <td class="text-end amount-cell text-success">
                            {{ number_format($report['totals']['settled_amount'], 2) }}
                            <span class="currency">د.ل</span>
                            <span class="amount-words">{{ numberToArabicWords($report['totals']['settled_amount']) }}</span>
                        </td>
                        <td class="text-end amount-cell {{ $report['totals']['pending_amount'] > 0 ? 'pending-danger' : 'text-success' }}">
                            {{ number_format($report['totals']['pending_amount'], 2) }}
                            <span class="currency">د.ل</span>
                            <span class="amount-words">{{ numberToArabicWords($report['totals']['pending_amount']) }}</span>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    @if($report['rows']->count() > 0)
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">معلمين مكتملين</h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="text-success">{{ $report['rows']->where('pending_amount', 0)->count() }}</h3>
                        <small class="text-muted">من أصل {{ $report['rows']->count() }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h6 class="mb-0">معلمين مستحقين</h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="text-warning">{{ $report['rows']->where('pending_amount', '>', 0)->count() }}</h3>
                        <small class="text-muted">يحتاجون تسوية</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">متوسط التسوية</h6>
                    </div>
                    <div class="card-body text-center">
                        @php
                            $avgProgress = $report['totals']['calculated_amount'] > 0 ? 
                                ($report['totals']['settled_amount'] / $report['totals']['calculated_amount']) * 100 : 0;
                        @endphp
                        <h3 class="text-primary">{{ number_format($avgProgress, 1) }}%</h3>
                        <small class="text-muted">نسبة إنجاز عامة</small>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection 