{{-- resources/views/finance/reports/index.blade.php --}}
@extends('layouts.app')

@section('title', 'التقارير المالية')

@push('styles')
<style>
    :root {
        --primary: #925419;
        --primary-light: rgba(146, 84, 25, 0.1);
        --success: #28a745;
        --info: #17a2b8;
        --warning: #ffc107;
        --danger: #dc3545;
        --light: #f8f9fa;
        --border: #e1e5eb;
        --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        --shadow-hover: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .reports-container {
        padding: 2rem 0;
    }

    .page-header {
        text-align: center;
        margin-bottom: 3rem;
        color: var(--primary);
    }

    .page-header h2 {
        font-weight: 800;
        margin-bottom: 0.5rem;
    }

    .page-subtitle {
        color: #6c757d;
        font-size: 1.1rem;
    }

    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 3rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .report-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
        position: relative;
    }

    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .report-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--primary-light));
    }

    .card-header {
        padding: 1.5rem;
        background: var(--light);
        border-bottom: 1px solid var(--border);
        position: relative;
    }

    .card-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        font-size: 1.5rem;
        color: white;
    }

    .treasury-icon { background: linear-gradient(135deg, var(--info), #0d7377); }
    .employee-icon { background: linear-gradient(135deg, var(--primary), #6b3410); }
    .teacher-icon { background: linear-gradient(135deg, var(--success), #1e7e34); }
    .student-icon { background: linear-gradient(135deg, var(--warning), #d39e00); }

    .card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary);
        margin: 0;
    }

    .card-description {
        color: #6c757d;
        font-size: 0.9rem;
        margin: 0.5rem 0 0 0;
    }

    .card-body {
        padding: 1.5rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .form-row-full {
        grid-column: 1 / -1;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-control, .form-select {
        border: 1px solid #ced4da;
        border-radius: 8px;
        padding: 0.75rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        width: 100%;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(146, 84, 25, 0.25);
        outline: 0;
    }

    .btn-group {
        display: flex;
        gap: 0.5rem;
        margin-top: 1.5rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        flex: 1;
        text-align: center;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary), #6b3410);
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #6b3410, var(--primary));
        transform: translateY(-1px);
    }

    .btn-outline {
        background: transparent;
        color: var(--primary);
        border: 2px solid var(--primary);
    }

    .btn-outline:hover {
        background: var(--primary);
        color: white;
    }

    .stats-bar {
        background: var(--primary-light);
        padding: 1rem;
        margin-top: 1rem;
        border-radius: 8px;
        text-align: center;
        border-left: 4px solid var(--primary);
    }

    .stats-text {
        color: var(--primary);
        font-size: 0.9rem;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .reports-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
            padding: 0 1rem;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }

        .btn-group {
            flex-direction: column;
        }
    }

    /* Animation */
    .report-card {
        animation: fadeInUp 0.6s ease-out;
    }

    .report-card:nth-child(1) { animation-delay: 0.1s; }
    .report-card:nth-child(2) { animation-delay: 0.2s; }
    .report-card:nth-child(3) { animation-delay: 0.3s; }
    .report-card:nth-child(4) { animation-delay: 0.4s; }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush

@section('content')
<div class="container reports-container">
    <div class="page-header">
        <h2>التقارير المالية</h2>
    </div>

    <div class="reports-grid">
        {{-- كشف حساب خزينة --}}
        <div class="report-card">
            <div class="card-header">
                <div class="card-icon treasury-icon">
                    <i class="fa fa-university"></i>
                </div>
                <h3 class="card-title">كشف حساب خزينة</h3>
                <p class="card-description">عرض جميع حركات الإيداع والسحب للخزائن خلال فترة زمنية محددة</p>
            </div>
            <div class="card-body">
                <form method="get" action="{{ route('finance.reports.treasury') }}">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="from" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="to" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">اختر الخزينة</label>
                        <select name="treasury_id" class="form-select">
                            <option value="">جميع الخزائن</option>
                            @foreach($treasuries as $treasury)
                                <option value="{{ $treasury->id }}">{{ $treasury->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">نوع الحركة</label>
                        <select name="transaction_type_id" id="" class="form-control">
                            <option value="">الكل</option>
                            @foreach ($transaction_types as $transaction_type)
                                <option value="{{$transaction_type->id}}">{{$transaction_type->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="stats-bar">
                        <div class="stats-text">عدد الخزائن النشطة: {{ $treasuries->count() }}</div>
                    </div>
                    <div class="btn-group">
                        <button type="submit" name="format" value="view" class="btn btn-primary">
                            <i class="fa fa-eye me-2"></i>عرض التقرير
                        </button>
                        <button type="submit" name="format" value="print" class="btn btn-outline">
                            <i class="fa fa-print me-2"></i>طباعة
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- كشف حساب الموظف --}}
        <div class="report-card">
            <div class="card-header">
                <div class="card-icon employee-icon">
                    <i class="fa fa-user-tie"></i>
                </div>
                <h3 class="card-title">كشف حساب الموظف</h3>
                <p class="card-description">تتبع المرتبات والبدلات والخصومات لموظف محدد</p>
            </div>
            <div class="card-body">
                <form method="get" action="{{ route('finance.reports.employee') }}">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="from" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="to" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">اختر الموظف</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">اختر موظف...</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="stats-bar">
                        <div class="stats-text">عدد الموظفين: {{ $employees->count() }}</div>
                    </div>
                    <div class="btn-group">
                        <button type="submit" name="format" value="view" class="btn btn-primary">
                            <i class="fa fa-eye me-2"></i>عرض التقرير
                        </button>
                        <button type="submit" name="format" value="print" class="btn btn-outline">
                            <i class="fa fa-print me-2"></i>طباعة
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- تقرير تسوية المعلمين --}}
        <div class="report-card">
            <div class="card-header">
                <div class="card-icon teacher-icon">
                    <i class="fa fa-chalkboard-teacher"></i>
                </div>
                <h3 class="card-title">تقرير تسوية المعلمين</h3>
                <p class="card-description">متابعة حساب الحصص المحسوبة والمدفوعة للمعلمين</p>
            </div>
            <div class="card-body">
                <form method="get" action="{{ route('finance.reports.teachers') }}">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="from" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="to" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">المعلم</label>
                            <select name="teacher_id" class="form-select">
                                <option value="">جميع المعلمين</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">حالة التسوية</label>
                            <select name="status" class="form-select">
                                <option value="">الكل</option>
                                <option value="pending">مستحق</option>
                                <option value="settled">مسدد</option>
                            </select>
                        </div>
                    </div>
                    <div class="stats-bar">
                        <div class="stats-text">عدد المعلمين: {{ $teachers->count() }}</div>
                    </div>
                    <div class="btn-group">
                        <button type="submit" name="format" value="view" class="btn btn-primary">
                            <i class="fa fa-eye me-2"></i>عرض التقرير
                        </button>
                        <button type="submit" name="format" value="print" class="btn btn-outline">
                            <i class="fa fa-print me-2"></i>طباعة
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- مدفوعات الطلاب --}}
        <div class="report-card">
            <div class="card-header">
                <div class="card-icon student-icon">
                    <i class="fa fa-graduation-cap"></i>
                </div>
                <h3 class="card-title">مدفوعات الطلاب</h3>
                <p class="card-description">تتبع جميع مدفوعات الأقساط المدرسية للطلاب</p>
            </div>
            <div class="card-body">
                <form method="get" action="{{ route('finance.reports.students') }}">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="from" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="to" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">طريقة الدفع</label>
                            <select name="payment_method" class="form-select">
                                <option value="">جميع الطرق</option>
                                @foreach($paymentMethods as $key => $method)
                                    <option value="{{ $key }}">{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">الخزينة</label>
                            <select name="treasury_id" class="form-select">
                                <option value="">جميع الخزائن</option>
                                @foreach($treasuries as $treasury)
                                    <option value="{{ $treasury->id }}">{{ $treasury->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">بحث بالطالب</label>
                        <input type="text" name="student_search" class="form-control" placeholder="اسم الطالب...">
                    </div>
                    <div class="stats-bar">
                        <div class="stats-text">إجمالي المدفوعات اليوم: {{ number_format($todayPayments, 2) }} د.ل</div>
                    </div>
                    <div class="btn-group">
                        <button type="submit" name="format" value="view" class="btn btn-primary">
                            <i class="fa fa-eye me-2"></i>عرض التقرير
                        </button>
                        <button type="submit" name="format" value="print" class="btn btn-outline">
                            <i class="fa fa-print me-2"></i>طباعة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection