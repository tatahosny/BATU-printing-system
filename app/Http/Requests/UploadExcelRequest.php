<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadExcelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'sheets'   => 'required|array|min:1|max:5',
            'sheets.*' => 'required|file|mimes:xlsx,xls,csv|max:5120', // 5MB max
            'batch_id' => 'nullable|integer|exists:batches,id',
        ];
    }

    public function messages(): array
    {
        return [
            'sheets.required'  => 'يجب رفع ملف واحد على الأقل',
            'sheets.max'       => 'لا يمكن رفع أكثر من 5 ملفات في المرة الواحدة',
            'sheets.*.mimes'   => 'صيغة الملف غير مدعومة. الصيغ المدعومة: xlsx, xls, csv',
            'sheets.*.max'     => 'حجم الملف لا يمكن أن يتجاوز 5 ميجابايت',
        ];
    }
}
