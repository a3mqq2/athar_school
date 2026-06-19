{{-- resources/views/admin/students/show.blade.php --}}
@extends('layouts.app')

@section('title', 'تفاصيل الطالب')

@push('styles')
@include('partials.page-styles')
<style>
    .student-profile{background:linear-gradient(135deg,#ab9923 0%,#e9cc0f 100%);color:#fff;border-radius:14px;padding:20px;margin-bottom:18px}
    .student-avatar{width:80px;height:80px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem}
    .info-card{border:1px solid #e9ecef;border-radius:10px;overflow:hidden}
    .info-card-header{background:#f8f9fa;padding:12px 16px;border-bottom:1px solid #e9ecef;font-weight:600;color:#495057}
    .info-card-body{padding:16px}
    .info-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f1f3f4}
    .info-row:last-child{border-bottom:none}
    .info-label{font-weight:600;color:#6c757d;min-width:120px}
    .info-value{color:#212529}
    .enrollment-card{border:2px solid #e9ecef;border-radius:10px;margin-bottom:12px;transition:all .2s}
    .enrollment-card.current{border-color:#28a745;box-shadow:0 0 0 .2rem rgba(40,167,69,.15)}
    .enrollment-year{background:#f8f9fa;padding:10px 15px;border-bottom:1px solid #e9ecef;font-weight:600}
    .enrollment-year.current{background:#d4edda;color:#155724}
    .badge-soft{border:1px solid rgba(0,0,0,.08);background:#fff}
    .nav-tabs .nav-link{font-weight:600}
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4">



    {{-- Header/Profile --}}
    <div class="student-profile d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <div class="student-avatar">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div>
                <h4 class="mb-1">{{ $student->name }}</h4>
                <div class="small" style="opacity:.9">
                    <i class="fas fa-venus-mars me-1"></i>
                    {{ $student->gender === 'male' ? 'ذكر' : 'أنثى' }}
                    <span class="mx-2">•</span>
                    <i class="fas fa-user me-1"></i> ولي الأمر: {{ $student->parent_name }}
                </div>
            </div>
        </div>
        <div class="text-end">
            <span class="badge badge-dark">
                <i class="far fa-clock me-1"></i>
                أنشئ {{ $student->created_at->format('Y-m-d H:i') }}
            </span>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-3" id="studentTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#tab-personal" type="button" role="tab" aria-controls="tab-personal" aria-selected="true">
                <i class="fas fa-id-card me-1"></i> البيانات الشخصية
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="academic-tab" data-bs-toggle="tab" data-bs-target="#tab-academic" type="button" role="tab" aria-controls="tab-academic" aria-selected="false">
                <i class="fas fa-graduation-cap me-1"></i> التسجيل الأكاديمي
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="fees-tab" data-bs-toggle="tab" data-bs-target="#tab-fees" type="button" role="tab" aria-controls="tab-fees" aria-selected="false">
                <i class="fas fa-money-bill-wave me-1"></i> الرسوم والأقساط
            </button>
        </li>
    </ul>

    <div class="tab-content" id="studentTabsContent">
        {{-- Personal Tab --}}
        <div class="tab-pane fade show active" id="tab-personal" role="tabpanel" aria-labelledby="personal-tab" tabindex="0">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-user me-2"></i>البيانات الشخصية
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label">الاسم الكامل:</span>
                        <span class="info-value">{{ $student->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">الرقم الوطني:</span>
                        <span class="info-value">{{ $student->national_id ?: 'غير محدد' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">رقم القيد:</span>
                        <span class="info-value">{{ $student->registration_number ?: 'غير محدد' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">الجنسية:</span>
                        <span class="info-value">{{ $student->nationality ?: 'غير محدد' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">اسم ولي الأمر:</span>
                        <span class="info-value">{{ $student->parent_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">رقم الهاتف:</span>
                        <span class="info-value">{{ $student->phone ?: 'غير محدد' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">العنوان:</span>
                        <span class="info-value">{{ $student->address ?: 'غير محدد' }}</span>
                    </div>
                    @if($student->notes)
                        <div class="info-row">
                            <span class="info-label">ملاحظات:</span>
                            <span class="info-value">{{ $student->notes }}</span>
                        </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">تاريخ الإنشاء:</span>
                        <span class="info-value">{{ $student->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Academic Tab --}}
        <div class="tab-pane fade" id="tab-academic" role="tabpanel" aria-labelledby="academic-tab" tabindex="0">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-graduation-cap me-2"></i>تاريخ التسجيل الأكاديمي
                </div>
                <div class="info-card-body">
                    @if($student->enrollments->count() > 0)
                        @foreach($student->enrollments->sortByDesc(fn($e)=>$e->academicYear->is_current) as $enrollment)
                            <div class="enrollment-card {{ $enrollment->academicYear->is_current ? 'current' : '' }}">
                                <div class="enrollment-year {{ $enrollment->academicYear->is_current ? 'current' : '' }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="fas fa-calendar me-2"></i>
                                            {{ $enrollment->academicYear->name }}
                                        </span>
                                        @if($enrollment->academicYear->is_current)
                                            <span class="badge bg-success">الحالية</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="p-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-row">
                                                <span class="info-label">القسم:</span>
                                                <span class="info-value">
                                                    @php $secType = $enrollment->stage?->section?->type ?? $enrollment->stage?->sectionObj?->type; @endphp
                                                    {{ $secType === 'local' ? 'محلي' : ($secType === 'international' ? 'دولي' : 'غير محدد') }}
                                                </span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">المرحلة:</span>
                                                <span class="info-value">{{ $enrollment->stage->name ?? 'غير محدد' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-row">
                                                <span class="info-label">الصف:</span>
                                                <span class="info-value">{{ $enrollment->grade->name ?? 'غير محدد' }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">الفصل:</span>
                                                <span class="info-value">{{ $enrollment->classroom->name ?? 'غير محدد' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">لا يوجد تسجيل أكاديمي لهذا الطالب</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Fees Tab --}}
        <div class="tab-pane fade" id="tab-fees" role="tabpanel" aria-labelledby="fees-tab" tabindex="0">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-money-bill-wave me-2"></i>ملخص الرسوم والأقساط
                </div>
                <div class="info-card-body">
                    @php
                        $totalDue = $student->installments->sum('amount_due');
                        $totalPaid = $student->installments->sum('paid_amount');
                        $totalRemaining = $totalDue - $totalPaid;
                        $unpaidCount = $student->installments->where('status', '!=', 'paid')->count();
                        $overdueCount = $student->installments->where('status', 'overdue')->count();
                        $installmentsByKind = $student->installments->groupBy('kind');
                    @endphp

                    <div class="row text-center mb-4">
                        <div class="col-md-3">
                            <div class="border rounded p-3 mb-2">
                                <div class="fw-bold text-primary fs-4">{{ number_format($totalDue, 2) }}</div>
                                <div class="text-muted small">إجمالي الرسوم</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 mb-2">
                                <div class="fw-bold text-success fs-4">{{ number_format($totalPaid, 2) }}</div>
                                <div class="text-muted small">المدفوع</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 mb-2">
                                <div class="fw-bold text-warning fs-4">{{ number_format($totalRemaining, 2) }}</div>
                                <div class="text-muted small">المتبقي</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 mb-2">
                                <div class="fw-bold text-danger fs-4">{{ $overdueCount }}</div>
                                <div class="text-muted small">أقساط متأخرة</div>
                            </div>
                        </div>
                    </div>

                    @if($installmentsByKind->count() > 0)
                        <h6 class="mb-3">تفاصيل الأقساط حسب النوع:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>نوع القسط</th>
                                        <th>العدد</th>
                                        <th>إجمالي المبلغ</th>
                                        <th>المدفوع</th>
                                        <th>المتبقي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($installmentsByKind as $kind => $installments)
                                        @php
                                            $kindDue = $installments->sum('amount_due');
                                            $kindPaid = $installments->sum('paid_amount');
                                            $kindRemaining = $kindDue - $kindPaid;
                                            $kindOverdue = $installments->where('status', 'overdue')->count();
                                            $kindNames = [
                                                'tuition' => 'رسوم دراسية',
                                                'transport' => 'رسوم نقل',
                                                'books' => 'رسوم كتب',
                                                'activities' => 'رسوم أنشطة',
                                                'uniform' => 'رسوم زي مدرسي',
                                                'meals' => 'رسوم وجبات',
                                                'other' => 'أخرى'
                                            ];
                                        @endphp
                                        <tr>
                                            <td>{{ $kindNames[$kind] ?? 'غير محدد' }}</td>
                                            <td>{{ $installments->count() }}</td>
                                            <td>{{ number_format($kindDue, 2) }}</td>
                                            <td>{{ number_format($kindPaid, 2) }}</td>
                                            <td>{{ number_format($kindRemaining, 2) }}</td>
                                          
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if($unpaidCount > 0)
                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                    يوجد {{ $unpaidCount }} قسط غير مدفوع
                                    @if($overdueCount > 0)
                                        ({{ $overdueCount }} متأخر)
                                    @endif
                                </span>
                                <a href="#installmentsDetail" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse">
                                    عرض التفاصيل
                                </a>
                            </div>
                        </div>

                        <div class="collapse mt-3" id="installmentsDetail">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">تفاصيل الأقساط غير المدفوعة</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>النوع</th>
                                                    <th>المبلغ</th>
                                                    <th>المدفوع</th>
                                                    <th>المتبقي</th>
                                                    <th>تاريخ الاستحقاق</th>
                                                    <th>الحالة</th>
                                                    <th>المرجع</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($student->installments->where('status', '!=', 'paid') as $installment)
                                                    <tr>
                                                        <td>{{ $installment->installmentType?->name}}</td>
                                                        <td>{{ number_format($installment->amount_due, 2) }}</td>
                                                        <td>{{ number_format($installment->paid_amount, 2) }}</td>
                                                        <td>{{ number_format($installment->remaining ?? ($installment->amount_due - $installment->paid_amount), 2) }}</td>
                                                        <td>
                                                            @if($installment->due_date)
                                                                {{ \Illuminate\Support\Carbon::parse($installment->due_date)->format('Y-m-d') }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @php
                                                                $st = $installment->status;
                                                                $stName = $installment->status_name ?? ($st === 'overdue' ? 'متأخر' : ($st === 'partial' ? 'جزئي' : ($st === 'paid' ? 'مدفوع' : 'قيد الانتظار')));
                                                                $stClass = $st === 'overdue' ? 'danger' : ($st === 'partial' ? 'warning' : ($st === 'paid' ? 'success' : 'primary'));
                                                            @endphp
                                                            <span class="badge bg-{{ $stClass }}">{{ $stName }}</span>
                                                        </td>
                                                        <td>{{ $installment->reference ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-3 p-3 bg-success bg-opacity-10 rounded text-center text-success">
                            <i class="fas fa-check-circle me-2"></i> جميع الأقساط مدفوعة
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // تحسين بسيط لبطاقات التسجيل
    document.querySelectorAll('.enrollment-card').forEach(function(card){
        card.addEventListener('mouseenter', ()=> card.style.transform='translateY(-2px)');
        card.addEventListener('mouseleave', ()=> card.style.transform='translateY(0)');
    });
});
</script>
@endpush
