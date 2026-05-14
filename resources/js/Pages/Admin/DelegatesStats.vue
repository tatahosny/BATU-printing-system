<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Table from '@/Components/UI/Table.vue';

const props = defineProps({ 
    stats: Array 
});

const calculateProgress = (stat) => {
    const total = parseInt(stat.total_section_students) || 0;
    const delivered = parseInt(stat.delivered_count) || 0;
    if (total === 0) return 0;
    return Math.round((delivered / total) * 100);
};
</script>

<template>
    <Head title="إحصائيات المناديب" />

    <AuthenticatedLayout>
        <template #header>
            رقابة أداء المناديب
        </template>

        <div class="max-w-7xl mx-auto space-y-8 pb-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <h2 class="text-3xl font-black text-white tracking-tight">متابعة حية للتسليم</h2>
                    <p class="text-slate-400 font-medium mt-1">مراقبة دقيقة لنسب الإنجاز ومستوى العهد لكل مندوب</p>
                </div>
                <div class="flex items-center gap-3 px-4 py-2 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl shadow-xl shadow-emerald-500/5">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-xs font-black text-emerald-400 uppercase tracking-widest">تحديث حي ومباشر</span>
                </div>
            </div>

            <Card no-padding class="overflow-hidden border-white/5 bg-slate-900/40 backdrop-blur-xl">
                <Table 
                    :headers="['المندوب', 'المادة', 'حجم السكشن', 'تم التسليم', 'العهدة', 'نسبة الإنجاز']"
                    :items="stats"
                >
                    <template #row="{ item }">
                        <td class="py-5 pl-4 pr-3 border-b border-white/5 sm:pl-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-indigo-500/10 border border-indigo-500/20 rounded-2xl flex items-center justify-center font-black text-indigo-400">
                                    {{ item.delegate_name?.charAt(0) }}
                                </div>
                                <div class="font-black text-white text-base">{{ item.delegate_name }}</div>
                            </div>
                        </td>
                        <td class="px-3 py-5 border-b border-white/5">
                            <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shadow-sm">
                                {{ item.subject_name }}
                            </span>
                        </td>
                        <td class="px-3 py-5 text-center font-black text-slate-300 border-b border-white/5">
                            {{ item.total_section_students }}
                        </td>
                        <td class="px-3 py-5 text-center border-b border-white/5">
                            <span class="text-emerald-400 font-black text-base">{{ item.delivered_count }}</span>
                        </td>
                        <td class="px-3 py-5 text-center border-b border-white/5">
                            <div class="flex flex-col items-center">
                                <span :class="[
                                    'font-black text-base',
                                    item.current_inventory < 5 ? 'text-rose-500 animate-pulse' : 'text-slate-100'
                                ]">
                                    {{ item.current_inventory }}
                                </span>
                                <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest mt-0.5">نسخة</span>
                            </div>
                        </td>
                        <td class="px-3 py-5 min-w-[180px] border-b border-white/5">
                            <div class="flex flex-col gap-2">
                                <div class="flex justify-between items-end">
                                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">معدل الإنجاز</span>
                                    <span class="text-xs font-black text-white">{{ calculateProgress(item) }}%</span>
                                </div>
                                <div class="relative h-2 bg-white/5 rounded-full overflow-hidden border border-white/5">
                                    <div class="h-full bg-gradient-to-l from-indigo-600 to-indigo-400 transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(99,102,241,0.3)]"
                                         :style="{ width: calculateProgress(item) + '%' }">
                                        <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </template>
                </Table>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
