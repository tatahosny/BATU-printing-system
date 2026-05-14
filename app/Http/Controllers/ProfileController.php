<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Inventory;
use App\Models\Student;
use App\Models\Transfer;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * عرض صفحة الملف الشخصي مع الإحصائيات (الاستلام والتوزيع).
     */
    public function show(Request $request): Response
    {
        $user = $request->user();

        // 1. حساب إجمالي ما استلمه المندوب من الإدارة أو المناديب العموميين
        $totalReceived = Transfer::where('to_user_id', $user->id)->sum('quantity');

        // 2. حساب إجمالي ما قام بتوزيعه على الطلاب (بناءً على حقل delivered_by)
        $totalDeliveredToStudents = Student::where('delivered_by', $user->id)
            ->where('is_received', true)
            ->count();

        // 3. الرصيد الحالي الفعلي الموجود معه في جميع المواد
        $currentStock = Inventory::where('user_id', $user->id)->sum('quantity');

        // 4. سجل آخر 5 عمليات استلام خاصة به ليعرف مصدر عهدته
        $recentActivity = Transfer::with(['fromUser', 'subject'])
            ->where('to_user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'from_user' => ['name' => $t->fromUser->name],
                'subject' => ['name' => $t->subject->name],
                'quantity' => $t->quantity,
                'created_at' => $t->created_at->diffForHumans()
            ]);

        return Inertia::render('Profile/Show', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'stats' => [
                'total_received' => (int) $totalReceived,
                'total_delivered' => (int) $totalDeliveredToStudents,
                'current_stock' => (int) $currentStock,
            ],
            'recentActivity' => $recentActivity,
        ]);
    }

    /**
     * عرض نموذج تعديل بيانات الملف الشخصي (الاسم، الإيميل، الباسورد).
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * تحديث معلومات المستخدم.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('success', 'تم تحديث البيانات بنجاح');
    }

    /**
     * حذف حساب المستخدم (اختياري، يفضل تعطيله للمناديب).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
