<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Table from '@/Components/UI/Table.vue';

const props = defineProps({
    logs: Object,
    filters: Object,
});

const activeType = ref(props.filters?.type || 'operation');
const searchQuery = ref(props.filters?.search || '');

const handleSearch = debounce(() => {
    router.get(route('admin.reports.logs'), { 
        search: searchQuery.value, 
        type: activeType.value 
    }, { preserveState: true, preserveScroll: true, replace: true });
}, 300);

const switchType = (type) => {
    activeType.value = type;
    router.get(route('admin.reports.logs'), { 
        search: searchQuery.value, 
        type: type 
    }, { preserveState: true, preserveScroll: true });
};

const getActionColor = (type) => {
    switch (type) {
        case 'delivery': return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
        case 'return': return 'bg-orange-500/10 text-orange-400 border border-orange-500/20';
        case 'cancel': return 'bg-red-500/10 text-red-400 border border-red-500/20';
        case 'admin_update': return 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20';
        case 'admin_reset': return 'bg-slate-500/10 text-slate-400 border border-slate-500/20';
        // Activity Types
        case 'page_view': return 'bg-slate-500/10 text-slate-400 border border-slate-500/20';
        case 'search': return 'bg-blue-500/10 text-blue-400 border border-blue-500/20';
        case 'file_upload': return 'bg-purple-500/10 text-purple-400 border border-purple-500/20';
        case 'action_execution': return 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
        default: return 'bg-slate-500/10 text-slate-400 border border-slate-500/20';
    }
};

const getActionName = (type) => {
    switch (type) {
        case 'delivery': return 'تسليم كتاب';
        case 'return': return 'مرتجع كتاب';
        case 'cancel': return 'إلغاء تسليم';
        case 'admin_update': return 'تعديل إداري';
        case 'admin_reset': return 'تصفير عهدة';
        // Activity Types
        case 'page_view': return 'زيارة صفحة';
        case 'search': return 'بحث في النظام';
        case 'file_upload': return 'رفع ملفات';
        case 'action_execution': return 'تنفيذ إجراء';
        default: return type;
    }
};
</script>

<template>
    <Head title="سجل العمليات والرقابة" />

    <AuthenticatedLayout>
        <template #header>
            سجل الرقابة والعمليات
        </template>

        <div class="max-w-7xl mx-auto space-y-8 pb-10">
            <!-- Header Section -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6">
                <div class="space-y-2">
                    <h2 class="text-3xl font-black text-white tracking-tight">النشاطات والرقابة</h2>
                    <p class="text-slate-400 text-sm max-w-md leading-relaxed">
                        متابعة حية لكافة التحركات على النظام لضمان أعلى معايير الأمان والشفافية في توزيع الكتب.
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto">
                    <!-- Search -->
                    <div class="relative w-full sm:w-64">
                        <input 
                            v-model="searchQuery" 
                            @input="handleSearch"
                            type="text" 
                            placeholder="بحث في السجلات..." 
                            class="w-full bg-slate-900/50 border border-white/10 rounded-2xl py-3 px-4 pr-10 text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all shadow-inner"
                        >
                        <svg class="w-5 h-5 text-slate-500 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- Type Switcher -->
                    <div class="bg-slate-900/80 p-1.5 rounded-2xl border border-white/5 flex items-center shadow-2xl backdrop-blur-xl">
                        <button 
                            @click="switchType('operation')"
                            class="px-6 py-2 rounded-xl text-xs font-black transition-all duration-300 uppercase tracking-widest whitespace-nowrap"
                            :class="activeType === 'operation' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/40' : 'text-slate-500 hover:text-slate-300'"
                        >
                            سجل التسليمات
                        </button>
                        <button 
                            @click="switchType('activity')"
                            class="px-6 py-2 rounded-xl text-xs font-black transition-all duration-300 uppercase tracking-widest whitespace-nowrap"
                            :class="activeType === 'activity' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/40' : 'text-slate-500 hover:text-slate-300'"
                        >
                            سجل النشاط العام
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-slate-900/40 border border-white/5 rounded-[24px] p-5 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">إجمالي السجلات</span>
                        <p class="text-2xl font-black text-white mt-1">{{ logs.total }}</p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-500/10 rounded-2xl flex items-center justify-center text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <Card no-padding class="overflow-hidden border border-white/5 shadow-2xl bg-slate-900/20 backdrop-blur-sm rounded-[32px]">
                <Table 
                    :headers="activeType === 'operation' ? ['المستخدم', 'العملية', 'التفاصيل', 'التوقيت'] : ['المستخدم', 'النوع', 'المسار / البيانات', 'التوقيت']"
                    :items="logs.data"
                >
                    <template #row="{ item }">
                        <td class="py-6 pl-4 pr-3 text-sm sm:pl-8 border-b border-white/5">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-slate-800 rounded-2xl flex items-center justify-center font-black text-slate-300 border border-white/5 shadow-xl group-hover:scale-110 transition-transform">
                                    {{ item.user?.name?.charAt(0) || 'U' }}
                                </div>
                                <div>
                                    <div class="font-black text-white leading-tight text-base">{{ item.user?.name || 'مستخدم محذوف' }}</div>
                                    <div class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mt-1">
                                        {{ item.user?.role === 'admin' ? 'مدير نظام' : 'مندوب' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-6 text-sm border-b border-white/5">
                            <span :class="[
                                'px-4 py-1.5 rounded-full text-[10px] font-black shadow-sm whitespace-nowrap uppercase tracking-widest border transition-all',
                                getActionColor(activeType === 'operation' ? item.action_type : item.action)
                            ]">
                                {{ getActionName(activeType === 'operation' ? item.action_type : item.action) }}
                            </span>
                        </td>
                        <td class="px-3 py-6 text-sm text-slate-300 border-b border-white/5">
                            <!-- Operation Logs Details -->
                            <div v-if="activeType === 'operation'" class="space-y-1.5">
                                <div v-if="item.student" class="flex flex-col">
                                    <p class="font-black text-slate-100 text-sm">{{ item.student.name }}</p>
                                    <p class="text-[11px] text-slate-400 font-bold flex items-center gap-1.5 mt-1">
                                        <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                        {{ item.student.subject?.name || 'مادة غير معروفة' }}
                                    </p>
                                </div>
                                <div v-else class="text-slate-500 flex items-center gap-2 italic">
                                    <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="text-[11px]">{{ item.user_agent ? item.user_agent.substring(0, 40) + '...' : 'بيانات تقنية' }}</span>
                                </div>
                            </div>
                            
                            <!-- Activity Logs Details -->
                            <div v-else class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <code class="text-[10px] bg-slate-800/80 text-indigo-300 px-2 py-0.5 rounded-lg font-mono border border-white/5">/{{ item.url }}</code>
                                </div>
                                <div v-if="item.payload && Object.keys(item.payload).length > 0" class="flex flex-wrap gap-1">
                                    <template v-for="(val, key) in item.payload" :key="key">
                                        <span class="text-[9px] bg-white/5 text-slate-400 px-1.5 py-0.5 rounded-md border border-white/5">
                                            {{ key }}: {{ typeof val === 'object' ? '...' : val }}
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-6 text-[11px] text-slate-500 border-b border-white/5 font-black uppercase tracking-widest whitespace-nowrap text-left pr-8" dir="ltr">
                            <div class="flex flex-col items-start gap-1">
                                <span class="text-slate-300">{{ new Date(item.created_at).toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' }) }}</span>
                                <span class="text-[9px] opacity-60">{{ new Date(item.created_at).toLocaleDateString('ar-EG', { day: '2-digit', month: '2-digit', year: 'numeric' }) }}</span>
                            </div>
                        </td>
                    </template>
                </Table>
            </Card>

            <!-- Pagination -->
            <div v-if="logs.links.length > 3" class="flex justify-center gap-3 mt-12 pb-10">
                <template v-for="(link, k) in logs.links" :key="k">
                    <Link v-if="link.url"
                        :href="link.url" v-html="link.label"
                        class="min-w-[44px] h-11 flex items-center justify-center rounded-2xl text-xs font-black transition-all duration-300"
                        :class="link.active 
                            ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/30 scale-110 z-10' 
                            : 'bg-slate-900/50 text-slate-500 hover:bg-slate-800 hover:text-slate-200 border border-white/5'" />
                    <span v-else
                        v-html="link.label"
                        class="min-w-[44px] h-11 flex items-center justify-center rounded-2xl text-xs font-black bg-slate-900/20 text-slate-700 border border-white/5 cursor-not-allowed opacity-50" />
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.group:hover .group-hover\:scale-110 {
    transform: scale(1.1);
}
</style>
