<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\University;
use App\Models\Student;
use App\Models\User;
use App\Models\Subject;
use App\Models\Inventory;
use App\Models\Department;
use App\Models\College;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. واجهة الأدمن (مدير النظام)
        if ($user->role === 'admin') {
            $totalStudents   = Student::count();
            $totalDelivered  = Student::where('is_received', true)->count();
            $stats = [
                'total_students'   => $totalStudents,
                'total_delivered'  => $totalDelivered,
                'total_subjects'   => Subject::count(),
                'active_delegates' => User::where('role', '!=', 'admin')->count(),
                'delivery_rate'    => $totalStudents > 0 ? round(($totalDelivered / $totalStudents) * 100) : 0,
            ];

            $university_stats = University::withCount(['colleges'])->get()->map(function($uni) {
                $total = Student::where('university_id', $uni->id)->count();
                $delivered = Student::where('university_id', $uni->id)->where('is_received', true)->count();
                return [
                    'id' => $uni->id,
                    'name' => $uni->name,
                    'colleges_count' => $uni->colleges_count,
                    'departments_count' => Department::whereHas('college', fn($q) => $q->where('university_id', $uni->id))->count(),
                    'subjects_count' => Subject::where('university_id', $uni->id)->count(),
                    'delivery_rate' => $total > 0 ? round(($delivered / $total) * 100) : 0
                ];
            });

            return Inertia::render('Dashboard', [
                'stats' => $stats,
                'university_stats' => $university_stats,
                'delegate_stats' => [] // جلب إحصائيات المناديب هنا إذا لزم الأمر
            ]);
        }

        // 2. واجهة المندوب
        $assignments = $user->assignments()->with(['batch', 'section'])->get();
        $batchIds    = $assignments->pluck('batch_id')->unique();
        
        $deliveryService = app(\App\Services\DeliveryService::class);

        $subjects = Subject::whereIn('batch_id', $batchIds)
            ->where('is_visible_to_section', true)
            ->get()
            ->map(function ($subject) use ($user, $deliveryService) {
                $studentQuery = $deliveryService->buildStudentQuery($subject, $user);
                
                $data = $subject->toArray();
                $data['total_students']     = (clone $studentQuery)->count();
                $data['delivered_students'] = (clone $studentQuery)->where('is_received', true)->count();
                $data['my_inventory']       = Inventory::where('user_id', $user->id)
                                                        ->where('subject_id', $subject->id)
                                                        ->value('quantity') ?? 0;
                
                // حساب ما يحتاجه المندوب من مندوب الدفعة (للأقسام فقط)
                $data['needed_from_batch'] = max(0, $data['total_students'] - $data['my_inventory'] - $data['delivered_students']);

                // إذا كان مندوب دفعة (ليس لديه سكشن محدد في أحد التعيينات)، يمكنه التوزيع
                $isBatchDelegate = $user->assignments()->where('batch_id', $subject->batch_id)->whereNull('section_id')->exists();
                $data['is_batch_delegate'] = $isBatchDelegate;

                if ($isBatchDelegate) {
                    // جلب مناديب السكاشن التابعين لهذه الدفعة
                    $data['section_delegates'] = User::whereHas('assignments', function($q) use ($subject) {
                        $q->where('batch_id', $subject->batch_id)->whereNotNull('section_id');
                    })->get()->map(function($secUser) use ($subject, $deliveryService) {
                        $secQuery = $deliveryService->buildStudentQuery($subject, $secUser);
                        $totalSec = (clone $secQuery)->count();
                        $currentInv = Inventory::where('user_id', $secUser->id)->where('subject_id', $subject->id)->value('quantity') ?? 0;
                        $delivered = (clone $secQuery)->where('is_received', true)->count();
                        
                        return [
                            'id' => $secUser->id,
                            'name' => $secUser->name,
                            'needed' => max(0, $totalSec - $currentInv - $delivered),
                            'current_inventory' => $currentInv
                        ];
                    })->filter(fn($u) => $u['needed'] > 0 || $u['current_inventory'] > 0)->values();
                }

                return $data;
            });

        return Inertia::render('Delegate/Dashboard', [
            'subjects'    => $subjects,
            'batch'       => $assignments->first()?->batch,
            'assignments' => $assignments,
        ]);
    }
}
