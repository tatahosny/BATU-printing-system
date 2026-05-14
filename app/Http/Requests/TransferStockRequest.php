<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user->isAdmin() || $user->isGeneralDelegate();
    }

    public function rules(): array
    {
        return [
            'subject_id'   => 'required|integer|exists:subjects,id',
            'to_user_id'   => 'required|integer|exists:users,id|different:from_user_id',
            'from_user_id' => 'nullable|integer|exists:users,id',
            'quantity'     => 'required|integer|min:1|max:100000',
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.required'     => 'يجب اختيار المادة الدراسية',
            'to_user_id.required'     => 'يجب اختيار المندوب المستلم',
            'to_user_id.different'    => 'لا يمكن النقل لنفس الشخص',
            'quantity.required'       => 'يجب إدخال الكمية',
            'quantity.min'            => 'الكمية يجب أن تكون 1 على الأقل',
        ];
    }
}
