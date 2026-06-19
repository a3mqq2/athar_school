<li class="pc-item {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
    <a href="{{ route('supervisor.dashboard') }}" class="pc-link">
        <span class="pc-micon">
            <svg class="pc-icon">
                <use xlink:href="#custom-status-up"></use>
            </svg>
        </span>
        <span class="pc-mtext">الصفحة الرئيسية</span>
    </a>
</li>


<li class="pc-item {{ request()->routeIs('supervisor.logs') ? 'active' : '' }}">
    <a href="{{ route('supervisor.logs') }}" class="pc-link">
        <span class="pc-micon">
            <svg class="pc-icon">
                <use xlink:href="#custom-document"></use>
            </svg>
        </span>
        <span class="pc-mtext"> سجلات الحضور </span>
    </a>
</li>

