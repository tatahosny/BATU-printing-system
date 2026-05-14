<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('delegate_assignments', function (Blueprint $table) {
            // ربط المندوب بنطاق محدد لا يخرج عنه
            $table->foreignId('university_id')->constrained()->onDelete('cascade');
            $table->foreignId('college_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->constrained()->onDelete('cascade'); // الفرقة الدراسية
        });
    }

    public function down(): void {
        Schema::table('delegate_assignments', function (Blueprint $table) {
            $table->dropForeign(['university_id', 'college_id', 'department_id', 'batch_id']);
            $table->dropColumn(['university_id', 'college_id', 'department_id', 'batch_id']);
        });
    }
};
