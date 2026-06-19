<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Models\Treasury;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $treasuryId = $request->input('treasury_id');
        $transactionTypeId = $request->input('transaction_type_id');
        $transactionType = $request->input('transaction_type');
        $paymentMethod = $request->input('payment_method');
        $payeeName = $request->input('payee_name');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $hasFilters = $treasuryId || $transactionTypeId || $transactionType || $paymentMethod || $payeeName || $fromDate || $toDate;

        $query = Transaction::query()
            ->with(['treasury', 'transactionType', 'user'])
            ->when($treasuryId, fn($query) => $query->where('treasury_id', $treasuryId))
            ->when($transactionTypeId, fn($query) => $query->where('transaction_type_id', $transactionTypeId))
            ->when($transactionType, fn($query) => $query->where('transaction_type', $transactionType))
            ->when($paymentMethod, fn($query) => $query->where('payment_method', $paymentMethod))
            ->when($payeeName, fn($query) => $query->where('payee_name', 'like', "%{$payeeName}%"))
            ->when($fromDate, fn($query) => $query->whereDate('created_at', '>=', $fromDate))
            ->when($toDate, fn($query) => $query->whereDate('created_at', '<=', $toDate))
            ->latest();

        if ($hasFilters) {
            $transactions = $query->get();
            $totalDeposits = $transactions->where('transaction_type', 'deposit')->sum('amount');
            $totalWithdrawals = $transactions->where('transaction_type', 'withdrawal')->sum('amount');
        } else {
            $transactions = $query->paginate(15)->withQueryString();
            $totalDeposits = 0;
            $totalWithdrawals = 0;
        }

        $netBalance = $totalDeposits - $totalWithdrawals;

        $treasuries = Treasury::all();
        $transactionTypes = TransactionType::forUsers()->get();

        return view('finance.transactions.index', compact(
            'transactions', 'treasuries', 'transactionTypes',
            'treasuryId', 'transactionTypeId', 'transactionType',
            'paymentMethod', 'payeeName', 'fromDate', 'toDate',
            'hasFilters', 'totalDeposits', 'totalWithdrawals', 'netBalance'
        ));
    }

    public function create()
    {
        $treasuries = Treasury::all();
        $transactionTypes = TransactionType::forUsers()->get();
        
        return view('finance.transactions.create', compact('treasuries', 'transactionTypes'));
    }

    public function store(TransactionRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        
        $transaction = Transaction::create($data);
        
        return redirect()
            ->route('finance.transactions.index', ['transaction_id' =>  $transaction->id])
            ->with('success', 'تم إضافة المعاملة بنجاح');
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['treasury', 'transactionType', 'user']);
        
        return view('finance.transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction)
    {
        $treasuries = Treasury::all();
        $transactionTypes = TransactionType::forUsers()->get();
        
        return view('finance.transactions.edit', compact('transaction', 'treasuries', 'transactionTypes'));
    }

    public function update(TransactionRequest $request, Transaction $transaction)
    {
        $oldTreasuryId = $transaction->treasury_id;
        
        $transaction->update($request->validated());
        
        if ($oldTreasuryId != $transaction->treasury_id) {
            Treasury::find($oldTreasuryId)?->recalculateBalance();
        }
        
        return redirect()
            ->route('finance.transactions.index', ['transaction_id' =>  $transaction->id])
            ->with('success', 'تم تحديث المعاملة بنجاح');
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        $transaction->delete();
        
        if ($request->expectsJson()) {
            return response()->json(['status' => true]);
        }
        
        return redirect()
            ->route('finance.transactions.index')
            ->with('success', 'تم حذف المعاملة بنجاح');
    }

    public function getTransactionTypes(Request $request)
    {
        $type = $request->input('type');
        
        $transactionTypes = TransactionType::forUsers()
            ->when($type, fn($query) => $query->where('type', $type))
            ->get();
            
        return response()->json($transactionTypes);
    }

    public function storeTransactionType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:deposit,withdrawal'
        ]);

        $transactionType = TransactionType::create([
            'name' => $request->name,
            'type' => $request->type,
            'for_system' => false
        ]);

        return response()->json([
            'status' => true,
            'transaction_type' => $transactionType,
            'message' => 'تم إضافة التصنيف بنجاح'
        ]);
    }

    public function printStatement(Request $request)
    {
        $treasuryId = $request->input('treasury_id');
        $transactionTypeId = $request->input('transaction_type_id');
        $transactionType = $request->input('transaction_type');
        $paymentMethod = $request->input('payment_method');
        $payeeName = $request->input('payee_name');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $transactions = Transaction::query()
            ->with(['treasury', 'transactionType', 'user'])
            ->when($treasuryId, fn($query) => $query->where('treasury_id', $treasuryId))
            ->when($transactionTypeId, fn($query) => $query->where('transaction_type_id', $transactionTypeId))
            ->when($transactionType, fn($query) => $query->where('transaction_type', $transactionType))
            ->when($paymentMethod, fn($query) => $query->where('payment_method', $paymentMethod))
            ->when($payeeName, fn($query) => $query->where('payee_name', 'like', "%{$payeeName}%"))
            ->when($fromDate, fn($query) => $query->whereDate('created_at', '>=', $fromDate))
            ->when($toDate, fn($query) => $query->whereDate('created_at', '<=', $toDate))
            ->latest()
            ->get();

        $totalDeposits = $transactions->where('transaction_type', 'deposit')->sum('amount');
        $totalWithdrawals = $transactions->where('transaction_type', 'withdrawal')->sum('amount');
        $netBalance = $totalDeposits - $totalWithdrawals;

        $filters = [
            'treasury' => $treasuryId ? Treasury::find($treasuryId)?->name : null,
            'transactionType' => $transactionType ? ($transactionType == 'deposit' ? 'إيداع' : 'سحب') : null,
            'transactionTypeId' => $transactionTypeId ? TransactionType::find($transactionTypeId)?->name : null,
            'paymentMethod' => $paymentMethod ? ($paymentMethod == 'cash' ? 'نقدي' : 'تحويل بنكي') : null,
            'payeeName' => $payeeName,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ];

        return view('finance.transactions.statement', compact(
            'transactions', 'totalDeposits', 'totalWithdrawals', 'netBalance', 'filters'
        ));
    }

    public function printReceipt(Transaction $transaction)
    {
        $transaction->load(['treasury', 'transactionType', 'user', 'studentPayment']);

        $amountInWords = $this->convertNumberToWords($transaction->amount);

        $data = [
            'companyName'        => config('app.name', 'اسم الشركة'),
            'companyAddress'     => 'عنوان الشركة',
            'companyPhone'       => '000000000',
            'companyFax'         => '000000000',
            'receiptNumber'      => str_pad($transaction->id, 6, '0', STR_PAD_LEFT),
            'payeeName'          => $transaction->studentPayment ? $transaction->studentPayment->student->name : $transaction->payee_name,
            'formattedAmount'    => number_format($transaction->amount, 2),
            'description'        => $transaction->description ?: $transaction->transactionType->name,
            'date'               => $transaction->created_at->format('Y/m/d'),
            'treasuryName'       => $transaction->treasury->name,
            'transactionTypeName'=> $transaction->transactionType->name,
            'documentNumber'     => $transaction->document_number,
            'amountInWords'      => $amountInWords,
            'transaction'        => $transaction,
            'paymentMethod'      => $transaction->payment_method_name,
            'studentPayment'     => $transaction->studentPayment ?? null,
            'type'               => $transaction->transaction_type == "withdrawal" ? "صرف" : "قبض",
        ];

        return view('finance.receipts.financial-receipt', $data);
    }
    
    private function convertNumberToWords($number)
    {
        $number = (string)$number;
        $number = str_replace(',', '', $number);

        $neg = false;
        if (strpos($number, '-') === 0) {
            $neg = true;
            $number = ltrim($number, '-');
        }

        $formatted = number_format((float)$number, 2, '.', '');
        [$intPart, $decPart] = explode('.', $formatted);
        $int = (int)$intPart;
        $dec = (int)$decPart;

        if ($int === 0 && $dec === 0) return 'صفر فقط لا غير';

        $words = [];

        if ($int > 0) {
            $words[] = ($neg ? 'سالب ' : '') . $this->arabicIntegerToWords($int);
        }

        if ($dec > 0) {
            $words[] = $this->arabicIntegerToWords($dec) . ' فلس';
        }

        return implode(' و ', $words) . ' فقط لا غير';
    }

    private function arabicIntegerToWords(int $number): string
    {
        if ($number === 0) return 'صفر';

        $ones = [
            0=>'',
            1=>'واحد', 2=>'اثنان', 3=>'ثلاثة', 4=>'أربعة', 5=>'خمسة',
            6=>'ستة', 7=>'سبعة', 8=>'ثمانية', 9=>'تسعة',
            10=>'عشرة', 11=>'أحد عشر', 12=>'اثنا عشر', 13=>'ثلاثة عشر',
            14=>'أربعة عشر', 15=>'خمسة عشر', 16=>'ستة عشر', 17=>'سبعة عشر',
            18=>'ثمانية عشر', 19=>'تسعة عشر'
        ];

        $tens = [
            0=>'',
            2=>'عشرون', 3=>'ثلاثون', 4=>'أربعون', 5=>'خمسون',
            6=>'ستون', 7=>'سبعون', 8=>'ثمانون', 9=>'تسعون'
        ];

        $hundreds = [
            0=>'',
            1=>'مائة', 2=>'مائتان', 3=>'ثلاثمائة', 4=>'أربعمائة',
            5=>'خمسمائة', 6=>'ستمائة', 7=>'سبعمائة',
            8=>'ثمانمائة', 9=>'تسعمائة'
        ];

        $scales = [
            0 => ['', '', ''],
            1 => ['ألف', 'ألفان', 'آلاف'],
            2 => ['مليون', 'مليونان', 'ملايين'],
            3 => ['مليار', 'ملياران', 'مليارات'],
            4 => ['تريليون', 'تريليونان', 'تريليونات'],
        ];

        $parts = [];
        $groupIndex = 0;

        while ($number > 0) {
            $group = $number % 1000;
            if ($group > 0) {
                $groupWords = $this->arabicThreeDigitsToWords($group, $ones, $tens, $hundreds);
                if ($groupIndex === 0) {
                    $parts[] = $groupWords;
                } else {
                    if ($group === 1) {
                        $parts[] = $scales[$groupIndex][0];
                    } elseif ($group === 2) {
                        $parts[] = $scales[$groupIndex][1];
                    } elseif ($group >= 3 && $group <= 10) {
                        $parts[] = $groupWords . ' ' . $scales[$groupIndex][2];
                    } else {
                        $parts[] = $groupWords . ' ' . $scales[$groupIndex][0];
                    }
                }
            }
            $number = intdiv($number, 1000);
            $groupIndex++;
        }

        return $this->joinArabicParts(array_reverse($parts));
    }

    private function arabicThreeDigitsToWords(int $num, array $ones, array $tens, array $hundreds): string
    {
        $h = intdiv($num, 100);
        $remainder = $num % 100;
        $chunks = [];

        if ($h > 0) {
            $chunks[] = $hundreds[$h];
        }

        if ($remainder > 0) {
            if ($remainder < 20) {
                $chunks[] = $ones[$remainder];
            } else {
                $t = intdiv($remainder, 10);
                $u = $remainder % 10;
                if ($u > 0) {
                    $chunks[] = $ones[$u] . ' و ' . $tens[$t];
                } else {
                    $chunks[] = $tens[$t];
                }
            }
        }

        return $this->joinArabicParts($chunks);
    }

    private function joinArabicParts(array $parts): string
    {
        $parts = array_values(array_filter(array_map('trim', $parts), fn($p)=>$p != ''));
        if (empty($parts)) return '';
        return implode(' و ', $parts);
    }
}
