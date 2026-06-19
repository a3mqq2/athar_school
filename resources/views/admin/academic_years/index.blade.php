{{-- resources/views/admin/academic_years/create.blade.php --}}
@extends('layouts.app')

@section('title','إضافة سنة دراسية')

@push('styles')
   @include('partials.page-styles')
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4">
    <h2 class="page-header">السنوات الدراسية</h2>

    <div class="mb-3 text-end">
        <a href="{{ route('admin.academic_years.create') }}" class="btn btn-dark">+ إضافة سنة</a>
    </div>


    <div class="table-card form-card">
        <div class="form-card-header">القائمة</div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>الفترة</th>
                    <th>الحالة</th>
                    <th class="text-end">إجراءات</th>
                </tr>
                </thead>
                <tbody>
                @forelse($years as $y)
                    <tr>
                        <td>{{ $y->id }}</td>
                        <td>{{ $y->name }}</td>
                        <td>
                            {{ $y->start_date?->format('Y-m-d') }} — {{ $y->end_date?->format('Y-m-d') }}
                        </td>
                        <td>
                            @if($y->is_current)
                                <span class="badge-current">الحالية</span>
                            @else
                                <span class="badge bg-light text-dark">مؤرشفة</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.academic_years.edit',$y) }}" class="btn btn-sm btn-secondary">تعديل</a>
                            <form action="{{ route('admin.academic_years.destroy',$y) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('تأكيد حذف السنة؟');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" @if($y->is_current) disabled @endif>حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">لا توجد بيانات</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $years->links() }}
        </div>
    </div>
</div>
@endsection
