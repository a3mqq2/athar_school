<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\TreasuryRequest;
use App\Models\Treasury;
use App\Models\User;
use Illuminate\Http\Request;

class TreasuryController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');
        $responsibleId = $request->input('responsible_user_id');

        $treasuries = Treasury::query()
            ->with('responsible')
            ->when($q, fn($qry) => $qry->where('name', 'like', "%{$q}%"))
            ->when($responsibleId, fn($qry) => $qry->where('responsible_user_id', $responsibleId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $users = User::role('finance')->get();

        return view('finance.treasuries.index', compact('treasuries', 'users', 'q', 'responsibleId'));
    }

    public function create()
    {
        $users = User::role('finance')->get();
        return view('finance.treasuries.create', compact('users'));
    }

    public function store(TreasuryRequest $request)
    {
        Treasury::create($request->validated());
        return redirect()->route('finance.treasuries.index')->with('success', 'تم إنشاء الخزينة بنجاح');
    }

    public function show(Treasury $treasury)
    {
        $treasury->load('responsible');
        return view('finance.treasuries.show', compact('treasury'));
    }

    public function edit(Treasury $treasury)
    {
        $users = User::role('finance')->get();
        return view('finance.treasuries.edit', compact('treasury', 'users'));
    }

    public function update(TreasuryRequest $request, Treasury $treasury)
    {
        $data = $request->validated();
        unset($data['opening_balance']);
        $treasury->update($data);
        return redirect()->route('finance.treasuries.index')->with('success', 'تم تحديث الخزينة بنجاح');
    }

    public function destroy(Request $request, Treasury $treasury)
    {
        $treasury->delete();
        if ($request->expectsJson()) {
            return response()->json(['status' => true]);
        }
        return redirect()->route('finance.treasuries.index')->with('success', 'تم حذف الخزينة');
    }
}
