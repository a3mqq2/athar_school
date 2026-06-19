<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:deposit,withdrawal',
            'for_system' => 'boolean'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'اسم التصنيف مطلوب',
            'name.string' => 'اسم التصنيف يجب أن يكون نص',
            'name.max' => 'اسم التصنيف لا يجب أن يزيد عن 255 حرف',
            
            'type.required' => 'نوع التصنيف مطلوب',
            'type.in' => 'نوع التصنيف يجب أن يكون إيداع أو سحب',
            
            'for_system.boolean' => 'حقل النظام يجب أن يكون صح أو خطأ'
        ];
    }
}