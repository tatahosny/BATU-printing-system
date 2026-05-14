<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. الجامعات
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // 2. الكليات (مرتبطة بجامعة)
        Schema::create('colleges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        // 3. الأقسام (مرتبطة بكلية)
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('college_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        // 4. الدفعات / السنوات (مرتبطة بقسم)
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('name'); // مثال: الفرقة الأولى، سنة 2026
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('batches');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('colleges');
        Schema::dropIfExists('universities');
    }
};
