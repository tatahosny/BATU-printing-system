<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_logs', function (Blueprint $table) {
            $table->id();
            // المندوب الذي قام بالعملية
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // الطالب الذي تمت عليه العملية
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            // نوع العملية: 'delivery' (تسليم), 'return' (مرتجع), 'cancel' (إلغاء)
            $table->string('action_type');
            // بيانات تقنية للأمان
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps(); // يسجل وقت العملية تلقائياً في created_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_logs');
    }
};
