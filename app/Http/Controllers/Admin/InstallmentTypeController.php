<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\InstallmentType;
use App\Http\Controllers\Controller;

class InstallmentTypeController extends Controller
{
    public function index() {
        $installments_types = InstallmentType::all();
        return view('admin.installments-types.index', compact('installments_types'));
    }

    public function create() {
        return view('admin.installments-types.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255|unique:installment_types,name',
            'description' => 'nullable|string|max:1000',
        ]);

        InstallmentType::create($request->only('name', 'description'));

        return redirect()->route('admin.installments-types.index')->with('success', 'Installment type created successfully.');
    }

    public function edit(InstallmentType $installmentType) {
        return view('admin.installments-types.edit', compact('installmentType'));
    }

    public function update(Request $request, InstallmentType $installmentType) {
        $request->validate([
            'name' => 'required|string|max:255|unique:installment_types,name,' . $installmentType->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $installmentType->update($request->only('name', 'description'));

        return redirect()->route('admin.installments-types.index')->with('success', 'Installment type updated successfully.');
    }


    public function destroy(InstallmentType $installmentType) {
        $installmentType->delete();
        return redirect()->route('admin.installments-types.index')->with('success', 'Installment type deleted successfully.');
    }


    public function toggleStatus(InstallmentType $installmentType) {
        $installmentType->status = $installmentType->status === 'active' ? 'inactive' : 'active';
        $installmentType->save();
        return redirect()->route('admin.installments-types.index')->with('success', 'Installment type status updated successfully.');
    }
}
