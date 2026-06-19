<li class="pc-item {{ request()->routeIs('finance.dashboard') ? 'active' : '' }}">
    <a href="{{ route('finance.dashboard') }}" class="pc-link">
        <span class="pc-micon">
            <svg class="pc-icon">
                <use xlink:href="#custom-status-up"></use>
            </svg>
        </span>
        <span class="pc-mtext">الصفحة الرئيسية</span>
    </a>
</li>

@if(auth()->user()->hasPermissionNamed('student_installments'))
<li class="pc-item {{ request()->routeIs('finance.students.*') ? 'active' : '' }}">
    <a href="{{ route('finance.students.index') }}" class="pc-link">
        <span class="pc-micon">
            <svg class="pc-icon"><use xlink:href="#custom-user"></use></svg>
        </span>
        <span class="pc-mtext">أقساط الطلاب</span>
    </a>
</li>
@endif

@if(auth()->user()->hasPermissionNamed('salaries'))
<li class="pc-item {{ request()->routeIs('finance.employee-balances.*') ? 'active' : '' }}">
    <a href="{{ route('finance.employee-balances.index') }}" class="pc-link">
        <span class="pc-micon"><i class="fas fa-users-cog"></i></span>
        <span class="pc-mtext">الحسابات المالية</span>
    </a>
</li>
@endif

@if(auth()->user()->hasPermissionNamed('teacher_settlements'))
<li class="pc-item {{ request()->routeIs('finance.teacher-settlements.*') ? 'active' : '' }}">
    <a href="{{ route('finance.teacher-settlements.list') }}" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-chalkboard-teacher"></i></span>
        <span class="pc-mtext">تسوية المعلمين</span>
    </a>
</li>
@endif

@if(auth()->user()->hasPermissionNamed('salaries'))
<li class="pc-item {{ request()->routeIs('finance.payrolls.*') ? 'active' : '' }}">
    <a href="{{ route('finance.payrolls.list') }}" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-briefcase"></i></span>
        <span class="pc-mtext">الرواتب</span>
    </a>
</li>
@endif

@if(auth()->user()->hasPermissionNamed('manage_financial_vaults'))
<li class="pc-item {{ request()->routeIs('finance.treasuries.*') ? 'active' : '' }}">
    <a href="{{ route('finance.treasuries.index') }}" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-vault"></i></span>
        <span class="pc-mtext">الخزائن المالية</span>
    </a>
</li>
@endif

@if(auth()->user()->hasPermissionNamed('financial_transactions'))
<li class="pc-item {{ request()->routeIs('finance.transactions.*') ? 'active' : '' }}">
    <a href="{{ route('finance.transactions.index') }}" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-swap"></i></span>
        <span class="pc-mtext">المعاملات المالية</span>
    </a>
</li>
@endif

@if(auth()->user()->hasPermissionNamed('vault_transfers'))
<li class="pc-item {{ request()->routeIs('finance.treasury-transfers.*') ? 'active' : '' }}">
    <a href="{{ route('finance.treasury-transfers.index') }}" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-arrows-left-right"></i></span>
        <span class="pc-mtext">تحويلات الخزائن</span>
    </a>
</li>
@endif

@if(auth()->user()->hasPermissionNamed('financial_reports'))
<li class="pc-item {{ request()->routeIs('finance.reports.*') ? 'active' : '' }}">
    <a href="{{ route('finance.reports.index') }}" class="pc-link">
        <span class="pc-micon"><svg class="pc-icon"><use xlink:href="#custom-document"></use></svg></span>
        <span class="pc-mtext">تقارير المالية</span>
    </a>
</li>
@endif
