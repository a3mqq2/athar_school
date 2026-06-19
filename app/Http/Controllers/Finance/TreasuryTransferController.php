<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\TreasuryTransferRequest;
use App\Models\TreasuryTransfer;
use App\Models\Treasury;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TreasuryTransferController extends Controller
{
    public function index(Request $request)
    {
        $fromTreasuryId = $request->input('from_treasury_id');
        $toTreasuryId = $request->input('to_treasury_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $transfers = TreasuryTransfer::query()
            ->with(['fromTreasury', 'toTreasury', 'user'])
            ->when($fromTreasuryId, fn($query) => $query->where('from_treasury_id', $fromTreasuryId))
            ->when($toTreasuryId, fn($query) => $query->where('to_treasury_id', $toTreasuryId))
            ->when($fromDate, fn($query) => $query->whereDate('created_at', '>=', $fromDate))
            ->when($toDate, fn($query) => $query->whereDate('created_at', '<=', $toDate))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $treasuries = Treasury::all();

        return view('finance.treasury-transfers.index', compact(
            'transfers', 'treasuries', 'fromTreasuryId', 'toTreasuryId', 'fromDate', 'toDate'
        ));
    }

    public function create()
    {
        $treasuries = Treasury::all();
        
        return view('finance.treasury-transfers.create', compact('treasuries'));
    }

    public function store(TreasuryTransferRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['user_id'] = Auth::id();
            
            $transfer = TreasuryTransfer::create($data);
            
            DB::commit();
            
            return redirect()
                ->route('finance.treasury-transfers.index')
                ->with('success', 'تم تحويل المبلغ بنجاح');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء التحويل: ' . $e->getMessage());
        }
    }

    public function show(TreasuryTransfer $treasuryTransfer)
    {
        $treasuryTransfer->load(['fromTreasury', 'toTreasury', 'user', 'withdrawalTransaction', 'depositTransaction']);
        
        return view('finance.treasury-transfers.show', compact('treasuryTransfer'));
    }

    public function destroy(Request $request, TreasuryTransfer $treasuryTransfer)
    {
        try {
            DB::beginTransaction();

            // حذف التحويل سيؤدي إلى حذف المعاملات المرتبطة تلقائياً
            $treasuryTransfer->delete();
            
            // إعادة حساب أرصدة الخزائن
            $treasuryTransfer->fromTreasury->recalculateBalance();
            $treasuryTransfer->toTreasury->recalculateBalance();
            
            DB::commit();
            
            if ($request->expectsJson()) {
                return response()->json(['status' => true]);
            }
            
            return redirect()
                ->route('finance.treasury-transfers.index')
                ->with('success', 'تم حذف التحويل بنجاح');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->expectsJson()) {
                return response()->json(['status' => false, 'message' => $e->getMessage()]);
            }
            
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء الحذف: ' . $e->getMessage());
        }
    }

    // API للحصول على رصيد الخزينة
    public function getTreasuryBalance(Request $request)
    {
        $treasuryId = $request->input('treasury_id');
        
        if (!$treasuryId) {
            return response()->json(['error' => 'Treasury ID is required'], 400);
        }
        
        $treasury = Treasury::find($treasuryId);
        
        if (!$treasury) {
            return response()->json(['error' => 'Treasury not found'], 404);
        }
        
        return response()->json([
            'balance' => $treasury->current_balance,
            'formatted_balance' => number_format($treasury->current_balance, 2)
        ]);
    }
}