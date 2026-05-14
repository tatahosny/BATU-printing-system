<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DelegateAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class DelegateController extends Controller {
    public function index() {
        $query = User::with(['assignments.university', 'assignments.college', 'assignments.batch', 'assignments.section'])
            ->withCount(['activityLogs', 'inventories'])
            ->withSum('inventories as total_stock', 'quantity')
            ->orderBy('created_at', 'desc');

        // حماية حساب السوبر أدمن (ID 1): لا يمكن لأحد رؤيته سوى نفسه
        if (auth()->id() !== 1) {
            $query->where('id', '!=', 1);
        }

        return Inertia::render('Admin/Delegates/Index', [
            'delegates' => $query->get(),
            'academic_tree' => \App\Models\University::with('colleges.departments.batches.sections')->get()
        ]);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,general_delegate,section_delegate',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'تم إنشاء حساب المندوب بنجاح');
    }

    public function assignScope(Request $request, User $user)
    {
        $data = $request->validate([
            'university_id' => 'required|exists:universities,id',
            'college_id' => 'required|exists:colleges,id',
            'department_id' => 'required|exists:departments,id',
            'batch_id' => 'required|exists:batches,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        // تعيين النطاق: نحدث التعيين الموجود لهذا النطاق (سكشن/مادة) أو ننشئ واحداً جديداً
        $assignment = \App\Models\DelegateAssignment::updateOrCreate(
            [
                'batch_id'   => $data['batch_id'],
                'section_id' => $data['section_id'] ?? null,
                'subject_id' => $data['subject_id'] ?? null,
            ],
            array_merge($data, ['user_id' => $user->id])
        );

        // إذا تم تعيين سكشن، نقوم بمزامنة الطلاب في هذا السكشن لهذا المندوب ليتمكن من رؤيتهم
        if ($data['section_id']) {
            $section = \App\Models\Section::find($data['section_id']);
            if ($section && $section->range_start && $section->range_end) {
                $start = (int) preg_replace('/\D/', '', (string)$section->range_start);
                $end   = (int) preg_replace('/\D/', '', (string)$section->range_end);

                \App\Models\Student::where('batch_id', $data['batch_id'])
                    ->whereRaw("CAST(REPLACE(student_external_id, ' ', '') AS INTEGER) BETWEEN ? AND ?", [$start, $end])
                    ->update(['delegate_id' => $user->id]);
            }
        }

        return back()->with('success', 'تم تعيين النطاق للمندوب ومزامنة كشف الطلاب بنجاح');
    }

    public function activityLog(User $user)
    {
        // حماية حساب السوبر أدمن (ID 1) وأي مدير لم يتم إنشاؤه بواسطة المستخدم الحالي
        $currentUser = auth()->user();
        if ($user->id === 1 && $currentUser->id !== 1) {
            abort(403, 'غير مصرح لك بمشاهدة سجلات هذا المستخدم');
        }

        // إذا كان المستخدم المطلوب أدمن، يجب أن يكون إما هو نفسه أو تم إنشاؤه بواسطة المستخدم الحالي (السوبر أدمن عادة)
        if ($user->role === 'admin' && $user->id !== $currentUser->id && $user->created_by !== $currentUser->id && $currentUser->id !== 1) {
             abort(403, 'غير مصرح لك بمشاهدة سجلات هذا المدير');
        }

        $logs = \App\Models\UserActivityLog::where('user_id', $user->id)
            ->latest()
            ->paginate(50);

        return Inertia::render('Admin/Delegates/ActivityLog', [
            'delegate' => $user,
            'logs' => $logs
        ]);
    }
}
