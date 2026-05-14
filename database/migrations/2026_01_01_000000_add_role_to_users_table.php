<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            /**
             * أنواع المستخدمين:
             * admin: Printing Manager (صلاحيات كاملة)
             * general_delegate: مندوب عام (مسؤول عن دفعة أو كلية)
             * section_delegate: مندوب سكشن (مسؤول عن تسليم الكتب للطلاب)
             */
            $table->enum('role', [
                'admin',
                'general_delegate',
                'section_delegate'
            ])->default('section_delegate')->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
