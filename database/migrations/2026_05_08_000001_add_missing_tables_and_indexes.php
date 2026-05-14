<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ──────────────────────────────────────────────
        // جدول transfers (كان مفقوداً تماماً)
        // ──────────────────────────────────────────────
        if (!Schema::hasTable('transfers')) {
            Schema::create('transfers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
                $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedInteger('quantity');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['subject_id', 'from_user_id']);
                $table->index(['subject_id', 'to_user_id']);
            });
        }

        // ──────────────────────────────────────────────
        // جدول inventory_transactions (audit trail)
        // ──────────────────────────────────────────────
        if (!Schema::hasTable('inventory_transactions')) {
            Schema::create('inventory_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
                $table->string('type'); // add_stock, distribute, return, reset, delivery, cancel
                $table->integer('quantity');
                $table->integer('before_qty')->default(0);
                $table->integer('after_qty')->default(0);
                $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index(['subject_id', 'type']);
                $table->index(['user_id', 'created_at']);
                $table->index('inventory_id');
            });
        }

        // ──────────────────────────────────────────────
        // إضافة initial_quantity لجدول inventories
        // ──────────────────────────────────────────────
        if (Schema::hasTable('inventories') && !Schema::hasColumn('inventories', 'initial_quantity')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->integer('initial_quantity')->default(0)->after('quantity');
            });
        }

        // ──────────────────────────────────────────────
        // إضافة description لجدول operation_logs
        // ──────────────────────────────────────────────
        if (Schema::hasTable('operation_logs') && !Schema::hasColumn('operation_logs', 'description')) {
            Schema::table('operation_logs', function (Blueprint $table) {
                $table->text('description')->nullable()->after('action_type');
            });
        }

        // ──────────────────────────────────────────────
        // soft deletes للجداول الأساسية
        // ──────────────────────────────────────────────
        if (Schema::hasTable('students') && !Schema::hasColumn('students', 'deleted_at')) {
            Schema::table('students', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('inventories') && !Schema::hasColumn('inventories', 'deleted_at')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // ──────────────────────────────────────────────
        // indexes إضافية لتحسين الأداء
        // ──────────────────────────────────────────────
        if (Schema::hasTable('students')) {
            try {
                Schema::table('students', function (Blueprint $table) {
                    $table->index('delegate_id');
                    $table->index(['subject_id', 'is_received']);
                    $table->index(['batch_id', 'subject_id']);
                });
            } catch (\Exception $e) {
                // Index may already exist — skip
            }
        }

        if (Schema::hasTable('inventories')) {
            try {
                Schema::table('inventories', function (Blueprint $table) {
                    $table->index(['user_id', 'subject_id']);
                });
            } catch (\Exception $e) {
                // Index may already exist — skip
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('transfers');
    }
};
