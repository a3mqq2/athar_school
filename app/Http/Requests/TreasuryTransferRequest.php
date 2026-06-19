<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TreasuryTransferRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'from_treasury_id' => 'required|exists:treasuries,id',
            'to_treasury_id' => [
                'required',
                'exists:treasuries,id',
                'different:from_treasury_id'
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999999.99',
                function ($attribute, $value, $fail) {
                    $fromTreasuryId = $this->input('from_treasury_id');
                    if ($fromTreasuryId) {
                        $treasury = \App\Models\Treasury::find($fromTreasuryId);
                        if ($treasury && $value > $treasury->current_balance) {
                            $fail('المبلغ المطلوب تحويله أكبر من الرصيد المتاح في الخزينة (' . number_format($treasury->current_balance, 2) . ')');
                        }
                    }
                }
            ],
            'description' => 'nullable|string|max:1000',
            'reference_number' => 'nullable|string|max:100'
        ];
    }

    public function messages()
    {
        return [
            'from_treasury_id.required' => 'خزينة المرسل مطلوبة',
            'from_treasury_id.exists' => 'خزينة المرسل غير موجودة',
            
            'to_treasury_id.required' => 'خزينة المستقبل مطلوبة',
            'to_treasury_id.exists' => 'خزينة المستقبل غير موجودة',
            'to_treasury_id.different' => 'لا يمكن التحويل من الخزينة إلى نفسها',
            
            'amount.required' => 'المبلغ مطلوب',
            'amount.numeric' => 'المبلغ يجب أن يكون رقم',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'amount.max' => 'المبلغ كبير جداً',
            
            'description.string' => 'الوصف يجب أن يكون نص',
            'description.max' => 'الوصف لا يجب أن يزيد عن 1000 حرف',
            
            'reference_number.string' => 'الرقم المرجعي يجب أن يكون نص',
            'reference_number.max' => 'الرقم المرجعي لا يجب أن يزيد عن 100 حرف'
        ];
    }

    public function prepareForValidation()
    {
        // تحويل الفواصل العربية إلى إنجليزية للأرقام
        if ($this->has('amount')) {
            $amount = str_replace(['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'], 
                                ['0','1','2','3','4','5','6','7','8','9'], 
                                $this->amount);
            $amount = str_replace(',', '', $amount);
            $this->merge(['amount' => $amount]);
        }
    }
}