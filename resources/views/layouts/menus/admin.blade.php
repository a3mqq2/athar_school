<li class="pc-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <a href="{{ route('admin.dashboard') }}" class="pc-link">
        <span class="pc-micon">
            <svg class="pc-icon">
                <use xlink:href="#custom-status-up"></use>
            </svg>
        </span>
        <span class="pc-mtext">الصفحة الرئيسية</span>
    </a>
</li>

@if(auth()->user()->hasPermissionNamed('manage_students'))
<li class="pc-item pc-hasmenu {{ request()->routeIs('admin.students.*') ? 'active pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><i class="ph-duotone ph-student"></i></span>
        <span class="pc-mtext">إدارة الطلاب</span>
        <span class="pc-arrow">
            <i class="fa fa-chevron-left"></i>
        </span>
    </a>
    <ul class="pc-submenu" style="{{ request()->routeIs('admin.students.*') ? 'display:block;' : 'display:none;' }}">
        <li class="pc-item {{ request()->routeIs('admin.students.index') ? 'active' : '' }}">
            <a href="{{ route('admin.students.index') }}" class="pc-link">
                <span class="pc-micon"><svg class="pc-icon"><use xlink:href="#custom-user-square"></use></svg></span>
                <span class="pc-mtext">جميع الطلاب</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.students.create') ? 'active' : '' }}">
            <a href="{{ route('admin.students.create') }}" class="pc-link">
                <span class="pc-micon"><svg class="pc-icon"><use xlink:href="#custom-add-item"></use></svg></span>
                <span class="pc-mtext">إضافة طالب</span>
            </a>
        </li>
        @php
            $sections = App\Models\Section::with(['stages.grades.classrooms'])->get();
        @endphp
        @foreach($sections as $section)
            <li class="pc-item pc-hasmenu">
                <a href="#!" class="pc-link">
                    <span class="pc-micon"><svg class="pc-icon"><use xlink:href="#custom-layer"></use></svg></span>
                    <span class="pc-mtext">{{ $section->type_name }}</span>
                    <span class="pc-arrow">
                        <i class="fa fa-chevron-left"></i>
                    </span>
                </a>
                <ul class="pc-submenu" style="display:none;">
                    @foreach($section->stages as $stage)
                        <li class="pc-item pc-hasmenu">
                            <a href="#!" class="pc-link">
                                <span class="pc-mtext">{{ $stage->name }}</span>
                                <span class="pc-arrow">
                                    <i class="fa fa-chevron-left"></i>
                                </span>
                            </a>
                            <ul class="pc-submenu" style="display:none;">
                                @foreach($stage->grades as $grade)
                                    <li class="pc-item pc-hasmenu">
                                        <a href="#!" class="pc-link">
                                            <span class="pc-mtext">{{ $grade->name }}</span>
                                            <span class="pc-arrow">
                                                <span class="pc-arrow">
                                                    <i class="fa fa-chevron-left"></i>
                                                </span>
                                            </span>
                                        </a>
                                        <ul class="pc-submenu" style="display:none;">
                                            @foreach($grade->classrooms as $classroom)
                                                <li class="pc-item">
                                                    <a href="{{ route('admin.students.index', [
                                                        'section_id' => $section->id,
                                                        'stage_id' => $stage->id,
                                                        'grade_id' => $grade->id,
                                                        'classroom_id' => $classroom->id
                                                    ]) }}" class="pc-link">
                                                        <span class="pc-mtext">{{ $classroom->name }}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endforeach
    </ul>
</li>
@endif

@if(auth()->user()->hasPermissionNamed('manage_users'))
<li class="pc-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
    <a href="{{ route('admin.users.index') }}" class="pc-link">
        <span class="pc-micon">
            <svg class="pc-icon">
                <use xlink:href="#custom-user-square"></use>
            </svg>
        </span>
        <span class="pc-mtext">المستخدمين</span>
    </a>
</li>
@endif



@if(auth()->user()->hasPermissionNamed('manage_settings'))
<li class="pc-item pc-hasmenu {{ request()->routeIs('admin.stages.*','admin.academic_years.*','admin.fees.catalog.*','admin.students.promotion.*','admin.installments-types.*') ? 'active pc-trigger' : '' }}">
    <a href="#!" class="pc-link">
        <span class="pc-micon"><svg class="pc-icon"><use xlink:href="#custom-level"></use></svg></span>
        <span class="pc-mtext">الاعدادات</span>
        <span class="pc-arrow">
            <i class="fa fa-chevron-left"></i>
        </span>
    </a>
    <ul class="pc-submenu" style="{{ request()->routeIs('admin.stages.*','admin.academic_years.*','admin.fees.catalog.*','admin.students.promotion.*','admin.installments-types.*') ? 'display:block;' : 'display:none;' }}">
        <li class="pc-item {{ request()->routeIs('admin.students.promotion.*') ? 'active' : '' }}">
            <a href="{{ route('admin.students.promotion.index') }}" class="pc-link">
                <span class="pc-micon"><svg class="pc-icon"><use xlink:href="#custom-data"></use></svg></span>
                <span class="pc-mtext">ترحيل الطلاب</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.stages.*') ? 'active' : '' }}">
            <a href="{{ route('admin.stages.index') }}" class="pc-link">
                <span class="pc-micon"><svg class="pc-icon"><use xlink:href="#custom-setting-2"></use></svg></span>
                <span class="pc-mtext">إعدادات المراحل</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.academic_years.*') ? 'active' : '' }}">
            <a href="{{ route('admin.academic_years.index') }}" class="pc-link">
                <span class="pc-micon"><svg class="pc-icon"><use xlink:href="#custom-calendar-1"></use></svg></span>
                <span class="pc-mtext">السنوات الدراسية</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.fees.catalog.*') ? 'active' : '' }}">
            <a href="{{ route('admin.fees.catalog.index') }}" class="pc-link">
                <span class="pc-micon"><svg class="pc-icon"><use xlink:href="#custom-dollar-circle"></use></svg></span>
                <span class="pc-mtext">تسعيرات الرسوم</span>
            </a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.installments-types.*') ? 'active' : '' }}">
            <a href="{{ route('admin.installments-types.index') }}" class="pc-link">
                <span class="pc-micon"><i class="ph-duotone ph-credit-card"></i></span>
                <span class="pc-mtext">انواع الاقساط</span>
            </a>
        </li>
    </ul>
</li>
@endif
