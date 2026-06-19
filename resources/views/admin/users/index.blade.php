{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.app')

@section('title', 'إدارة المستخدمين')

@push('styles')
@include('partials.page-styles')
@endpush

@section('content')
<div class="container py-4">
    <h3 class="page-header">إدارة المستخدمين</h3>

    <div class="filter-card mb-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">الاسم</label>
                <input type="text" name="name" value="{{ request('name') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="text" name="email" value="{{ request('email') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">رقم الهاتف</label>
                <input type="text" name="phone" value="{{ request('phone') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">الدور</label>
                <select name="role" class="form-select">
                    <option value="">الكل</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                            {{ $role->display_name ?? $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">المادة (للمدرّسين)</label>
                <input type="text" name="subject" value="{{ request('subject') }}" class="form-control">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">بحث</button>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="section-title mb-0"><i class="fa fa-users"></i> قائمة المستخدمين</h5>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> إضافة مستخدم
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الهاتف</th>
                        <th>الأدوار</th>
                        <th>تاريخ التعيين</th>
                        <th> المسمى الوظيفي </th>
                        <th>المادة</th>
                        <th>سعر الحصة</th>
                        <th>الراتب</th>
                        <th class="text-center">التحكم</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->code }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? '-' }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-secondary">{{ $role->display_name }}</span>
                                @endforeach
                            </td>
                            <td>{{ $user->hire_date?->format('Y-m-d') ?? '-' }}</td>
                            <td>{{$user->job_title}}</td>
                            <td>{{ $user->subject ?? '-' }}</td>
                            <td>{{ $user->session_price ? number_format($user->session_price, 2) : '-' }}</td>
                            <td>{{ $user->salary ? number_format($user->salary, 2) : '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-secondary">
                                    <i class="fa fa-edit"></i>
                                </a>
                                @if ($user->id != 1)
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">لا يوجد بيانات</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
