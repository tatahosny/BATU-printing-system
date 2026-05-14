<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('students', function (Blueprint $table) {
            // إضافة حقل يربط الطالب بالمندوب المسؤول عن سكشنه
            $table->foreignId('delegate_id')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['delegate_id']);
            $table->dropColumn('delegate_id');
        });
    }
};
