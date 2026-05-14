<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DelegateController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AcademicManagerController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

/**
 * System Core Routes Configuration
 * Ref: 4d 6f 73 74 61 66 61 20 48 6f 73 6e 79
 */

/*
|--------------------------------------------------------------------------
| Web Routes - Printing Distribution & Tracking System
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => redirect()->route('login'));

// ─────────────────────────────────────────────────────────────
// مسارات مشتركة (المستخدم مسجّل الدخول)
// ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // لوحة التحكم
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // الملف الشخصي
    Route::get('/profile',       [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',     [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',    [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ─── التسليم (المندوب + الأدمن) ───────────────────────────
    Route::prefix('delivery')->name('delivery.')->group(function () {
        Route::get('/{subject}',                          [InventoryController::class, 'showDeliveryPage'])->name('show');
        Route::post('/{subject}/student/{student}/toggle',[InventoryController::class, 'toggleStatus'])->name('students.toggle');
        // رفع كشف السكشن — الأدمن فقط مع Rate Limiting
        Route::post('/{subject}/upload-section',          [InventoryController::class, 'uploadSection'])
            ->name('upload-section')
            ->middleware(['can:admin-access', 'throttle:uploads']);
    });

    // ─── تحويل العُهدة ─────────────────────────────────────────
    Route::middleware(['can:transfer-access'])->group(function () {
        Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
    });

    // ─────────────────────────────────────────────────────────────
    // مسارات إدارية مشتركة (Admin + Batch Delegate)
    // ─────────────────────────────────────────────────────────────
    Route::middleware('can:batch-delegate-access')->group(function () {

        Route::prefix('admin/delegates')->name('admin.delegates.')->group(function () {
            Route::get('/', [DelegateController::class, 'index'])
                ->middleware('can:admin-access')->name('index');
            Route::get('/create', [DelegateController::class, 'create'])
                ->middleware('can:admin-access')->name('create');
            Route::post('/', [DelegateController::class, 'store'])
                ->middleware('can:admin-access')->name('store');
            Route::get('/{user}/activity', [DelegateController::class, 'activityLog'])
                ->middleware('can:admin-access')->name('activity');
            // تعيين النطاق — Admin + General Delegate
            Route::post('/{user}/assign', [DelegateController::class, 'assignScope'])->name('assign');
        });

        // الفرقة — للعرض للـ Batch Delegate أيضاً
        Route::get('/admin/academic/batch/{batch}', [AcademicManagerController::class, 'showBatch'])
            ->name('admin.academic.batch.show');

        // طلاب المادة — batch delegate access
        Route::get('/admin/academic/subject/{subject}/students', [AcademicManagerController::class, 'showSubjectStudents'])
            ->name('admin.academic.subject.students');
    });

    // ─────────────────────────────────────────────────────────────
    // مسارات الأدمن فقط
    // ─────────────────────────────────────────────────────────────
    Route::middleware('can:admin-access')->group(function () {

        // الإدارة الأكاديمية
        Route::prefix('admin/academic')->name('admin.academic.')->group(function () {
            Route::get('/universities',                     [AcademicManagerController::class, 'index'])->name('index');
            Route::get('/university/{university}',          [AcademicManagerController::class, 'showUniversity'])->name('university.show');
            Route::post('/upsert',                          [AcademicManagerController::class, 'upsert'])->name('upsert');
            Route::delete('/destroy/{id}/{type}',           [AcademicManagerController::class, 'destroy'])->name('destroy');
            Route::post('/subject/{subject}/toggle',        [AcademicManagerController::class, 'toggleSubject'])->name('subject.toggle');
            Route::get('/subject/{subject}/log',            [AcademicManagerController::class, 'subjectLog'])->name('subject.log');
            Route::post('/upload-material-sheet',           [AcademicManagerController::class, 'uploadMaterialSheet'])
                ->name('upload-sheet')->middleware('throttle:uploads');
            Route::post('/batch/{batch}/auto-assign',       [AcademicManagerController::class, 'autoAssignByRange'])->name('batch.auto-assign');
            // تنزيل تيمبليت + رفع كشف طلاب الدفعة
            Route::get('/batch/{batch}/download-template',  [AcademicManagerController::class, 'downloadBatchTemplate'])->name('batch.download-template');
            Route::post('/batch/{batch}/upload-students',   [AcademicManagerController::class, 'uploadBatchStudents'])->name('batch.upload-students')->middleware('throttle:uploads');
        });

        // المخزون والعُهد
        Route::prefix('admin/inventory')->name('admin.inventory.')->group(function () {
            Route::get('/',                                 [AdminController::class, 'inventoryIndex'])->name('index');
            Route::post('/upload-master-list',              [AdminController::class, 'uploadMasterList'])
                ->name('upload-master-list')->middleware('throttle:uploads');
            Route::post('/upload-master/{subject}',         [AdminController::class, 'uploadMasterSheet'])
                ->name('upload-master')->middleware('throttle:uploads');
            Route::post('/main/add',                        [AdminController::class, 'addMainStock'])->name('add-main');
            Route::post('/distribute',                      [AdminController::class, 'distributeStock'])->name('distribute');
            Route::patch('/update-qty/{inventory}',         [AdminController::class, 'updateQuantity'])->name('update-qty');
            Route::post('/reset-qty/{inventory}',           [AdminController::class, 'resetInventory'])->name('reset-qty');
            Route::post('/reset-subject/{subject}',         [AdminController::class, 'resetSubjectData'])->name('reset-subject');
            Route::get('/transactions',                     [AdminController::class, 'transactionHistory'])->name('transactions');
        });

        // التقارير والإحصائيات
        Route::prefix('admin/reports')->name('admin.reports.')->group(function () {
            Route::get('/delegates-stats', [AdminController::class, 'getDelegatesStats'])->name('delegates-stats');
            Route::get('/logs',            [\App\Http\Controllers\Admin\LogController::class, 'index'])->name('logs');
        });
    });
});

require __DIR__.'/auth.php';
