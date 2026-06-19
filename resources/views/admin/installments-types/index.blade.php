{{-- resources/views/admin/installments-types/index.blade.php --}}
@extends('layouts.app')

@section('title', 'إدارة أنواع الأقساط')

@push('styles')
@include('partials.page-styles')
@endpush

@section('content')
<div class="container py-4">
    <h3 class="page-header">إدارة أنواع الأقساط</h3>

    <div class="table-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="section-title mb-0"><i class="fa fa-list"></i> قائمة أنواع الأقساط</h5>
            <a href="{{ route('admin.installments-types.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> إضافة نوع جديد
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>الوصف</th>
                        <th>الحالة</th>
                        <th>تاريخ الإنشاء</th>
                        <th class="text-center">التحكم</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($installments_types as $type)
                        <tr>
                            <td>{{ $type->id }}</td>
                            <td>{{ $type->name }}</td>
                            <td>{{ $type->description ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $type->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $type->status === 'active' ? 'نشط' : 'غير نشط' }}
                                </span>
                            </td>
                            <td>{{ $type->created_at?->format('Y-m-d') ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.installments-types.edit', $type) }}" class="btn btn-sm btn-secondary">
                                    <i class="fa fa-edit"></i>
                                </a>

                                <form action="{{ route('admin.installments-types.destroy', $type) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                </form>

                                <form action="{{ route('admin.installments-types.toggle', $type) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-warning">
                                        <i class="fa fa-toggle-on"></i>
                                        {{ $type->status === 'active' ? 'تعطيل' : 'تفعيل' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">لا يوجد بيانات</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
