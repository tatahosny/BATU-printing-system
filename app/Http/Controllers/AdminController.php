<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Student;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\User;
use App\Models\OperationLog;
use App\Services\InventoryService;
use App\Http\Requests\AddMainStockRequest;
use App\Imports\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected \App\Services\DeliveryService $deliveryService
    ) {}

    /**
     * لوحة تحكم الأدمن الرئيسية
     */
    public function index()
    {
        $stats = [
            'total_students'    => Student::count(),
            'total_delivered'   => Student::delivered()->count(),
            'total_subjects'    => Subject::count(),
            'active_delegates'  => User::delegates()->count(),
            'delivery_rate'     => Student::count() > 0
                ? round((Student::delivered()->count() / Student::count()) * 100)
                : 0,
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats'         => $stats,
            'academic_tree' => \App\Models\University::with('colleges.departments.batches')->get(),
        ]);
    }

    /**
     * إحصائيات المناديب
     */
    public function getDelegatesStats()
    {
        $rows = [];

        User::delegates()
            ->with(['inventories.subject'])
            ->get()
            ->each(function ($delegate) use (&$rows) {
                if ($delegate->inventories->isEmpty()) {
                    $rows[] = [
                        'delegate_name'          => $delegate->name,
                        'subject_name'           => '—',
                        'total_section_students' => 0,
                        'delivered_count'        => 0,
                        'current_inventory'      => 0,
                    ];
                    return;
                }

                foreach ($delegate->inventories as $inv) {
                    if (!$inv->subject) continue;

                    // استخدام الـ DeliveryService لحساب الأرقام بدقة بناءً على التعيينات
                    $query = $this->deliveryService->buildStudentQuery($inv->subject, $delegate);
                    
                    $totalStudents = (clone $query)->count();
                    $delivered     = (clone $query)->delivered()->count();

                    $rows[] = [
                        'delegate_name'          => $delegate->name,
                        'subject_name'           => $inv->subject->name,
                        'total_section_students' => $totalStudents,
                        'delivered_count'        => $delivered,
                        'current_inventory'      => $inv->quantity,
                    ];
                }
            });

        return Inertia::render('Admin/DelegatesStats', ['stats' => $rows]);
    }

    /**
     * لوحة تحكم المخزون الرئيسية
     */
    public function inventoryIndex()
    {
        $admin = auth()->user();

        $mainStore = Inventory::mainStore()
            ->with(['subject.batch.department.college.university'])
            ->get()
            ->map(fn($inv) => array_merge($inv->toArray(), [
                'delivered_count' => Student::where('subject_id', $inv->subject_id)->delivered()->count(),
                'total_students'  => Student::where('subject_id', $inv->subject_id)->count(),
            ]));

        $delegateInventories = Inventory::delegateInventories()
            ->with(['user', 'subject.batch'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($inv) => array_merge($inv->toArray(), [
                'delivered_count' => Student::where('delegate_id', $inv->user_id)
                                            ->where('subject_id', $inv->subject_id)
                                            ->delivered()->count(),
            ]));

        $stats = [
            'total_stock'       => Inventory::sum('quantity'),
            'main_store_stock'  => Inventory::mainStore()->sum('quantity'),
            'delegates_stock'   => Inventory::delegateInventories()->sum('quantity'),
            'total_delivered'   => Student::delivered()->count(),
            'total_students'    => Student::count(),
            'out_of_stock'      => Inventory::delegateInventories()->where('quantity', '<=', 0)->count(),
        ];

        $recentTransactions = InventoryTransaction::with(['user', 'subject', 'fromUser', 'toUser'])
            ->latest()
            ->limit(20)
            ->get();

        return Inertia::render('Admin/Inventory/Index', [
            'main_store'          => $mainStore,
            'delegate_inventories'=> $delegateInventories,
            'stats'               => $stats,
            'recent_transactions' => $recentTransactions,
            'all_delegates'       => User::delegates()->with('assignments')->get(),
            'all_subjects'        => Subject::with('batch.department.college.university')->get(),
            'universities_tree'   => \App\Models\University::with('colleges.departments.batches')->get(),
        ]);
    }

    /**
     * إضافة كمية للمخزن الرئيسي
     */
    public function addMainStock(AddMainStockRequest $request)
    {
        $subject = Subject::findOrFail($request->subject_id);
        $result  = $this->inventoryService->addStock(auth()->user(), $subject, $request->quantity);

        return back()->with('success', "تم إضافة {$request->quantity} نسخة للمخزن الرئيسي - {$subject->name}");
    }

    /**
     * توزيع كمية على مندوب
     */
    public function distributeStock(Request $request)
    {
        $data = $request->validate([
            'subject_id'   => 'required|integer|exists:subjects,id',
            'delegate_id'  => 'required|integer|exists:users,id',
            'quantity'     => 'required|integer|min:1',
        ]);

        $delegate = User::findOrFail($data['delegate_id']);
        $subject  = Subject::findOrFail($data['subject_id']);

        $result = $this->inventoryService->distributeToDelegate(
            auth()->user(), $delegate, $subject, $data['quantity']
        );

        $key = $result['success'] ? 'success' : 'error';
        return back()->with($key, $result['message']);
    }

    /**
     * رفع الشيت الرئيسي للدفعة
     */
    public function uploadMasterList(Request $request)
    {
        $request->validate([
            'sheets'   => 'required|array|min:1|max:5',
            'sheets.*' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'batch_id' => 'required|exists:batches,id',
        ]);

        try {
            $batch = \App\Models\Batch::findOrFail($request->batch_id);
            $hierarchy = [
                'university_id' => $batch->university_id,
                'college_id'    => $batch->college_id,
                'batch_id'      => $batch->id,
                'subject_id'    => null,
            ];

            foreach ($request->file('sheets') as $sheet) {
                // تنظيف التكرارات قبل كل ملف
                $this->cleanupDuplicates($hierarchy);
                Excel::import(new StudentsImport($hierarchy), $sheet);
            }
            return back()->with('success', 'تم رفع الكشوفات الرئيسية للدفعة بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في الرفع: ' . $e->getMessage());
        }
    }

    /**
     * رفع شيتات المادة
     */
    public function uploadMasterSheet(Request $request, Subject $subject)
    {
        $request->validate([
            'sheets'   => 'required|array|min:1|max:5',
            'sheets.*' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $hierarchy = [
                'university_id' => $subject->university_id,
                'college_id'    => $subject->college_id,
                'department_id' => $subject->department_id,
                'batch_id'      => $subject->batch_id,
                'section_id'    => $subject->section_id,
                'subject_id'    => $subject->id,
            ];

            $totalImported = 0;
            $totalSkipped = 0;

            foreach ($request->file('sheets') as $sheet) {
                $this->cleanupDuplicates($hierarchy);
                $importer = new StudentsImport($hierarchy);
                Excel::import($importer, $sheet);
                $totalImported += $importer->getImported();
                $totalSkipped += $importer->getSkipped();
            }
            return back()->with('success', "تم رفع الكشوف بنجاح! تم قبول {$totalImported} طالب وتجاهل {$totalSkipped} (غير موجودين في الكشف الرئيسي أو مكررين).");
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في الرفع: ' . $e->getMessage());
        }
    }

    /**
     * وظيفة مساعدة لتنظيف التكرارات مع الحفاظ على حالة التسليم
     */
    private function cleanupDuplicates(array $hierarchy)
    {
        $query = Student::where('batch_id', $hierarchy['batch_id']);
        
        if (isset($hierarchy['subject_id'])) {
            $query->where('subject_id', $hierarchy['subject_id']);
        } else {
            $query->whereNull('subject_id');
        }

        $duplicateIds = (clone $query)
            ->groupBy('student_external_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('student_external_id');

        foreach ($duplicateIds as $extId) {
            $recs = (clone $query)->where('student_external_id', $extId)->get();
            $master = $recs->where('is_received', true)->first() ?? $recs->first();
            (clone $query)->where('student_external_id', $extId)->where('id', '!=', $master->id)->forceDelete();
        }
    }

    /**
     * تعديل كمية عهدة يدوياً
     */
    public function updateQuantity(Request $request, Inventory $inventory)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
            'reason'   => 'nullable|string|max:500',
        ]);

        $result = $this->inventoryService->adjustQuantity(
            auth()->user(), $inventory, $request->quantity, $request->reason ?? ''
        );

        $key = $result['success'] ? 'success' : 'error';
        return back()->with($key, $result['message']);
    }

    /**
     * تصفير عهدة مندوب
     */
    public function resetInventory(Request $request, Inventory $inventory)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $result = $this->inventoryService->resetInventory(
            auth()->user(), $inventory, $request->reason ?? ''
        );

        $key = $result['success'] ? 'success' : 'error';
        return back()->with($key, $result['message']);
    }

    /**
     * تصفير بيانات مادة بالكامل
     */
    public function resetSubjectData(Subject $subject)
    {
        DB::transaction(function () use ($subject) {
            Student::where('subject_id', $subject->id)->delete();
            Inventory::where('subject_id', $subject->id)->update(['quantity' => 0, 'initial_quantity' => 0]);
        });

        return back()->with('success', "تم تصفير كافة بيانات المادة: {$subject->name}");
    }

    /**
     * سجل المعاملات
     */
    public function transactionHistory(Request $request)
    {
        $transactions = InventoryTransaction::with(['user', 'subject', 'fromUser', 'toUser'])
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Admin/Inventory/Transactions', [
            'transactions' => $transactions,
            'subjects'     => Subject::select('id', 'name')->get(),
            'filters'      => $request->only(['subject_id', 'type']),
        ]);
    }

    /**
     * سجل العمليات
     */
    public function logs()
    {
        $logs = OperationLog::with(['user', 'student.subject'])
            ->latest()
            ->paginate(100);

        return Inertia::render('Admin/Logs', ['logs' => $logs]);
    }
}
