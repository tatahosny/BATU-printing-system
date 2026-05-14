<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. حساب الأدمن (موجود في الـ Enum كـ admin)
        User::create([
            'name' => 'المدير العام',
            'email' => 'admin@batu.com',
            'password' => Hash::make('admin123456'),
            'role' => 'admin',
        ]);

        // 2. حساب المندوب (لازم تستخدم section_delegate أو general_delegate)
        User::create([
            'name' => 'مندوب تجربة',
            'email' => 'delegate@batu.com',
            'password' => Hash::make('12345678'),
            'role' => 'section_delegate', // دي القيمة الصحيحة طبقاً للملف بتاعك
        ]);

        $this->command->info('✅ تم إنشاء الحسابات المتوافقة مع الصلاحيات بنجاح!');
    }
}
