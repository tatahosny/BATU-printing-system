<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import Card from '@/Components/UI/Card.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="إعدادات الحساب" />

    <AuthenticatedLayout>
        <template #header>
            إعدادات الحساب
        </template>

        <div class="max-w-4xl mx-auto space-y-10 pb-20">
            <!-- Header Section -->
            <div class="space-y-2 animate-page-entry">
                <h2 class="text-3xl font-black text-white tracking-tight">إعدادات ملفك الشخصي</h2>
                <p class="text-slate-400 text-sm leading-relaxed max-w-lg">
                    تحكم في بياناتك الشخصية، قم بتحديث كلمة المرور، أو إدارة خيارات الأمان الخاصة بك.
                </p>
            </div>

            <div class="space-y-8">
                <!-- Profile Information Card -->
                <div class="bg-slate-900/40 border border-white/5 rounded-[32px] p-8 shadow-2xl backdrop-blur-xl animate-page-entry" style="animation-delay: 0.1s">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <!-- Password Update Card -->
                <div class="bg-slate-900/40 border border-white/5 rounded-[32px] p-8 shadow-2xl backdrop-blur-xl animate-page-entry" style="animation-delay: 0.2s">
                    <UpdatePasswordForm />
                </div>

                <!-- Account Deletion (Danger Zone) -->
                <div v-if="$page.props.auth.user.role !== 'admin'" class="bg-red-500/5 border border-red-500/10 rounded-[32px] p-8 shadow-2xl backdrop-blur-xl animate-page-entry" style="animation-delay: 0.3s">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
