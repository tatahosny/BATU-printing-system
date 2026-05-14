<template>
  <AuthenticatedLayout>
    <div class="delivery-page min-h-screen relative overflow-hidden" dir="rtl">
      <!-- Background Decorative Elements -->
      <div class="absolute top-[-10%] right-[-10%] w-[40%] h-[40%] bg-indigo-600/10 blur-[150px] rounded-full pointer-events-none"></div>
      <div class="absolute bottom-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-600/10 blur-[150px] rounded-full pointer-events-none"></div>

      <div class="relative z-10 p-6 lg:p-10 max-w-[1400px] mx-auto space-y-10">
        
        <!-- Premium Header -->
        <div class="glass-card rounded-[40px] p-8 lg:p-10 border border-white/10 shadow-2xl animate-in fade-in slide-in-from-top-10 duration-700 bg-slate-900/60 backdrop-blur-2xl">
            <div class="flex flex-col lg:flex-row justify-between items-center gap-8">
                <div class="flex items-center gap-6">
                    <Link :href="route('dashboard')" class="p-4 bg-white/5 hover:bg-white/10 rounded-2xl text-slate-300 hover:text-white transition-all group border border-white/5">
                        <svg class="w-6 h-6 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </Link>
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                             <span class="px-3 py-1 bg-indigo-600/20 text-indigo-300 text-[10px] font-black uppercase tracking-widest rounded-lg border border-indigo-600/30">وحدة التسليم الذكي</span>
                             <span class="w-1.5 h-1.5 bg-slate-700 rounded-full"></span>
                             <span class="text-xs font-bold text-slate-400">{{ subject.batch?.name }}</span>
                        </div>
                        <h1 class="text-3xl lg:text-5xl font-black text-white leading-tight drop-shadow-sm">{{ subject.name }}</h1>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-4">
                    <div class="inventory-pill p-4 px-8 rounded-3xl bg-slate-950/60 border border-indigo-500/20 backdrop-blur-xl flex flex-col items-center shadow-lg shadow-indigo-900/20">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">رصيدك الحالي</span>
                        <div class="flex items-center gap-3">
                            <span class="text-3xl font-black" :class="user_inventory > 0 ? 'text-indigo-400' : 'text-rose-500'">{{ user_inventory }}</span>
                            <span class="text-xs font-bold text-slate-500">نسخة</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 animate-in fade-in slide-in-from-bottom-10 duration-700 delay-200">
            <div v-for="(val, key) in deliveryStats" :key="key" class="stat-mini-card p-6 rounded-[32px] bg-slate-900/40 border border-white/5 backdrop-blur-md hover:border-indigo-500/20 transition-all duration-300">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest block mb-2">{{ val.label }}</span>
                <div class="flex items-end gap-2">
                    <span class="text-3xl font-black text-white">{{ val.value }}</span>
                    <span v-if="val.suffix" class="text-xs font-bold text-slate-600 mb-1.5">{{ val.suffix }}</span>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="glass-card rounded-[40px] p-8 border border-white/10 shadow-2xl animate-in fade-in slide-in-from-bottom-10 duration-700 delay-300 bg-slate-900/40">
            <div class="flex flex-col md:flex-row gap-6">
                <div class="relative flex-1 group">
                    <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-indigo-400 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input
                        type="text"
                        class="w-full bg-slate-950/40 border border-white/5 rounded-2xl p-5 pr-14 text-white outline-none focus:ring-2 focus:ring-indigo-600/30 focus:border-indigo-500 transition-all font-bold placeholder:text-slate-600 shadow-inner"
                        placeholder="ابحث عن طالب بالاسم أو الرقم الجامعي..."
                        :value="filters.search"
                        @input="e => router.get(route('delivery.show', subject.id), { search: e.target.value }, { preserveState: true, replace: true })"
                    >
                </div>
            </div>
        </div>

        <!-- Students Table Section -->
        <div class="glass-card rounded-[40px] overflow-hidden border border-white/10 shadow-2xl animate-in fade-in slide-in-from-bottom-10 duration-1000 delay-400 bg-slate-900/40">
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="bg-white/5">
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">الرقم الجامعي</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">الاسم بالكامل</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">حالة الاستلام</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">التاريخ</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-left">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-if="!students.data.length">
                            <td colspan="5" class="px-8 py-20">
                                <EmptyState title="لا توجد بيانات متاحة" description="لم يتم العثور على أي طلاب يطابقون معايير البحث." />
                            </td>
                        </tr>
                        <tr v-for="student in students.data" :key="student.id" class="group hover:bg-white/[0.02] transition-colors">
                            <td class="px-8 py-6">
                                <span class="text-sm font-black text-indigo-400/80 group-hover:text-indigo-400 transition-colors">#{{ student.student_external_id }}</span>
                            </td>
                            <td class="px-8 py-6">
                                <span class="text-base font-bold text-slate-200 group-hover:text-white transition-colors">{{ student.name }}</span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full" :class="student.is_received ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]' : 'bg-slate-700'"></div>
                                    <span class="text-xs font-black uppercase tracking-widest" :class="student.is_received ? 'text-emerald-400' : 'text-slate-500'">
                                        {{ student.is_received ? 'تم التسليم' : 'بانتظار الاستلام' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="text-xs font-bold text-slate-500 group-hover:text-slate-400 transition-colors">{{ student.received_at ? formatDate(student.received_at) : '—' }}</span>
                            </td>
                            <td class="px-8 py-6 text-left">
                                <button
                                    @click="toggleDelivery(student, student.is_received ? 'cancel' : 'delivery')"
                                    :disabled="processing === student.id || (!student.is_received && user_inventory <= 0)"
                                    class="delivery-action-btn"
                                    :class="student.is_received ? 'cancel-btn' : 'confirm-btn'"
                                >
                                    <span v-if="processing !== student.id" class="flex items-center gap-2">
                                        <svg v-if="!student.is_received" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        {{ student.is_received ? 'إلغاء الاستلام' : 'تأكيد التسليم' }}
                                    </span>
                                    <svg v-else class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Container -->
            <div v-if="students.last_page > 1" class="bg-slate-900/40 p-8 flex justify-center border-t border-white/5">
                 <div class="flex gap-2">
                    <Link
                        v-for="link in students.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        class="pagination-link transition-all duration-300"
                        :class="{ 'active': link.active, 'disabled': !link.url }"
                        v-html="link.label"
                    />
                 </div>
            </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState  from '@/Components/UI/EmptyState.vue';

const props = defineProps({
  subject:        { type: Object, required: true },
  students:       { type: Object, required: true },
  user_inventory: { type: Number, default: 0 },
  batch_delegate: { type: Object, default: null },
  stats:          { type: Object, default: () => ({ total: 0, delivered: 0, remaining: 0, percentage: 0 }) },
  filters:        { type: Object, default: () => ({}) },
});

const processing = ref(null);

const deliveryStats = computed(() => ({
    total: { label: 'إجمالي الطلاب', value: props.stats.total, suffix: 'طالب' },
    delivered: { label: 'تم التسليم', value: props.stats.delivered, suffix: 'طالب' },
    remaining: { label: 'متبقي للتسليم', value: props.stats.remaining, suffix: 'طالب' },
    rate: { label: 'نسبة الإنجاز', value: props.stats.percentage, suffix: '%' }
}));

function toggleDelivery(student, action) {
  processing.value = student.id;
  router.post(
    route('delivery.students.toggle', { subject: props.subject.id, student: student.id }),
    { action },
    {
      preserveScroll: true,
      onFinish: () => { processing.value = null; },
    }
  );
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('ar-EG', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
}
</script>

<style scoped>
.delivery-page {
    background-color: #0f172a;
    font-family: 'Inter', 'Noto Sans Arabic', sans-serif;
}

.glass-card {
    background: rgba(30, 41, 59, 0.4);
    backdrop-filter: blur(20px);
}

.delivery-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 14px;
    font-size: 0.75rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.confirm-btn {
    background: #6366f1;
    color: white;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
}

.confirm-btn:hover:not(:disabled) {
    background: #4f46e5;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
}

.cancel-btn {
    background: rgba(239, 68, 68, 0.1);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.cancel-btn:hover:not(:disabled) {
    background: #ef4444;
    color: white;
    transform: translateY(-2px);
}

.delivery-action-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
    filter: grayscale(1);
}

.pagination-link {
    padding: 10px 18px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: #94a3b8;
    font-size: 0.8rem;
    font-weight: 800;
    transition: all 0.2s;
}

.pagination-link.active {
    background: #6366f1;
    color: white;
    border-color: #6366f1;
}

.pagination-link.disabled {
    opacity: 0.2;
    pointer-events: none;
}

@keyframes slideInTop {
    from { transform: translateY(-40px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

@keyframes slideInBottom {
    from { transform: translateY(40px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.animate-in { animation-fill-mode: both; }
.slide-in-from-top-10 { animation: slideInTop 1s cubic-bezier(0.2, 0.8, 0.2, 1); }
.slide-in-from-bottom-10 { animation: slideInBottom 1s cubic-bezier(0.2, 0.8, 0.2, 1); }
</style>
