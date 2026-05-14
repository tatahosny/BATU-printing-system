<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header class="mb-8">
            <h2 class="text-xl font-black text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                تأمين وحماية الحساب
            </h2>
            <p class="mt-1 text-sm text-slate-400 font-medium">تأكد من استخدام كلمة مرور قوية للحفاظ على أمان حسابك الشخصي.</p>
        </header>

        <form @submit.prevent="updatePassword" class="space-y-6">
            <div class="grid grid-cols-1 gap-6">
                <Input
                    ref="currentPasswordInput"
                    label="كلمة المرور الحالية"
                    v-model="form.current_password"
                    type="password"
                    autocomplete="current-password"
                    :error="form.errors.current_password"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Input
                        ref="passwordInput"
                        label="كلمة المرور الجديدة"
                        v-model="form.password"
                        type="password"
                        autocomplete="new-password"
                        :error="form.errors.password"
                    />

                    <Input
                        label="تأكيد كلمة المرور"
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        :error="form.errors.password_confirmation"
                    />
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <Button :disabled="form.processing" class="px-8 shadow-xl shadow-indigo-500/20">تحديث كلمة المرور</Button>

                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-x-4"
                    enter-to-class="opacity-100 translate-x-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-emerald-400 font-black flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        تم التحديث بنجاح
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
