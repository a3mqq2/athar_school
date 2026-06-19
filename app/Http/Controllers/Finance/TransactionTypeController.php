<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionTypeRequest;
use App\Models\TransactionType;
use Illuminate\Http\Request;

class TransactionTypeController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type');
        $forSystem = $request->input('for_system');
        $name = $request->input('name');

        $transactionTypes = TransactionType::query()
            ->withCount('transactions')
            ->when($type, fn($query) => $query->where('type', $type))
            ->when($name, fn($query) => $query->where('name', 'like', "%{$name}%"))
            ->when($forSystem != null, fn($query) => $query->where('for_system', $forSystem))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('finance.transaction-types.index', compact('transactionTypes', 'type', 'forSystem', 'name'));
    }

    public function create()
    {
        return view('finance.transaction-types.create');
    }

    public function store(TransactionTypeRequest $request)
    {
        TransactionType::create($request->validated());
        
        return redirect()
            ->route('finance.transaction-types.index')
            ->with('success', 'تم إنشاء تصنيف المعاملة بنجاح');
    }

    public function show(TransactionType $transactionType)
    {
        $transactionType->loadCount('transactions');
        $recentTransactions = $transactionType->transactions()
            ->with(['treasury', 'user'])
            ->latest()
            ->limit(10)
            ->get();
            
        return view('finance.transaction-types.show', compact('transactionType', 'recentTransactions'));
    }

    public function edit(TransactionType $transactionType)
    {
        // منع تعديل التصنيفات الخاصة بالنظام
        if ($transactionType->for_system) {
            return redirect()
                ->route('finance.transaction-types.index')
                ->with('error', 'لا يمكن تعديل تصنيفات النظام');
        }
        
        return view('finance.transaction-types.edit', compact('transactionType'));
    }

    public function update(TransactionTypeRequest $request, TransactionType $transactionType)
    {
        // منع تعديل التصنيفات الخاصة بالنظام
        if ($transactionType->for_system) {
            return redirect()
                ->route('finance.transaction-types.index')
                ->with('error', 'لا يمكن تعديل تصنيفات النظام');
        }
        
        $transactionType->update($request->validated());
        
        return redirect()
            ->route('finance.transaction-types.index')
            ->with('success', 'تم تحديث تصنيف المعاملة بنجاح');
    }

    public function destroy(Request $request, TransactionType $transactionType)
    {
        // منع حذف التصنيفات الخاصة بالنظام
        if ($transactionType->for_system) {
            if ($request->expectsJson()) {
                return response()->json(['status' => false, 'message' => 'لا يمكن حذف تصنيفات النظام']);
            }
            return redirect()
                ->route('finance.transaction-types.index')
                ->with('error', 'لا يمكن حذف تصنيفات النظام');
        }
        
        // التحقق من عدم وجود معاملات مرتبطة
        if ($transactionType->transactions()->count() > 0) {
            if ($request->expectsJson()) {
                return response()->json(['status' => false, 'message' => 'لا يمكن حذف التصنيف لوجود معاملات مرتبطة به']);
            }
            return redirect()
                ->route('finance.transaction-types.index')
                ->with('error', 'لا يمكن حذف التصنيف لوجود معاملات مرتبطة به');
        }
        
        $transactionType->delete();
        
        if ($request->expectsJson()) {
            return response()->json(['status' => true]);
        }
        
        return redirect()
            ->route('finance.transaction-types.index')
            ->with('success', 'تم حذف تصنيف المعاملة بنجاح');
    }
}