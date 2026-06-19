<?php

// app/Http/Controllers/Admin/AcademicYearController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicYearController extends Controller
{
    public function index()
    {
        $years = AcademicYear::orderByDesc('is_current')->orderByDesc('id')->paginate(12);
        return view('admin.academic_years.index', compact('years'));
    }

    public function create()
    {
        return view('admin.academic_years.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required','string','max:100'],
            'start_date' => ['nullable','date'],
            'end_date'   => ['nullable','date','after_or_equal:start_date'],
        ]);


        AcademicYear::where('is_current', true)->update(['is_current' => false]);

        AcademicYear::create([
            'name'       => $data['name'],
            'start_date' => $data['start_date'] ?? null,
            'end_date'   => $data['end_date'] ?? null,
            'is_current' => 1,
        ]);

        return redirect()->route('admin.academic_years.index')->with('success', 'تم الحفظ');
    }

    public function edit(AcademicYear $academic_year)
    {
        return view('admin.academic_years.edit', ['year' => $academic_year]);
    }

    public function update(Request $request, AcademicYear $academic_year)
    {
        $data = $request->validate([
            'name'       => ['required','string','max:100'],
            'start_date' => ['nullable','date'],
            'end_date'   => ['nullable','date','after_or_equal:start_date'],
            'is_current' => ['nullable', Rule::in(['on'])],
        ]);

        $isCurrent = isset($data['is_current']) && $data['is_current'] === 'on';

        if ($isCurrent) {
            AcademicYear::where('is_current', true)->where('id','!=',$academic_year->id)->update(['is_current' => false]);
        }

        $academic_year->update([
            'name'       => $data['name'],
            'start_date' => $data['start_date'] ?? null,
            'end_date'   => $data['end_date'] ?? null,
            'is_current' => $isCurrent,
        ]);

        return redirect()->route('admin.academic_years.index')->with('success', 'تم التحديث');
    }

    public function destroy(AcademicYear $academic_year)
    {
        if ($academic_year->is_current) {
            return back()->with('error','لا يمكن حذف السنة الحالية');
        }
        $academic_year->delete();
        return redirect()->route('admin.academic_years.index')->with('success', 'تم الحذف');
    }

    public function setCurrent(AcademicYear $academic_year)
    {
        AcademicYear::where('is_current', true)->update(['is_current' => false]);
        $academic_year->update(['is_current' => true]);

        return redirect()->route('admin.academic_years.index')->with('success','تم تعيين السنة الحالية');
    }
}
