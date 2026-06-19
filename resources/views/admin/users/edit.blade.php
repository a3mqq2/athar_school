{{-- resources/views/admin/users/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'تعديل مستخدم')
@push('styles')
@include('partials.page-styles')

<style>
/* Custom Checkbox Styles */
.checkbox-group {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    background-color: #f8f9fa;
}

.permissions-container {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background-color: #f8f9fa;
    padding: 1rem;
    max-height: 500px;
    overflow-y: auto;
}

.role-group {
    margin-bottom: 1.5rem;
    border: 1px solid #e3f2fd;
    border-radius: 0.5rem;
    overflow: hidden;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.role-group.active {
    border-color: #007bff;
    box-shadow: 0 4px 8px rgba(0,123,255,0.2);
}

.role-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.role-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.role-header:hover::before {
    left: 100%;
}

.role-header.inactive {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    opacity: 0.7;
}

.role-header.teacher {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
}

.role-header.admin {
    background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
}

.role-header.finance {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    color: #212529;
}

.role-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    font-size: 1.1rem;
}

.role-badge {
    background-color: rgba(255,255,255,0.2);
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 500;
}

.permissions-list {
    padding: 1rem;
    display: none;
    background-color: #fafbfc;
    border-top: 1px solid rgba(0,0,0,0.1);
}

.permissions-list.show {
    display: block;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
        padding: 0 1rem;
    }
    to {
        opacity: 1;
        max-height: 500px;
        padding: 1rem;
    }
}

.checkbox-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
    background-color: #fff;
    border: 1px solid #e9ecef;
}

.checkbox-item:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.15);
    transform: translateY(-1px);
}

.checkbox-item.checked {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border-color: #28a745;
    color: #155724;
}

.custom-checkbox {
    position: relative;
    margin-left: 1rem;
    cursor: pointer;
}

.custom-checkbox input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkmark {
    position: relative;
    top: 0;
    left: 0;
    height: 22px;
    width: 22px;
    background-color: #fff;
    border: 2px solid #ced4da;
    border-radius: 6px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.custom-checkbox:hover .checkmark {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.custom-checkbox input:checked ~ .checkmark {
    background-color: #007bff;
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.3);
}

.checkmark:after {
    content: "";
    position: absolute;
    display: none;
    left: 7px;
    top: 3px;
    width: 6px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.custom-checkbox input:checked ~ .checkmark:after {
    display: block;
    animation: checkAnimation 0.3s ease-in-out;
}

.checkbox-label {
    flex: 1;
    font-size: 0.95rem;
    font-weight: 500;
    color: #495057;
    cursor: pointer;
    user-select: none;
}

.checkbox-item.checked .checkbox-label {
    color: #155724;
    font-weight: 600;
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #dee2e6;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #495057;
    margin: 0;
}

.select-all-btn {
    font-size: 0.85rem;
    padding: 0.25rem 0.75rem;
    border: none;
    border-radius: 0.25rem;
    background-color: #6c757d;
    color: white;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.select-all-btn:hover {
    background-color: #5a6268;
}

.counter {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
}

.role-section {
    margin-bottom: 2rem;
}

.permission-section {
    margin-bottom: 1rem;
}

.collapse-icon {
    transition: transform 0.3s ease;
}

.collapse-icon.rotated {
    transform: rotate(180deg);
}

/* Search box styles */
.search-box {
    position: relative;
    margin-bottom: 1rem;
}

.search-box input {
    padding-right: 2.5rem;
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
}

.search-box .search-icon {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

/* Animation for checkbox check */
@keyframes checkAnimation {
    0% { transform: scale(0) rotate(45deg); }
    50% { transform: scale(1.2) rotate(45deg); }
    100% { transform: scale(1) rotate(45deg); }
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
    font-style: italic;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .permissions-container {
        max-height: 400px;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .role-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <h3 class="page-header">تعديل مستخدم: {{ $user->name }}</h3>

    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="form-card" id="user-form">
        @csrf
        @method('PUT')

        <div class="form-card-header">
            <h5 class="form-card-title"><i class="fa fa-user-edit"></i> البيانات الأساسية</h5>
        </div>

        <div class="form-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label"><span class="required">*</span> الاسم</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label"><span class="required">*</span> الكود </label>
                    <input type="text" name="code" class="form-control" value="{{ old('code', $user->code) }}" required>
                </div>


                <div class="col-md-4">
                    <label class="form-label"><span class="required">*</span> البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">رقم الهاتف</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">كلمة المرور (اتركها فارغة بدون تغيير)</label>
                    <input type="password" name="password" class="form-control" minlength="6">
                </div>

                <div class="col-md-3">
                    <label class="form-label">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation" class="form-control" minlength="6">
                </div>

                <div class="col-md-4">
                    <label class="form-label">تاريخ التعيين</label>
                    <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date', optional($user->hire_date)->format('Y-m-d')) }}">
                </div>

                <!-- الأدوار والصلاحيات -->
                <div class="col-12">
                    <div class="role-section">
                        <div class="section-header">
                            <h6 class="section-title">
                                <i class="fa fa-users me-2"></i>
                                الأدوار والصلاحيات <span class="required">*</span>
                            </h6>
                            <div class="d-flex align-items-center gap-2">
                                <span class="counter" id="total-counter">0 دور، 0 صلاحية</span>
                                <button type="button" class="select-all-btn" id="expand-all">توسيع الكل</button>
                            </div>
                        </div>
                        
                        <div class="search-box">
                            <input type="text" class="form-control" id="roles-permissions-search" placeholder="البحث في الأدوار والصلاحيات...">
                            <i class="fa fa-search search-icon"></i>
                        </div>
                        
                        <div class="permissions-container" id="roles-permissions-container">
                            @php 
                                $userRoles = $user->roles?->pluck('name')->toArray() ?? [];
                                $oldRoles = (array) old('role', $userRoles);
                                $oldPerms = (array) old('permissions', $userDirectPermissions ?? []);
                            @endphp
                            
                            {{-- Loop through each role --}}
                            @foreach($roles as $role)
                                @php
                                    $isRoleSelected = in_array($role->name, $oldRoles);
                                    $roleClass = strtolower($role->name);
                                @endphp
                                
                                <div class="role-group {{ $isRoleSelected ? 'active' : '' }}" 
                                     data-role="{{ $role->name }}"
                                     data-search="{{ strtolower($role->display_name ?? $role->name) }}">
                                    
                                    <div class="role-header {{ $isRoleSelected ? $roleClass : 'inactive' }}" 
                                         data-bs-toggle="collapse" 
                                         data-bs-target="#permissions-{{ $role->id }}">
                                        <div class="role-title">
                                            <label class="custom-checkbox mb-0">
                                                <input type="checkbox" 
                                                       name="role[]" 
                                                       value="{{ $role->name }}"
                                                       {{ $isRoleSelected ? 'checked' : '' }}
                                                       onclick="event.stopPropagation();">
                                                <span class="checkmark"></span>
                                            </label>
                                            <i class="fa fa-{{ $roleClass == 'teacher' ? 'chalkboard-teacher' : ($roleClass == 'admin' ? 'user-shield' : 'calculator') }}"></i>
                                            <span>{{ $role->display_name ?? $role->name }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="role-badge" id="badge-{{ $role->name }}">
                                                @if($role->permissions)
                                                    {{ $role->permissions->count() }} صلاحية
                                                @else
                                                    0 صلاحية
                                                @endif
                                            </span>
                                            <i class="fa fa-chevron-down collapse-icon"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="permissions-list {{ $isRoleSelected ? 'show' : '' }}" 
                                         id="permissions-{{ $role->id }}">
                                        @if($role->permissions && $role->permissions->count() > 0)
                                            {{-- Loop through each permission of this role --}}
                                            @foreach($role->permissions as $permission)
                                                @php $isPermSelected = in_array($permission->name, $oldPerms); @endphp
                                                <div class="checkbox-item {{ $isPermSelected ? 'checked' : '' }}"
                                                     data-permission="{{ $permission->name }}"
                                                     data-search="{{ strtolower($permission->display_name ?? $permission->name) }}">
                                                    <label class="custom-checkbox">
                                                        <input type="checkbox" 
                                                               name="permissions[]" 
                                                               value="{{ $permission->name }}"
                                                               data-role="{{ $role->name }}"
                                                               {{ $isPermSelected ? 'checked' : '' }}>
                                                        <span class="checkmark"></span>
                                                    </label>
                                                    <span class="checkbox-label">{{ $permission->display_name ?? $permission->name }}</span>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="empty-state">
                                                <i class="fa fa-info-circle"></i>
                                                لا توجد صلاحيات محددة لهذا الدور
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            
                            {{-- Additional permissions not assigned to any role --}}
                            @php
                                $assignedPermissionIds = collect();
                                foreach($roles as $role) {
                                    if($role->permissions) {
                                        $assignedPermissionIds = $assignedPermissionIds->merge($role->permissions->pluck('id'));
                                    }
                                }
                                $unassignedPermissions = $permissions->whereNotIn('id', $assignedPermissionIds->unique());
                            @endphp
                            
                            @if($unassignedPermissions->count() > 0)
                                <div class="role-group" data-role="other" data-search="صلاحيات إضافية أخرى">
                                    <div class="role-header" style="background: linear-gradient(135deg, #6f42c1 0%, #5a2d91 100%);"
                                         data-bs-toggle="collapse" 
                                         data-bs-target="#permissions-other">
                                        <div class="role-title">
                                            <i class="fa fa-plus-circle"></i>
                                            <span>صلاحيات إضافية</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="role-badge">{{ $unassignedPermissions->count() }} صلاحية</span>
                                            <i class="fa fa-chevron-down collapse-icon"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="permissions-list" id="permissions-other">
                                        @foreach($unassignedPermissions as $permission)
                                            @php $isPermSelected = in_array($permission->name, $oldPerms); @endphp
                                            <div class="checkbox-item {{ $isPermSelected ? 'checked' : '' }}"
                                                 data-permission="{{ $permission->name }}"
                                                 data-search="{{ strtolower($permission->display_name ?? $permission->name) }}">
                                                <label class="custom-checkbox">
                                                    <input type="checkbox" 
                                                           name="permissions[]" 
                                                           value="{{ $permission->name }}"
                                                           data-role="other"
                                                           {{ $isPermSelected ? 'checked' : '' }}>
                                                    <span class="checkmark"></span>
                                                </label>
                                                <span class="checkbox-label">{{ $permission->display_name ?? $permission->name }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="form-text mt-2">اختر الأدوار المطلوبة وستظهر صلاحياتها تلقائياً. يمكن تعديل الصلاحيات يدوياً.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card-header">
            <h5 class="form-card-title"><i class="fa fa-briefcase"></i> بيانات حسب الدور</h5>
        </div>

        <div class="form-card-body">
            <div class="row g-3">
                <div class="col-md-6 teacher-only" style="display:none">
                    <label class="form-label"><span class="required teacher-required-marker" style="display:none">*</span> التخصص/المادة</label>
                    <input type="text" name="subject" id="subject" class="form-control" value="{{ old('subject', $user->subject) }}">
                </div>

                <div class="col-md-6 teacher-only" style="display:none">
                    <label class="form-label"><span class="required teacher-required-marker" style="display:none">*</span> سعر الحصة</label>
                    <input type="number" step="0.01" min="0" name="session_price" id="session_price" class="form-control" value="{{ old('session_price', $user->session_price) }}">
                </div>

                <div class="col-md-6 staff-only" style="display:none">
                    <label class="form-label"><span class="required staff-required-marker" style="display:none">*</span> الراتب</label>
                    <input type="number" step="0.01" min="0" name="salary" id="salary" class="form-control" value="{{ old('salary', $user->salary) }}">
                </div>

                <div class="col-md-6 staff-only" style="display:none">
                    <label class="form-label"><span class="required staff-required-marker" style="display:none">*</span> المسمى الوظيفي</label>
                    <input type="text" name="job_title" id="job_title" class="form-control" value="{{ old('job_title', $user->job_title) }}">
                </div>
            </div>
        </div>

        <div class="form-card-footer d-flex justify-content-between align-items-center">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">رجوع</a>
            <button type="submit" class="btn btn-primary">تحديث</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
$(function () {
    const initialManualPerms = @json(old('permissions', $userDirectPermissions ?? []));

    // تحديث العدادات
    function updateCounters() {
        const selectedRoles = $('input[name="role[]"]:checked').length;
        const selectedPermissions = $('input[name="permissions[]"]:checked').length;
        $('#total-counter').text(`${selectedRoles} دور، ${selectedPermissions} صلاحية`);
    }

    // التعامل مع تغيير الأدوار
    function handleRoleChange() {
        $(document).on('change', 'input[name="role[]"]', function() {
            const roleName = $(this).val();
            const roleGroup = $(this).closest('.role-group');
            const permissionsList = roleGroup.find('.permissions-list');
            const roleHeader = roleGroup.find('.role-header');
            const collapseIcon = roleHeader.find('.collapse-icon');
            
            if (this.checked) {
                // تفعيل الدور
                roleGroup.addClass('active');
                roleHeader.removeClass('inactive').addClass(roleName.toLowerCase());
                permissionsList.addClass('show');
                collapseIcon.addClass('rotated');
                
                // تحديد جميع صلاحيات هذا الدور
                roleGroup.find('input[name="permissions[]"]').prop('checked', true)
                    .closest('.checkbox-item').addClass('checked');
            } else {
                // إلغاء تفعيل الدور
                roleGroup.removeClass('active');
                roleHeader.addClass('inactive').removeClass(roleName.toLowerCase());
                permissionsList.removeClass('show');
                collapseIcon.removeClass('rotated');
                
                // إلغاء تحديد صلاحيات هذا الدور
                roleGroup.find('input[name="permissions[]"]').prop('checked', false)
                    .closest('.checkbox-item').removeClass('checked');
            }
            
            toggleFields();
            updateCounters();
        });
    }

    // التعامل مع تغيير الصلاحيات
    function handlePermissionChange() {
        $(document).on('change', 'input[name="permissions[]"]', function() {
            const permissionItem = $(this).closest('.checkbox-item');
            
            if (this.checked) {
                permissionItem.addClass('checked');
            } else {
                permissionItem.removeClass('checked');
            }
            
            updateCounters();
        });
    }

    // التعامل مع النقر على العناصر
    function handleItemClicks() {
        $(document).on('click', '.checkbox-item', function(e) {
            if (e.target.type != 'checkbox' && !$(e.target).hasClass('checkmark')) {
                const checkbox = $(this).find('input[type="checkbox"]');
                checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
            }
        });

        // التعامل مع النقر على رأس الدور
        $(document).on('click', '.role-header', function(e) {
            if (e.target.type != 'checkbox' && !$(e.target).hasClass('checkmark') && !$(e.target).hasClass('custom-checkbox')) {
                const collapseIcon = $(this).find('.collapse-icon');
                const permissionsList = $(this).siblings('.permissions-list');
                
                if (permissionsList.hasClass('show')) {
                    permissionsList.removeClass('show');
                    collapseIcon.removeClass('rotated');
                } else {
                    permissionsList.addClass('show');
                    collapseIcon.addClass('rotated');
                }
            }
        });
    }

    // توسيع/طي الكل
    $('#expand-all').click(function() {
        const allExpanded = $('.permissions-list.show').length === $('.permissions-list').length;
        
        if (allExpanded) {
            $('.permissions-list').removeClass('show');
            $('.collapse-icon').removeClass('rotated');
            $(this).text('توسيع الكل');
        } else {
            $('.permissions-list').addClass('show');
            $('.collapse-icon').addClass('rotated');
            $(this).text('طي الكل');
        }
    });

    // البحث
    $('#roles-permissions-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('.role-group').each(function() {
            const roleGroup = $(this);
            const roleSearch = roleGroup.data('search');
            let roleMatches = roleSearch.includes(searchTerm);
            let hasVisiblePermissions = false;
            
            // البحث في الصلاحيات
            roleGroup.find('.checkbox-item').each(function() {
                const permissionSearch = $(this).data('search');
                if (permissionSearch && permissionSearch.includes(searchTerm)) {
                    $(this).show();
                    hasVisiblePermissions = true;
                } else if (searchTerm === '') {
                    $(this).show();
                    hasVisiblePermissions = true;
                } else {
                    $(this).hide();
                }
            });
            
            // إظهار/إخفاء مجموعة الدور
            if (roleMatches || hasVisiblePermissions || searchTerm === '') {
                roleGroup.show();
                if (searchTerm != '' && hasVisiblePermissions) {
                    roleGroup.find('.permissions-list').addClass('show');
                    roleGroup.find('.collapse-icon').addClass('rotated');
                }
            } else {
                roleGroup.hide();
            }
        });
    });

    // إظهار/إخفاء الحقول حسب الدور
    function toggleFields() {
        const selectedRoles = $('input[name="role[]"]:checked').map(function() {
            return $(this).val().toLowerCase();
        }).get();
        
        const hasTeacher = selectedRoles.includes('teacher');
        const hasAdminOrFinance = selectedRoles.includes('admin') || selectedRoles.includes('finance');

        $('.teacher-only').toggle(hasTeacher);
        $('.staff-only').toggle(hasAdminOrFinance);

        $('#subject').prop('required', hasTeacher);
        $('#session_price').prop('required', hasTeacher);
        $('#salary').prop('required', hasAdminOrFinance);
        $('#job_title').prop('required', hasAdminOrFinance);

        $('.teacher-required-marker').toggle(hasTeacher);
        $('.staff-required-marker').toggle(hasAdminOrFinance);
    }

    // تهيئة الصفحة
    function initializePage() {
        handleRoleChange();
        handlePermissionChange();
        handleItemClicks();
        updateCounters();
        toggleFields();
        
        // فتح الأدوار المحددة
        $('input[name="role[]"]:checked').each(function() {
            const roleGroup = $(this).closest('.role-group');
            roleGroup.find('.permissions-list').addClass('show');
            roleGroup.find('.collapse-icon').addClass('rotated');
        });
    }

    // تشغيل التهيئة
    initializePage();
});
</script>
@endpush