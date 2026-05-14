<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_external_id')->unique(); // كود الطالب
            $table->string('name');
            $table->boolean('is_received')->default(false); // حالة الاستلام

            // الربط بالهيكل الأكاديمي
            $table->foreignId('university_id')->constrained()->onDelete('cascade');
            $table->foreignId('college_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');

            // المادة والسكشن (اختياري حسب التوزيع)
            $table->integer('section_number')->nullable();

            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('students'); }
};
