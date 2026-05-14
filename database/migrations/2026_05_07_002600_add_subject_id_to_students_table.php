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
        Schema::table('students', function (Blueprint $table) {
            // Drop unique index if it exists
            // Usually named 'students_student_external_id_unique'
            $table->dropUnique(['student_external_id']);
            
            $table->foreignId('subject_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            
            // Add composite unique to prevent duplicates per subject
            $table->unique(['student_external_id', 'subject_id']);
            
            // Add some missing fields for better tracking
            $table->timestamp('received_at')->nullable()->after('is_received');
            $table->foreignId('delivered_by')->nullable()->after('received_at')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['delivered_by']);
            $table->dropUnique(['student_external_id', 'subject_id']);
            $table->dropColumn(['subject_id', 'received_at', 'delivered_by']);
            
            $table->unique('student_external_id');
        });
    }
};
