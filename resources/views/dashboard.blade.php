@extends('layouts.app')

@section('title', 'لوحة التحكم')

@push('styles')
<style>
    .stats-card {
        background: linear-gradient(135deg, #fff, #f8f9fa);
        border-radius: 15px;
        border: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    }
    
    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--card-color, #173a5e);
    }
    
    .stats-card.primary::before { background: #173a5e; }
    .stats-card.success::before { background: #28a745; }
    .stats-card.warning::before { background: #ffc107; }
    .stats-card.info::before { background: #17a2b8; }
    .stats-card.danger::before { background: #dc3545; }
    .stats-card.purple::before { background: #6f42c1; }
    .stats-card.orange::before { background: #fd7e14; }
    .stats-card.teal::before { background: #20c997; }
    
    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        margin-bottom: 15px;
    }
    
    .stats-icon.primary { background: linear-gradient(135deg, #173a5e, #23a89b); }
    .stats-icon.success { background: linear-gradient(135deg, #28a745, #20c997); }
    .stats-icon.warning { background: linear-gradient(135deg, #ffc107, #fd7e14); }
    .stats-icon.info { background: linear-gradient(135deg, #17a2b8, #007bff); }
    .stats-icon.danger { background: linear-gradient(135deg, #dc3545, #e83e8c); }
    .stats-icon.purple { background: linear-gradient(135deg, #6f42c1, #e83e8c); }
    .stats-icon.orange { background: linear-gradient(135deg, #fd7e14, #ffc107); }
    .stats-icon.teal { background: linear-gradient(135deg, #20c997, #17a2b8); }
    
    .stats-number {
        font-size: 32px;
        font-weight: bold;
        margin-bottom: 5px;
        color: #2c3e50;
    }
    
    .stats-label {
        color: #6c757d;
        font-size: 14px;
        margin-bottom: 10px;
    }
    
    .stats-change {
        font-size: 12px;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
    }
    
    .stats-change.positive {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }
    
    .stats-change.negative {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    
    .chart-card {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: none;
    }
    
    .chart-header {
        padding: 20px 20px 0;
        border-bottom: 1px solid #eee;
    }
    
    .recent-activity {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .activity-item {
        padding: 15px 20px;
        border-bottom: 1px solid #f1f3f4;
        transition: background 0.2s;
    }
    
    .activity-item:hover {
        background: #f8f9fa;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: white;
        margin-left: 15px;
    }
    
    .status-processing { background: #ffc107; }
    .status-in-transit { background: #17a2b8; }
    .status-delivered { background: #28a745; }
    .status-cancelled { background: #dc3545; }
    
    .progress-ring {
        transform: rotate(-90deg);
    }
    
    .progress-ring-circle {
        stroke: #e6e6e6;
        fill: transparent;
        stroke-width: 8;
    }
    
    .progress-ring-progress {
        stroke: #173a5e;
        fill: transparent;
        stroke-width: 8;
        stroke-linecap: round;
        stroke-dasharray: 314;
        stroke-dashoffset: 314;
        transition: stroke-dashoffset 0.5s ease-in-out;
    }
    
    .top-institutions {
        padding: 0;
    }
    
    .institution-item {
        padding: 15px 20px;
        border-bottom: 1px solid #f1f3f4;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .institution-item:last-child {
        border-bottom: none;
    }
    
    .institution-rank {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #173a5e;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
        margin-left: 15px;
    }
    
    .quick-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .quick-action-btn {
        flex: 1;
        min-width: 150px;
        padding: 15px;
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        background: transparent;
        text-decoration: none;
        color: #6c757d;
        text-align: center;
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    
    .quick-action-btn:hover {
        border-color: #173a5e;
        color: #173a5e;
        background: rgba(45, 90, 5, 0.05);
        text-decoration: none;
    }
    
    .quick-action-icon {
        font-size: 24px;
    }
    
    .dashboard-header {
        background: linear-gradient(135deg, #173a5e 0%, #23a89b 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    
    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
        animation: float 20s infinite linear;
    }
    
    @keyframes float {
        0% { transform: translateX(-100px) translateY(-100px); }
        100% { transform: translateX(100px) translateY(100px); }
    }
    
    .welcome-text {
        position: relative;
        z-index: 1;
    }
    
    .refresh-btn {
        position: absolute;
        top: 20px;
        left: 20px;
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .refresh-btn:hover {
        background: rgba(255,255,255,0.3);
        transform: rotate(180deg);
    }
    
    @media (max-width: 768px) {
        .stats-number {
            font-size: 24px;
        }
        
        .stats-icon {
            width: 50px;
            height: 50px;
            font-size: 20px;
        }
        
        .quick-actions {
            flex-direction: column;
        }
        
        .quick-action-btn {
            min-width: auto;
        }
    }
</style>
@endpush

@section('content')
<!-- رأس الصفحة -->
<div class="dashboard-header mt-4">
    <button class="refresh-btn" onclick="refreshDashboard()" title="تحديث البيانات">
        <i class="ti ti-refresh"></i>
    </button>
    <div class="welcome-text">
        <h2 class="mb-2 text-white">مرحباً {{ auth()->user()->name }}! 👋</h2>
        <p class="mb-0 opacity-90">نظرة عامة على نشاط النظام اليوم {{ now()->format('Y-m-d') }}</p>
    </div>
</div>

<!-- الإحصائيات الرئيسية -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stats-card primary">
            <div class="card-body text-center">
                <div class="stats-icon primary">
                    <i class="ti ti-package"></i>
                </div>
                <div class="stats-number" id="totalShipments">{{ number_format($totalStats['total_shipments']) }}</div>
                <div class="stats-label">إجمالي الشحنات</div>
                <div class="stats-change positive">
                    <i class="ti ti-arrow-up"></i> +{{ $todayStats['shipments'] }} اليوم
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stats-card success">
            <div class="card-body text-center">
                <div class="stats-icon success">
                    <i class="fa fa-check-circle"></i>
                </div>
                <div class="stats-number">{{ number_format($statusStats['delivered']) }}</div>
                <div class="stats-label">تم التسليم</div>
                <div class="stats-change positive">
                    <i class="ti ti-arrow-up"></i> +{{ $todayStats['delivered'] }} اليوم
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stats-card warning">
            <div class="card-body text-center">
                <div class="stats-icon warning">
                    <i class="ti ti-clock"></i>
                </div>
                <div class="stats-number">{{ number_format($statusStats['processing']) }}</div>
                <div class="stats-label">قيد التوصيل</div>
                <div class="stats-change {{ $processingShipments->count() > 5 ? 'negative' : 'positive' }}">
                    <i class="ti ti-alert-circle"></i> {{ $processingShipments->count() }} تحتاج متابعة
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stats-card info">
            <div class="card-body text-center">
                <div class="stats-icon info">
                    <i class="ti ti-building"></i>
                </div>
                <div class="stats-number">{{ number_format($totalStats['total_institutions']) }}</div>
                <div class="stats-label">المؤسسات</div>
                <div class="stats-change positive">
                    <i class="ti ti-users"></i> {{ number_format($totalStats['total_users']) }} مستخدم
                </div>
            </div>
        </div>
    </div>
</div>

<!-- البطاقات المالية (للمصرح لهم) -->
@if(auth()->user()->permissions->where('name', 'view_financials')->count() > 0)
@if($financialStats)
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stats-card purple">
            <div class="card-body text-center">
                <div class="stats-icon purple">
                    <i class="ti ti-coin"></i>
                </div>
                <div class="stats-number">{{ number_format($financialStats['today_revenue'], 2) }}</div>
                <div class="stats-label">إيرادات اليوم (د.ل)</div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stats-card orange">
            <div class="card-body text-center">
                <div class="stats-icon orange">
                    <i class="ti ti-chart-line"></i>
                </div>
                <div class="stats-number">{{ number_format($financialStats['month_revenue'], 2) }}</div>
                <div class="stats-label">إيرادات الشهر (د.ل)</div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stats-card teal">
            <div class="card-body text-center">
                <div class="stats-icon teal">
                    <i class="ti ti-wallet"></i>
                </div>
                <div class="stats-number">{{ number_format($financialStats['total_revenue'], 2) }}</div>
                <div class="stats-label">إجمالي الإيرادات (د.ل)</div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stats-card danger">
            <div class="card-body text-center">
                <div class="stats-icon danger">
                    <i class="ti ti-calculator"></i>
                </div>
                <div class="stats-number">{{ number_format($financialStats['average_shipment_value'], 2) }}</div>
                <div class="stats-label">متوسط قيمة الشحنة (د.ل)</div>
            </div>
        </div>
    </div>
</div>
@endif
@endif

<!-- الإجراءات السريعة -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti ti-zap me-2"></i>
                    الإجراءات السريعة
                </h5>
            </div>
            <div class="card-body">
                <div class="quick-actions">
                    <a href="{{ route('shipments.create') }}" class="quick-action-btn">
                        <div class="quick-action-icon">
                            <i class="ti ti-package-plus"></i>
                        </div>
                        <span>إضافة شحنة جديدة</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="quick-action-btn">
                        <div class="quick-action-icon">
                            <i class="ti ti-chart-bar"></i>
                        </div>
                        <span>عرض التقارير</span>
                    </a>
                    <a href="{{ route('shipments.index') }}?status=pending" class="quick-action-btn">
                        <div class="quick-action-icon">
                            <i class="ti ti-clock"></i>
                        </div>
                        <span>الشحنات المعلقة</span>
                    </a>
                    <a href="{{ route('shipments.index') }}?status=processing" class="quick-action-btn">
                        <div class="quick-action-icon">
                            <i class="ti ti-truck"></i>
                        </div>
                        <span>في الطريق</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- الأداء الشهري -->
    <div class="col-lg-8 mb-4">
        <div class="card chart-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti ti-trending-up me-2"></i>
                    الأداء الشهري (آخر 6 شهور)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- إحصائيات الحالات -->
    <div class="col-lg-4 mb-4">
        <div class="card chart-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti ti-pie-chart me-2"></i>
                    توزيع حالات الشحنات
                </h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- أفضل المؤسسات -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti ti-trophy me-2"></i>
                    أفضل المؤسسات
                </h5>
            </div>
            <div class="card-body top-institutions">
                @forelse($topInstitutions as $index => $institution)
                <div class="institution-item">
                    <div class="d-flex align-items-center">
                        <div class="institution-rank">{{ $index + 1 }}</div>
                        <div>
                            <h6 class="mb-0">{{ $institution->name }}</h6>
                            <small class="text-muted">{{ $institution->shipments_count }} شحنة</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary">{{ number_format($institution->shipments_count) }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="ti ti-inbox fs-1"></i>
                    <p>لا توجد بيانات</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- الشحنات الحديثة -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ti ti-history me-2"></i>
                    الشحنات الحديثة
                </h5>
                <a href="{{ route('shipments.index') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
            </div>
            <div class="card-body recent-activity">
                @forelse($recentShipments as $shipment)
                <div class="activity-item">
                    <div class="d-flex align-items-center">
                        <div class="activity-icon status-{{ $shipment->status }}">
                            @switch($shipment->status)
                                @case('processing')
                                    <i class="ti ti-clock"></i>
                                    @break
                                @case('in_transit')
                                    <i class="ti ti-truck"></i>
                                    @break
                                @case('delivered')
                                    <i class="ti ti-check"></i>
                                    @break
                                @case('cancelled')
                                    <i class="ti ti-x"></i>
                                    @break
                                @default
                                    <i class="ti ti-package"></i>
                            @endswitch
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $shipment->reference_number }}</h6>
                            <p class="mb-1 text-muted small">{{ Str::limit($shipment->subject, 40) }}</p>
                            <small class="text-muted">
                                {{ $shipment->institution->name ?? '' }} • {{ $shipment->created_at->diffForHumans() }}
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-{{ $shipment->status == 'delivered' ? 'success' : ($shipment->status == 'processing' ? 'warning' : 'info') }}">
                                @switch($shipment->status)
                                    @case('processing') قيد التوصيل @break
                                    @case('in_transit') في الطريق @break
                                    @case('delivered') تم التسليم @break
                                    @case('cancelled') ملغي @break
                                    @default {{ $shipment->status }}
                                @endswitch
                            </span>
                            @if(auth()->user()->permissions->where('name', 'view_financials')->count() > 0)
                            <div class="small text-muted mt-1">{{ number_format($shipment->price, 2) }} د.ل</div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="ti ti-package-off fs-1"></i>
                    <p>لا توجد شحنات حديثة</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- الشحنات التي تحتاج متابعة -->
@if($processingShipments->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning bg-opacity-10">
                <h5 class="card-title mb-0 text-warning">
                    <i class="ti ti-alert-triangle me-2"></i>
                    شحنات تحتاج متابعة (أكثر من يومين)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>الرقم الإشاري</th>
                                <th>المؤسسة</th>
                                <th>من</th>
                                <th>إلى</th>
                                <th>تاريخ الإنشاء</th>
                                <th>المدة</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($processingShipments as $shipment)
                            <tr>
                                <td>
                                    <strong>{{ $shipment->reference_number }}</strong>
                                </td>
                                <td>{{ $shipment->institution->name ?? '-' }}</td>
                                <td>{{ Str::limit($shipment->fromDepartment->name ?? '-', 20) }}</td>
                                <td>{{ Str::limit($shipment->toDepartment->name ?? '-', 20) }}</td>
                                <td>{{ $shipment->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <span class="badge bg-danger">{{ $shipment->created_at->diffForHumans() }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('shipments.show', $shipment) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // رسم الأداء الشهري
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyData = @json($monthlyPerformance);
    
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyData.map(item => item.month_ar),
            datasets: [{
                label: 'إجمالي الشحنات',
                data: monthlyData.map(item => item.shipments),
                borderColor: '#173a5e',
                backgroundColor: 'rgba(45, 90, 5, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'تم التسليم',
                data: monthlyData.map(item => item.delivered),
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    // رسم توزيع الحالات
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = @json($statusStats);
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['قيد التوصيل', 'في الطريق', 'تم التسليم', 'ملغي'],
            datasets: [{
                data: [
                    statusData.processing,
                    statusData.in_transit,
                    statusData.delivered,
                    statusData.cancelled
                ],
                backgroundColor: [
                    '#ffc107',
                    '#17a2b8',
                    '#28a745',
                    '#dc3545'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
    
    // تحديث الأرقام بتأثير العد
    animateNumbers();
});

// وظيفة تحديث لوحة التحكم
function refreshDashboard() {
    const refreshBtn = document.querySelector('.refresh-btn');
    refreshBtn.style.transform = 'rotate(360deg)';
    
    // محاكاة تحديث البيانات
    setTimeout(() => {
        location.reload();
    }, 1000);
}

// تأثير العد للأرقام
function animateNumbers() {
    const numbers = document.querySelectorAll('.stats-number');
    
    numbers.forEach(number => {
        const target = parseInt(number.textContent.replace(/,/g, ''));
        const increment = target / 50;
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            number.textContent = Math.floor(current).toLocaleString();
        }, 50);
    });
}

// تحديث البيانات كل 5 دقائق
setInterval(() => {
    fetch('{{ route("dashboard.stats") }}')
        .then(response => response.json())
        .then(data => {
            // تحديث الأرقام الأساسية
            document.getElementById('totalShipments').textContent = data.total_shipments.toLocaleString();
            // يمكن إضافة المزيد من التحديثات هنا
        })
        .catch(error => console.log('Error updating stats:', error));
}, 300000); // 5 دقائق
</script>
@endpush