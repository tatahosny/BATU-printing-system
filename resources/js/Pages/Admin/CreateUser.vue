<template>
    <Head title="إضافة حساب جديد" />
    <div class="min-h-screen bg-[#F8FAFC] flex items-center justify-center p-6 text-right" dir="rtl">
        <div class="w-full max-w-md bg-white rounded-[40px] shadow-2xl p-10 border border-slate-100">
            <h1 class="text-2xl font-black text-slate-900 text-center mb-8">إنشاء حساب جديد</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-xs font-black text-slate-400 mb-2 mr-2">الاسم بالكامل</label>
                    <input v-model="form.name" type="text" class="w-full p-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 font-bold shadow-inner">
                    <p v-if="form.errors.name" class="text-red-500 text-[10px] mt-1">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 mb-2 mr-2">البريد الإلكتروني</label>
                    <input v-model="form.email" type="email" class="w-full p-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 font-bold text-left shadow-inner">
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 mb-2 mr-2">الصلاحية</label>
                    <select v-model="form.role" class="w-full p-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 font-bold shadow-inner">
                        <option value="delegate">مندوب (Delegate)</option>
                        <option value="admin">مدير (Admin)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black text-slate-400 mb-2 mr-2">كلمة المرور</label>
                        <input v-model="form.password" type="password" class="w-full p-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 text-left shadow-inner">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 mb-2 mr-2">تأكيد الكلمة</label>
                        <input v-model="form.password_confirmation" type="password" class="w-full p-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 text-left shadow-inner">
                    </div>
                </div>

                <button :disabled="form.processing" type="submit" class="w-full bg-slate-900 text-white p-5 rounded-3xl font-black text-sm hover:bg-indigo-600 transition-all shadow-xl active:scale-95 mt-4">
                    {{ form.processing ? 'جاري المعالجة...' : 'تفعيل الحساب' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '', email: '', role: 'delegate', password: '', password_confirmation: '',
});

const submit = () => {
    form.post(route('admin.delegates.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>
