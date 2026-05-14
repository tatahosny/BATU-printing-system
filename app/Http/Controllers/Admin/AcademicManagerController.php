<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\College;
use App\Models\Department;
use App\Models\Batch;
use App\Models\Subject;
use App\Models\Section;
use App\Models\Material;
use App\Models\Student;
use App\Models\User;
use App\Models\MasterStudentList;
use App\Models\MaterialStudent;
use App\Models\Inventory;
use App\Imports\GenericImport;
use App\Imports\BatchStudentsImport;
use App\Exports\BatchTemplateExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class AcademicManagerController extends Controller
{
    /**
     * العرض الأساسي لجميع الجامعات
     */
    public function index()
    {
        return Inertia::render('Admin/Academic/Index', [
            'universities' => University::withCount('colleges')->get()->map(function($uni) {
                $uni->subjects_count = Subject::where('university_id', $uni->id)->count();
                $uni->students_count = Student::where('university_id', $uni->id)->whereNull('subject_id')->count();
                return $uni;
            })
        ]);
    }

    /**
     * عرض تفاصيل جامعة معينة (الكليات والتبعية الهرمية)
     */
    public function showUniversity(University $university)
    {
        return Inertia::render('Admin/Academic/UniversityDetails', [
            'university' => $university->load('colleges.departments.batches.subjects'),
        ]);
    }

    /**
     * عرض تفاصيل فرقة (السكاشن والمواد)
     */
    public function showBatch(Batch $batch, Request $request)
    {
        $user = auth()->user();
        // IDOR Protection: Check if delegate is assigned to this batch
        if (!$user->isAdmin() && !$user->assignments()->where('batch_id', $batch->id)->exists()) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بالوصول لبيانات هذه الدفعة');
        }

        $batch->load(['sections.assignments.user', 'university', 'department.college.university']);
        
        // --- خطوة "المزامنة الكاملة" المحسنة (Improved SQL Sync) ---
        // مزامنة السكاشن والمناديب لجميع سجلات المواد بناءً على الكشف الرئيسي باستعلام واحد سريع
        DB::statement("
            UPDATE students 
            SET 
                section_id = (
                    SELECT m.section_id 
                    FROM students m 
                    WHERE REPLACE(m.student_external_id, ' ', '') = REPLACE(students.student_external_id, ' ', '') 
                    AND m.batch_id = students.batch_id 
                    AND m.subject_id IS NULL 
                    LIMIT 1
                ),
                delegate_id = (
                    SELECT m.delegate_id 
                    FROM students m 
                    WHERE REPLACE(m.student_external_id, ' ', '') = REPLACE(students.student_external_id, ' ', '') 
                    AND m.batch_id = students.batch_id 
                    AND m.subject_id IS NULL 
                    LIMIT 1
                )
            WHERE batch_id = ? AND subject_id IS NOT NULL
        ", [$batch->id]);

        $activeSubjects = Subject::where('batch_id', $batch->id)->get();
        
        $sections = $batch->sections->map(function($section) use ($activeSubjects, $batch) {
            $arr = $section->toArray();
            $arr['master_students_count'] = Student::where('batch_id', $batch->id)->whereNull('subject_id')->where('section_id', $section->id)->count();
            
            $supervisorId = $section->assignments->first()?->user_id;
            $arr['delegate_received_total'] = 0;
            $arr['delegate_current_inventory'] = 0;
            
            if ($supervisorId) {
                $inventoryStats = Inventory::where('user_id', $supervisorId)
                    ->whereIn('subject_id', $activeSubjects->pluck('id'))
                    ->selectRaw('sum(initial_quantity) as total_received, sum(quantity) as current_qty')
                    ->first();
                $arr['delegate_received_total'] = (int) ($inventoryStats->total_received ?? 0);
                $arr['delegate_current_inventory'] = (int) ($inventoryStats->current_qty ?? 0);
            }

            $arr['subjects_stats'] = $activeSubjects->map(function($sub) use ($section, $batch) {
                $stats = Student::where('batch_id', $batch->id)->where('subject_id', $sub->id)->where('section_id', $section->id)
                    ->selectRaw('count(*) as total, sum(case when is_received = 1 then 1 else 0 end) as delivered')
                    ->first();
                return [
                    'id' => $sub->id, 'name' => $sub->name, 'total' => (int) $stats->total, 'delivered' => (int) $stats->delivered,
                    'remaining' => max(0, (int) $stats->total - (int) $stats->delivered)
                ];
            })->values()->toArray();

            $arr['total_students_count'] = collect($arr['subjects_stats'])->sum('total');
            $arr['delivered_students_count'] = collect($arr['subjects_stats'])->sum('delivered');
            $arr['remaining_students_count'] = max(0, $arr['total_students_count'] - $arr['delivered_students_count']);
            
            return $arr;
        })->values()->toArray();

        $subjects = $activeSubjects->map(function($subject) use ($batch) {
            $arr = $subject->toArray();
            $arr['distributed_inventory'] = (int) Inventory::where('subject_id', $subject->id)->whereHas('user', fn($q) => $q->where('role', '!=', 'admin'))->sum('quantity');
            $arr['main_inventory'] = (int) Inventory::where('subject_id', $subject->id)->whereHas('user', fn($q) => $q->where('role', 'admin'))->sum('quantity');
            $arr['actual_delivered'] = (int) Student::where('subject_id', $subject->id)->where('is_received', true)->count();
            $arr['real_total_students'] = (int) Student::where('subject_id', $subject->id)->count();
            return $arr;
        })->values();

        $batchStats = [
            'master_students_count' => Student::where('batch_id', $batch->id)->whereNull('subject_id')->count(),
            'total_expected_operations' => Student::where('batch_id', $batch->id)->whereNotNull('subject_id')->count(),
            'total_distributed_inventory' => Inventory::whereIn('subject_id', $activeSubjects->pluck('id'))->whereHas('user', fn($q) => $q->where('role', '!=', 'admin'))->sum('quantity'),
            'total_actual_delivered' => Student::where('batch_id', $batch->id)->whereNotNull('subject_id')->where('is_received', true)->count(),
        ];

        $potential_supervisors = User::where('role', 'section_delegate')->get();

        $search = $request->input('search');
        $batch_students = Student::where('batch_id', $batch->id)
            ->whereNull('subject_id')
            ->when($search, function($q) use ($search) {
                $q->where(function($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                          ->orWhere('student_external_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(100)
            ->withQueryString();

        return Inertia::render('Admin/Academic/BatchDetails', [
            'batch' => array_merge($batch->toArray(), [
                'sections' => $sections,
                'subjects' => $subjects,
                'stats' => $batchStats
            ]),
            'potential_supervisors' => $potential_supervisors,
            'batch_students' => $batch_students,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * إضافة أو تحديث (جامعة، كلية، قسم، فرقة، سكشن، مادة)
     */
    public function upsert(Request $request)
    {
        $data = $request->validate([
            'id'            => 'nullable|integer',
            'name'          => 'required|string|max:255',
            'type'          => 'required|in:university,college,department,batch,section,subject',
            'parent_id'     => 'nullable|integer',
            'university_id' => 'nullable|integer',
            'college_id'    => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'batch_id'      => 'nullable|integer',
            'section_id'    => 'nullable|integer',
            'term'          => 'nullable|in:1,2',
            'range_start'   => 'nullable|string|max:50',
            'range_end'     => 'nullable|string|max:50',
        ]);

        $model = $this->getModelByType($data['type']);

        $item = $model::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $this->prepareData($data)
        );

        return back()->with('success', 'تم حفظ البيانات بنجاح');
    }

    /**
     * تفعيل أو تعطيل ظهور المادة للسكاشن
     */
    public function toggleSubject(Subject $subject)
    {
        $subject->update(['is_visible_to_section' => !$subject->is_visible_to_section]);
        return back()->with('success', 'تم تغيير حالة ظهور المادة');
    }

    /**
     * الحذف الشامل لأي مستوى
     */
    public function destroy($id, $type)
    {
        $model = $this->getModelByType($type);
        $item = $model::findOrFail($id);
        $item->delete();

        return back()->with('success', 'تم حذف العنصر بنجاح');
    }

    /**
     * تحديد الموديل بناءً على النوع
     */
    private function getModelByType($type)
    {
        return match($type) {
            'university' => \App\Models\University::class,
            'college'    => \App\Models\College::class,
            'department' => \App\Models\Department::class,
            'batch'      => \App\Models\Batch::class,
            'section'    => \App\Models\Section::class,
            'subject'    => \App\Models\Subject::class,
        };
    }

    /**
     * وظيفة تجهيز البيانات
     */
    private function prepareData($data)
    {
        $fields = ['name' => $data['name']];

        switch ($data['type']) {
            case 'college':
                $fields['university_id'] = (int)$data['parent_id'];
                break;

            case 'department':
                $fields['college_id'] = (int)$data['parent_id'];
                break;

            case 'batch':
                $fields['department_id'] = (int)$data['parent_id'];
                break;

            case 'section':
                $fields['batch_id'] = (int)$data['parent_id'];
                $fields['range_start'] = $data['range_start'] ?? null;
                $fields['range_end'] = $data['range_end'] ?? null;
                break;

            case 'subject':
                $fields['batch_id'] = (int)$data['parent_id'];
                $fields['term'] = isset($data['term']) ? (int)$data['term'] : 1;
                $fields['university_id'] = (int)$data['university_id'];
                $fields['college_id']    = (int)$data['college_id'];
                $fields['department_id'] = (int)($data['department_id'] ?? 0);
                $fields['section_id']    = isset($data['section_id']) ? (int)$data['section_id'] : null;
                break;
        }

        return $fields;
    }
    public function uploadMaterialSheet(Request $request)
{
    // التأكد من وجود الملف والمعرف
    $request->validate([
        'excel_file' => 'required|mimes:xlsx,xls,csv',
        'material_id' => 'required|integer'
    ]);

    // تحويل الإكسيل لبيانات (نستخدم Laravel Excel)
    $rows = Excel::toArray(new GenericImport, $request->file('excel_file'))[0];

    $material = Material::findOrFail($request->material_id);
    $newAdded = 0;

    foreach ($rows as $row) {
        $studentCode = $row['code'] ?? $row['الترم']; // حسب اسم العمود في الشيت عندك

        // البحث عن الطالب في شيت الدفعة الأساسي
        $masterData = MasterStudentList::where('student_code', $studentCode)->first();

        if ($masterData) {
            $record = MaterialStudent::updateOrCreate(
                [
                    'material_id'  => $material->id,
                    'student_code' => $studentCode
                ],
                [
                    'student_name' => $masterData->name,
                    'section'      => $masterData->section,
                    'is_delivered' => false
                ]
            );

            if ($record->wasRecentlyCreated) $newAdded++;
        }
    }

    return back()->with('success', "تمت المعالجة! تم إضافة $newAdded طالب جديد بنجاح.");
}

    /**
     * توزيع الطلاب آلياً على السكاشن بناءً على النطاق الرقمي للأكواد
     */
    public function autoAssignByRange(\App\Models\Batch $batch)
    {
        $this->autoAssignSectionsByRange($batch);

        return back()->with('success', "تم توزيع الطلاب آلياً بناءً على نطاقات الأكواد المعرفة.");
    }

    /**
     * عرض الطلاب المسجلين في مادة معينة
     */
    public function showSubjectStudents(\App\Models\Subject $subject)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->assignments()->where('batch_id', $subject->batch_id)->exists()) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بمشاهدة طلاب هذه المادة');
        }

        $students = \App\Models\Student::where('subject_id', $subject->id)
            ->with(['delegate:id,name', 'section:id,name'])
            ->orderBy('name')
            ->paginate(100);

        return \Inertia\Inertia::render('Admin/Academic/SubjectStudents', [
            'subject' => $subject->load('batch'),
            'students' => $students
        ]);
    }

    /**
     * تنزيل تيمبليت Excel للدفعة
     */
    public function downloadBatchTemplate(Batch $batch)
    {
        $batch->load(['sections', 'department.college.university']);
        $filename = 'template_' . \Illuminate\Support\Str::slug($batch->name) . '_' . now()->format('Ymd') . '.xlsx';
        return Excel::download(new BatchTemplateExport($batch), $filename);
    }

    /**
     * رفع ومعالجة كشف طلاب الدفعة
     */
    public function uploadBatchStudents(Request $request, Batch $batch)
    {
        $request->validate([
            'sheet'    => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $batch->load(['department.college.university']);

            $hierarchy = [
                'university_id' => $batch->university_id ?? $batch->department?->college?->university_id,
                'college_id'    => $batch->college_id    ?? $batch->department?->college?->id,
                'department_id' => $batch->department_id ?? $batch->department?->id,
            ];

            // --- تطهير شامل للتكرارات قبل البدء (Pre-Import Cleanup) ---
            // نقوم بدمج أي تكرارات قديمة في الدفعة لضمان دقة الإحصائيات قبل الرفع الجديد
            $duplicateIds = Student::where('batch_id', $batch->id)
                ->whereNull('subject_id')
                ->groupBy('student_external_id')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('student_external_id');

            foreach ($duplicateIds as $extId) {
                $recs = Student::where('batch_id', $batch->id)->where('student_external_id', $extId)->whereNull('subject_id')->get();
                $master = $recs->where('is_received', true)->first() ?? $recs->first();
                Student::where('batch_id', $batch->id)->where('student_external_id', $extId)->whereNull('subject_id')->where('id', '!=', $master->id)->forceDelete();
            }
            // ---------------------------------------------------------

            $importer = new BatchStudentsImport($batch->id, $hierarchy);
            Excel::import($importer, $request->file('sheet'));

            // بعد الاستيراد — توزيع الطلاب على السكاشن آلياً بناءً على النطاقات
            $this->autoAssignSectionsByRange($batch);

            $imported = $importer->getImported();
            $skipped  = $importer->getSkipped();

            return back()->with('success', "تم رفع الكشف بنجاح! تم إضافة {$imported} طالب وتخطي {$skipped} صف فارغ.");
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في معالجة الملف: ' . $e->getMessage());
        }
    }

    /**
     * توزيع الطلاب على السكاشن بناءً على النطاقات الرقمية بعد الاستيراد
     */
    private function autoAssignSectionsByRange(Batch $batch): void
    {
        $sections = $batch->sections()->with('assignments')->get();

        foreach ($sections as $section) {
            $start = (int) preg_replace('/\D/', '', (string)$section->range_start);
            $end   = (int) preg_replace('/\D/', '', (string)$section->range_end);

            if ($start && $end) {
                // الحصول على المندوب المعين لهذا السكشن إن وجد
                $delegateId = $section->assignments->first()?->user_id;

                Student::where('batch_id', $batch->id)
                    ->whereNull('subject_id')
                    ->whereRaw("CAST(REPLACE(student_external_id, ' ', '') AS INTEGER) BETWEEN ? AND ?", [$start, $end])
                    ->update([
                        'section_id' => $section->id,
                        'delegate_id' => $delegateId
                    ]);
            }
        }
    }

    /**
     * عرض سجل عمليات المادة
     */
    public function subjectLog(Request $request, Subject $subject)
    {
        $user = auth()->user();
        // IDOR Protection
        if (!$user->isAdmin() && !$user->assignments()->where('batch_id', $subject->batch_id)->exists()) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بمشاهدة سجل هذه المادة');
        }

        $search = $request->input('search');

        $transactions = \App\Models\InventoryTransaction::with(['user', 'fromUser', 'toUser'])
            ->where('subject_id', $subject->id)
            ->when($search, function ($query, $search) {
                $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"))
                      ->orWhereHas('toUser', fn($q) => $q->where('name', 'like', "%{$search}%"))
                      ->orWhereHas('fromUser', fn($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return \Inertia\Inertia::render('Admin/Academic/SubjectLog', [
            'subject' => $subject,
            'transactions' => $transactions,
            'filters' => ['search' => $search]
        ]);
    }
}
