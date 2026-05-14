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
        // في SQLite تعديل الأعمدة التي عليها Constraints يتطلب حذفها وإعادة إنشائها
        Schema::table('subjects', function (Blueprint $table) {
            // حذف العمود القديم اللي عامل المشكلة
            if (Schema::hasColumn('subjects', 'term')) {
                $table->dropColumn('term');
            }
        });

        Schema::table('subjects', function (Blueprint $table) {
            // إضافة العمود من جديد بنوع integer بسيط وبدون تعقيدات
            // وضعناه بعد الـ batch_id للتنظيم
            $table->integer('term')->default(1)->after('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('term');
        });
    }
};
