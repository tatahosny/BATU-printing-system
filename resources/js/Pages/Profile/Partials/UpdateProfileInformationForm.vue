<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header class="mb-8">
            <h2 class="text-xl font-black text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                بيانات الحساب الأساسية
            </h2>
            <p class="mt-1 text-sm text-slate-400 font-medium">تحديث معلومات ملفك الشخصي وعنوان بريدك الإلكتروني.</p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <Input
                    label="الاسم الكامل"
                    v-model="form.name"
                    required
                    autocomplete="name"
                    :error="form.errors.name"
                />

                <Input
                    label="البريد الإلكتروني"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="username"
                    :error="form.errors.email"
                />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-amber-400 bg-amber-400/5 border border-amber-400/20 p-4 rounded-2xl">
                    عنوان بريدك الإلكتروني غير مؤكد.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="text-indigo-400 hover:text-indigo-300 underline font-black ml-1"
                    >
                        انقر هنا لإعادة إرسال بريد التأكيد.
                    </Link>
                </p>

                <div v-show="status === 'verification-link-sent'" class="mt-2 text-sm font-bold text-emerald-400">
                    تم إرسال رابط تأكيد جديد إلى عنوان بريدك الإلكتروني.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <Button :disabled="form.processing" class="px-8 shadow-xl shadow-indigo-500/20">حفظ التغييرات</Button>

                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-x-4"
                    enter-to-class="opacity-100 translate-x-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-emerald-400 font-black flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        تم حفظ البيانات بنجاح
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
