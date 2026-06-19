<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'payee_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'description' => 'nullable|string|max:1000',
            'document_number' => 'nullable|string|max:100',
            'transaction_type' => 'required|in:deposit,withdrawal',
            'transaction_type_id' => 'required|exists:transaction_types,id',
            'treasury_id' => 'required|exists:treasuries,id',
            'payment_method' => "required",
        ];
    }

    public function messages()
    {
        return [
            'payee_name.required' => 'اسم المستلم مطلوب',
            'payee_name.string' => 'اسم المستلم يجب أن يكون نص',
            'payee_name.max' => 'اسم المستلم لا يجب أن يزيد عن 255 حرف',
            
            'amount.required' => 'المبلغ مطلوب',
            'amount.numeric' => 'المبلغ يجب أن يكون رقم',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'amount.max' => 'المبلغ كبير جداً',
            
            'description.string' => 'الوصف يجب أن يكون نص',
            'description.max' => 'الوصف لا يجب أن يزيد عن 1000 حرف',
            
            'document_number.string' => 'رقم المستند يجب أن يكون نص',
            'document_number.max' => 'رقم المستند لا يجب أن يزيد عن 100 حرف',
            
            'transaction_type.required' => 'نوع المعاملة مطلوب',
            'transaction_type.in' => 'نوع المعاملة يجب أن يكون إيداع أو سحب',
            
            'transaction_type_id.required' => 'تصنيف المعاملة مطلوب',
            'transaction_type_id.exists' => 'تصنيف المعاملة غير موجود',
            
            'treasury_id.required' => 'الخزينة مطلوبة',
            'treasury_id.exists' => 'الخزينة غير موجودة'
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