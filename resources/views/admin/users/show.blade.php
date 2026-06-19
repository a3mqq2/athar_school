@extends('layouts.app')

@section('title', 'عرض بيانات المستخدم')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">بيانات المستخدم</h5>
                <a href="{{ route(get_area_name().'.users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> رجوع
                </a>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">الاسم</th>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th>البريد الإلكتروني</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th>الحالة</th>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">نشط</span>
                            @else
                                <span class="badge bg-danger">غير نشط</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>الأدوار</th>
                        <td>
                            @forelse($user->roles as $role)
                                <span class="badge bg-info">{{ $role->display_name ?? $role->display_name }}</span>
                            @empty
                                <span class="text-muted">لا يوجد</span>
                            @endforelse
                        </td>
                    </tr>
                    <tr>
                        <th>الصلاحيات</th>
                        <td>
                            @forelse($user->permissions as $permission)
                                <span class="badge bg-secondary">{{ $permission->name }}</span>
                            @empty
                                <span class="text-muted">لا يوجد</span>
                            @endforelse
                        </td>
                    </tr>
                    <tr>
                        <th>تاريخ الإنشاء</th>
                        <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    <tr>
                        <th>آخر تحديث</th>
                        <td>{{ $user->updated_at->format('Y-m-d H:i') }}</td>
                    </tr>
                </table>
            </div>
            <div class="card-footer text-end">
                <a href="{{ route(get_area_name().'.users.edit', $user) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                <form action="{{ route(get_area_name().'.users.destroy', $user) }}" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">
                        <i class="fas fa-trash"></i> حذف
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
