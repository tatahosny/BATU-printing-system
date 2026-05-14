<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDelegateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:general_delegate,section_delegate',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'يجب إدخال اسم المندوب',
            'email.required'    => 'يجب إدخال البريد الإلكتروني',
            'email.unique'      => 'البريد الإلكتروني مسجل مسبقاً',
            'password.required' => 'يجب إدخال كلمة المرور',
            'password.min'      => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'role.required'     => 'يجب اختيار نوع المندوب',
            'role.in'           => 'نوع المندوب غير صالح',
        ];
    }
}
