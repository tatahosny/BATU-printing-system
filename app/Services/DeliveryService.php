<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\OperationLog;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DeliveryService
{
    /**
     * بناء استعلام الطلاب بناءً على الدور والتعيين
     */
    public function buildStudentQuery(Subject $subject, User $user): Builder
    {
        // الاستعلام الأساسي: الطلاب التابعين لهذه الدفعة والمادة
        $query = Student::where('batch_id', $subject->batch_id)
            ->where(function ($q) use ($subject) {
                $q->where('subject_id', $subject->id)
                  ->orWhere(function ($sq) use ($subject) {
                      $sq->whereNull('subject_id')
                         ->whereNotExists(function ($sub) use ($subject) {
                             $sub->select(DB::raw(1))
                                 ->from('students as s2')
                                 ->whereColumn('s2.student_external_id', 'students.student_external_id')
                                 ->where('s2.subject_id', $subject->id)
                                 ->whereNull('s2.deleted_at');
                         });
                  });
            });

        // الأدمن يرى الكل
        if ($user->isAdmin()) {
            return $query;
        }

        // جلب تعيينات المندوب لهذه الدفعة
        $assignments = $user->assignments()
            ->where('batch_id', $subject->batch_id)
            ->with('section')
            ->get();

        if ($assignments->isEmpty()) {
            // لا تعيينات = لا رؤية
            return $query->whereRaw('1 = 0');
        }

        // تصفية بناءً على نوع التعيين
        $query->where(function ($q) use ($assignments, $user) {
            foreach ($assignments as $assignment) {
                if ($assignment->section_id && $assignment->section) {
                    $section = $assignment->section;
                    
                    // 1. مطابقة مباشرة بالـ section_id (إذا كان الطالب مرتبطاً بالسكشن)
                    $q->orWhere('section_id', $section->id);

                    // 2. مطابقة بالنطاق الرقمي (إذا وجد النطاق في تعريف السكشن)
                    if ($section->range_start && $section->range_end) {
                        $start = (int) preg_replace('/\D/', '', (string)$section->range_start);
                        $end   = (int) preg_replace('/\D/', '', (string)$section->range_end);
                        if ($start > 0 && $end > 0) {
                            $q->orWhereRaw("CAST(REPLACE(student_external_id, ' ', '') AS INTEGER) BETWEEN ? AND ?", [$start, $end]);
                        }
                    }
                } else {
                    // مندوب دفعة عام — يرى كل طلاب هذه الدفعة في هذه المادة
                    // نستخدم orWhereNotNull('id') لضمان اختيار جميع طلاب الدفعة
                    $q->orWhereNotNull('id');
                }
            }
            // دائماً يرى طلابه المخصصين له يدوياً (عن طريق الإدمن)
            $q->orWhere('delegate_id', $user->id);
            // وإذا كان الطالب مسلماً بواسطته، يجب أن يراه دائماً
            $q->orWhere('delivered_by', $user->id);
        });

        return $query;
    }

    /**
     * تسليم كتاب لطالب
     */
    public function deliverToStudent(Subject $subject, Student $student, User $deliveredBy): array
    {
        return DB::transaction(function () use ($subject, $student, $deliveredBy) {
            // Security: Verify student belongs to subject batch
            if ($student->batch_id !== $subject->batch_id) {
                return ['success' => false, 'message' => 'هذا الطالب غير تابع لهذه الدفعة'];
            }

            // Security: Verify assignment scope (Delegate can only deliver to students in their assigned scope)
            if (!$deliveredBy->isAdmin()) {
                $scopedQuery = $this->buildStudentQuery($subject, $deliveredBy);
                // Check if this specific student is in the scoped query
                if (!$scopedQuery->where('students.id', $student->id)->exists()) {
                    return ['success' => false, 'message' => 'هذا الطالب خارج نطاق صلاحياتك المعتمدة'];
                }
            }

            // إذا كان سجل الطالب عاماً (subject_id = null)، أنشئ سجل خاص بهذه المادة
            $student = $this->resolveStudentForSubject($subject, $student);

            if ($student->is_received) {
                return ['success' => false, 'message' => 'الطالب استلم هذه المادة بالفعل'];
            }

            // تحقق من الرصيد إن لم يكن أدمن
            if (!$deliveredBy->isAdmin()) {
                $inventory = Inventory::forUser($deliveredBy->id)
                    ->forSubject($subject->id)
                    ->lockForUpdate()
                    ->first();

                if (!$inventory || $inventory->quantity <= 0) {
                    return ['success' => false, 'message' => 'رصيدك الحالي صفر. لا يمكن التسليم.'];
                }

                $inventory->decrement('quantity', 1);
            }

            $student->update([
                'is_received' => true,
                'received_at' => now(),
                'delivered_by' => $deliveredBy->id,
                'delegate_id'  => $deliveredBy->isDelegate() ? $deliveredBy->id : $student->delegate_id,
            ]);

            $this->logDelivery($deliveredBy->id, $student->id, 'delivery');

            return ['success' => true, 'message' => "✅ تم التسليم للطالب: {$student->name}"];
        });
    }

    /**
     * إلغاء أو استرجاع تسليم
     */
    public function cancelDelivery(Subject $subject, Student $student, User $cancelledBy): array
    {
        return DB::transaction(function () use ($subject, $student, $cancelledBy) {
            // Security: Verify student belongs to subject batch
            if ($student->batch_id !== $subject->batch_id) {
                return ['success' => false, 'message' => 'هذا الطالب غير تابع لهذه الدفعة'];
            }

            // Security: Verify scope (Can only cancel if they delivered it or are admin/assigned)
            if (!$cancelledBy->isAdmin()) {
                $scopedQuery = $this->buildStudentQuery($subject, $cancelledBy);
                if (!$scopedQuery->where('students.id', $student->id)->exists() && $student->delivered_by !== $cancelledBy->id) {
                    return ['success' => false, 'message' => 'غير مصرح لك بإلغاء تسليم هذا الطالب'];
                }
            }

            $student = $this->resolveStudentForSubject($subject, $student);

            if (!$student->is_received) {
                return ['success' => false, 'message' => 'الطالب لم يستلم المادة أصلاً'];
            }

            // أعد النسخة للعهدة
            $inventory = Inventory::forUser($cancelledBy->id)
                ->forSubject($subject->id)
                ->first();

            if ($inventory) {
                $inventory->increment('quantity', 1);
            }

            $student->update([
                'is_received' => false,
                'received_at' => null,
                'delivered_by' => null,
            ]);

            $this->logDelivery($cancelledBy->id, $student->id, 'cancel');

            return ['success' => true, 'message' => "تم إلغاء التسليم وإعادة النسخة لعهدتك"];
        });
    }

    /**
     * حل مشكلة الطالب العام (subject_id = null) بإنشاء سجل خاص بالمادة
     */
    private function resolveStudentForSubject(Subject $subject, Student $student): Student
    {
        if (!is_null($student->subject_id)) {
            return $student;
        }

        $actual = Student::where('student_external_id', $student->student_external_id)
            ->where('subject_id', $subject->id)
            ->first();

        if (!$actual) {
            $actual = $student->replicate();
            $actual->subject_id = $subject->id;
            $actual->is_received = false;
            $actual->received_at = null;
            $actual->delivered_by = null;
            $actual->save();
        }

        return $actual;
    }

    /**
     * تسجيل عملية التسليم في Operation Logs
     */
    private function logDelivery(int $userId, int $studentId, string $type): void
    {
        OperationLog::create([
            'user_id'     => $userId,
            'student_id'  => $studentId,
            'action_type' => $type,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}
