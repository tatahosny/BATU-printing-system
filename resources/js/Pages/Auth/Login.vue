<template>
    <Head title="تسجيل الدخول - نظام إدارة المطبوعات" />

    <div class="login-wrapper min-h-screen flex items-center justify-center p-6 overflow-hidden relative">
        <!-- Animated Background Elements -->
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
        <div class="bg-shape bg-shape-3"></div>

        <div class="w-full max-w-[480px] relative z-10 animate-in fade-in zoom-in duration-700">
            <div class="text-center mb-10">
                <div class="logo-container inline-flex items-center justify-center w-20 h-20 rounded-[24px] mb-6 relative group">
                    <div class="absolute inset-0 bg-indigo-600 blur-2xl opacity-40 group-hover:opacity-70 transition-opacity"></div>
                    <div class="relative bg-slate-900 border border-white/10 w-full h-full rounded-[24px] flex items-center justify-center shadow-2xl">
                        <svg class="w-10 h-10 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
                <h1 class="text-4xl font-black text-white tracking-tight mb-2 drop-shadow-sm">BATU SYSTEM</h1>
                <p class="text-slate-400 font-medium tracking-wide uppercase text-[10px]">نظام إدارة وتوزيع المطبوعات الجامعية</p>
            </div>

            <div class="glass-card p-10 rounded-[40px] border border-white/10 shadow-2xl backdrop-blur-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-indigo-400 uppercase tracking-widest mr-1">البريد الإلكتروني</label>
                        <div class="relative group">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-indigo-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                                </svg>
                            </div>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
                                placeholder="name@company.com"
                                class="login-input w-full p-4 pl-12 bg-white/5 border border-white/10 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all text-white text-sm"
                            >
                        </div>
                        <p v-if="form.errors.email" class="text-red-400 text-xs font-bold mt-1 mr-1">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center px-1">
                            <label class="text-[11px] font-black text-indigo-400 uppercase tracking-widest">كلمة المرور</label>
                            <Link v-if="canResetPassword" :href="route('password.request')" class="text-[10px] font-bold text-slate-500 hover:text-indigo-400 transition-colors">
                                نسيت كلمة المرور؟
                            </Link>
                        </div>
                        <div class="relative group">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-indigo-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002-2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input
                                v-model="form.password"
                                type="password"
                                required
                                placeholder="••••••••"
                                class="login-input w-full p-4 pl-12 bg-white/5 border border-white/10 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all text-white text-sm"
                            >
                        </div>
                        <p v-if="form.errors.password" class="text-red-400 text-xs font-bold mt-1 mr-1">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex items-center justify-between px-1">
                        <label class="flex items-center cursor-pointer group">
                            <input type="checkbox" v-model="form.remember" class="w-4 h-4 rounded border-white/10 bg-white/5 text-indigo-600 focus:ring-indigo-500/20 transition-all cursor-pointer">
                            <span class="mr-3 text-xs font-bold text-slate-400 group-hover:text-slate-200 transition-colors">تذكرني</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="submit-btn w-full bg-indigo-600 text-white py-4 rounded-2xl font-black text-sm hover:shadow-[0_0_30px_-5px_rgba(79,70,229,0.6)] transition-all duration-300 transform active:scale-[0.97]"
                    >
                        <span v-if="!form.processing">تسجيل الدخول</span>
                        <span v-else class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            جارٍ التحقق...
                        </span>
                    </button>
                </form>
            </div>

            <p class="text-center mt-10 text-slate-500 text-[10px] font-bold uppercase tracking-[0.2em]">
                &copy; 2026 BATU DISTRIBUTION SYSTEM
            </p>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<style scoped>
.login-wrapper {
    background-color: #0f172a;
    font-family: 'Inter', 'Noto Sans Arabic', sans-serif;
}

.bg-shape {
    position: absolute;
    filter: blur(120px);
    border-radius: 50%;
    z-index: 1;
}

.bg-shape-1 {
    width: 500px;
    height: 500px;
    background: rgba(79, 70, 229, 0.15);
    top: -100px;
    right: -100px;
    animation: move1 20s infinite alternate;
}

.bg-shape-2 {
    width: 400px;
    height: 400px;
    background: rgba(14, 165, 233, 0.1);
    bottom: -50px;
    left: -50px;
    animation: move2 25s infinite alternate;
}

.bg-shape-3 {
    width: 300px;
    height: 300px;
    background: rgba(168, 85, 247, 0.1);
    top: 40%;
    left: 20%;
    animation: move3 30s infinite alternate;
}

@keyframes move1 {
    0% { transform: translate(0, 0); }
    100% { transform: translate(-100px, 100px); }
}

@keyframes move2 {
    0% { transform: translate(0, 0); }
    100% { transform: translate(100px, -50px); }
}

@keyframes move3 {
    0% { transform: translate(0, 0); }
    100% { transform: translate(-50px, -150px); }
}

.glass-card {
    background: rgba(30, 41, 59, 0.5);
    backdrop-filter: blur(20px);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.login-input {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.login-input:focus {
    background: rgba(255, 255, 255, 0.08);
}

.submit-btn {
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
}

.submit-btn:hover {
    transform: translateY(-2px);
}
</style>
