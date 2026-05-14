<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddMainStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'subject_id' => 'required|integer|exists:subjects,id',
            'quantity'    => 'required|integer|min:1|max:100000',
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.required' => 'يجب اختيار المادة الدراسية',
            'subject_id.exists'   => 'المادة المختارة غير موجودة',
            'quantity.required'   => 'يجب إدخال الكمية',
            'quantity.min'        => 'الكمية يجب أن تكون 1 على الأقل',
            'quantity.max'        => 'الكمية لا يمكن أن تتجاوز 100,000',
        ];
    }
}
