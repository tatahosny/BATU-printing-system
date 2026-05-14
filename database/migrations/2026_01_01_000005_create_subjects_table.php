<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('term', ['first', 'second', 'summer'])->default('first');

            // صلاحيات الرؤية
            $table->boolean('is_visible_to_general')->default(true);
            $table->boolean('is_visible_to_section')->default(true);

            // الربط الهرمي الكامل
            $table->foreignId('university_id')->constrained()->onDelete('cascade');
            $table->foreignId('college_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('subjects'); }
};
