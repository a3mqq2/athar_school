<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TreasuryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $treasuryId = $this->route('treasury')?->id ?? null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('treasuries', 'name')->ignore($treasuryId),
            ],
            'opening_balance' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'numeric',
                'min:0',
            ],
            'responsible_user_id' => [
                'required',
                'exists:users,id',
            ],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        if ($this->isMethod('post')) {
            $data['current_balance'] = $data['opening_balance'] ?? 0;
        }
        return $data;
    }
}
