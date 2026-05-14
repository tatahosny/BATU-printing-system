<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('students', function (Blueprint $table) {
            $table->index('student_external_id'); // للبحث بالرقم الأكاديمي
            $table->index('name');                // للبحث بالاسم
        });
    }

    public function down(): void {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['student_external_id']);
            $table->dropIndex(['name']);
        });
    }
};
