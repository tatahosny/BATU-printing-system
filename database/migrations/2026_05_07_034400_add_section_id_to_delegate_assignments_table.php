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
        Schema::table('delegate_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('delegate_assignments', 'section_id')) {
                $table->foreignId('section_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('delegate_assignments', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->constrained()->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delegate_assignments', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
        });
    }
};
