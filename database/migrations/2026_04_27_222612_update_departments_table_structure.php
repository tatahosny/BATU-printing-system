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
        Schema::table('departments', function (Blueprint $table) {
            // إذا كان العمود موجوداً ونريد حذفه ليتناسب مع طلبك
            if (Schema::hasColumn('departments', 'university_id')) {
                $table->dropColumn('university_id');
            }

            // التأكد من وجود الأعمدة التي ذكرتها
            // ملاحظة: id, created_at, updated_at موجودين بالفعل افتراضياً
            if (!Schema::hasColumn('departments', 'name')) {
                $table->string('name')->after('id');
            }

            if (!Schema::hasColumn('departments', 'college_id')) {
                $table->foreignId('college_id')->after('name')->constrained()->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // لإرجاع العمود في حالة عمل Rollback
            $table->foreignId('university_id')->nullable()->constrained()->onDelete('cascade');
        });
    }
};
