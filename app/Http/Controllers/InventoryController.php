<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Student;
use App\Models\Inventory;
use App\Models\OperationLog;
use App\Services\DeliveryService;
use App\Imports\SectionMatchingImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    private string $engineId = 'MH-SYS-CORE-2024';

    public function __construct(protected DeliveryService $deliveryService) {}

    /**
     * عرض صفحة التسليم
     */
    public function showDeliveryPage(Subject $subject, Request $request)
    {
        $user   = auth()->user();
        // IDOR Protection
        if (!$user->isAdmin() && !$user->assignments()->where('batch_id', $subject->batch_id)->exists()) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بالوصول لصفحة التسليم لهذه المادة');
        }

        $search = $request->input('search');

        $query = $this->deliveryService->buildStudentQuery($subject, $user);

        $students = $query
            ->when($search, fn($q) => $q->where(function ($inner) use ($search) {
                $inner->where('student_external_id', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
            }))
            ->orderBy('is_received', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(50)
            ->withQueryString();

        $userInventory = Inventory::forUser($user->id)->forSubject($subject->id)->value('quantity') ?? 0;

        $batchDelegate = \App\Models\User::generalDelegates()
            ->whereHas('assignments', fn($q) => $q->where('batch_id', $subject->batch_id)->whereNull('section_id'))
            ->first();

        $recentLogs = OperationLog::where('user_id', $user->id)
            ->whereHas('student', fn($q) => $q->where('subject_id', $subject->id))
            ->with('student:id,name,student_external_id')
            ->latest()
            ->limit(10)
            ->get();

        // إحصائيات سريعة
        $statsQuery = $this->deliveryService->buildStudentQuery($subject, $user);
        $stats = [
            'total'     => (clone $statsQuery)->count(),
            'delivered' => (clone $statsQuery)->delivered()->count(),
        ];
        $stats['remaining'] = $stats['total'] - $stats['delivered'];
        $stats['percentage'] = $stats['total'] > 0
            ? round(($stats['delivered'] / $stats['total']) * 100)
            : 0;

        return Inertia::render('Delegate/Delivery', [
            'subject'        => $subject->load('batch'),
            'students'       => $students,
            'user_inventory' => (int) $userInventory,
            'batch_delegate' => $batchDelegate,
            'recent_logs'    => $recentLogs,
            'stats'          => $stats,
            'filters'        => ['search' => $search],
        ]);
    }

    /**
     * تبديل حالة الاستلام
     */
    public function toggleStatus(Subject $subject, Student $student, Request $request)
    {
        $user       = auth()->user();
        // IDOR Protection
        if (!$user->isAdmin() && !$user->assignments()->where('batch_id', $subject->batch_id)->exists()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك'], 403);
        }

        $actionType = $request->input('action', 'delivery');

        if ($actionType === 'delivery') {
            $result = $this->deliveryService->deliverToStudent($subject, $student, $user);
        } else {
            $result = $this->deliveryService->cancelDelivery($subject, $student, $user);
        }

        $key = $result['success'] ? 'success' : 'error';
        return back()->with($key, $result['message']);
    }

    /**
     * رفع شيت السكشن
     */
    public function uploadSection(Request $request, Subject $subject)
    {
        $request->validate([
            'sheet' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            Excel::import(new SectionMatchingImport($subject->id), $request->file('sheet'));
            return back()->with('success', 'تمت مطابقة الطلاب مع الشيت الرئيسي وتحديث العهدة آلياً.');
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في المعالجة: ' . $e->getMessage());
        }
    }
}
