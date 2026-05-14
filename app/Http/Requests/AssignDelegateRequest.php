<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignDelegateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user->isAdmin() || $user->isGeneralDelegate();
    }

    public function rules(): array
    {
        return [
            'university_id' => 'required|integer|exists:universities,id',
            'college_id'    => 'required|integer|exists:colleges,id',
            'department_id' => 'required|integer|exists:departments,id',
            'batch_id'      => 'required|integer|exists:batches,id',
            'section_id'    => 'nullable|integer|exists:sections,id',
            'subject_id'    => 'nullable|integer|exists:subjects,id',
        ];
    }

    public function messages(): array
    {
        return [
            'university_id.required' => 'يجب اختيار الجامعة',
            'college_id.required'    => 'يجب اختيار الكلية',
            'department_id.required' => 'يجب اختيار القسم',
            'batch_id.required'      => 'يجب اختيار الفرقة الدراسية',
        ];
    }
}
