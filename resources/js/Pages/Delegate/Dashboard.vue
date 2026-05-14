<template>
  <AuthenticatedLayout>
    <div class="del-dash relative min-h-[calc(100vh-100px)]" dir="rtl">
      <!-- Decor Background -->
      <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-indigo-600/5 blur-[120px] rounded-full pointer-events-none"></div>
      <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-blue-600/5 blur-[100px] rounded-full pointer-events-none"></div>

      <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12 animate-in fade-in slide-in-from-top-4 duration-700">
          <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-indigo-600/20 flex items-center justify-center text-indigo-500 shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <h1 class="text-3xl font-black text-white tracking-tight">لوحة تحكم المندوب</h1>
            </div>
            <p class="text-slate-500 font-bold mr-13">{{ batch?.name ?? 'المستوى الأكاديمي المخصص' }}</p>
          </div>
          
          <div class="flex items-center gap-4">
             <div class="hidden sm:flex flex-col items-end">
                <span class="text-xs font-black text-slate-500 uppercase tracking-widest">تاريخ اليوم</span>
                <span class="text-sm font-bold text-slate-200">{{ new Date().toLocaleDateString('ar-EG', { weekday: 'long', day: 'numeric', month: 'long' }) }}</span>
             </div>
          </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12 animate-in fade-in slide-in-from-bottom-4 duration-700 delay-100">
          <StatsCard label="المواد المخصصة"  :value="summary.total_subjects"   variant="blue"   suffix="مادة">
            <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg></template>
          </StatsCard>
          <StatsCard label="طلاب السكشن"   :value="summary.total_students"   variant="purple" suffix="طالب">
            <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></template>
          </StatsCard>
          <StatsCard label="تم التسليم"       :value="summary.total_delivered"  variant="green"  suffix="طالب">
            <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></template>
          </StatsCard>
          <StatsCard label="رصيد الكتب" :value="summary.total_inventory"  variant="amber"  suffix="نسخة">
            <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 10V11"/></svg></template>
          </StatsCard>
        </div>

        <!-- Subjects Title -->
        <div class="flex items-center justify-between mb-8 px-2 animate-in fade-in duration-700 delay-200">
            <h2 class="text-xl font-black text-white flex items-center gap-3">
                <span class="w-2 h-8 bg-indigo-600 rounded-full"></span>
                قائمة المواد الدراسية
            </h2>
            <div class="h-px flex-1 bg-white/5 mx-6"></div>
        </div>

        <!-- Subjects List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-300">
          <div v-if="!subjects.length" class="col-span-full">
            <div class="bg-white/5 border border-dashed border-white/10 rounded-[40px] p-20 flex flex-center text-center justify-center">
                <EmptyState title="لا توجد مواد مخصصة لك" description="تواصل مع المدير لتخصيص المواد الدراسية." />
            </div>
          </div>
          
          <div v-for="subject in subjects" :key="subject.id" class="subject-glass-card group relative">
            <!-- Glow Effect -->
            <div class="absolute -inset-px bg-gradient-to-br from-indigo-600/20 to-transparent rounded-[32px] opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            
            <div class="relative bg-slate-900/40 backdrop-blur-xl border border-white/10 rounded-[32px] p-8 h-full flex flex-col shadow-2xl transition-all duration-300 group-hover:-translate-y-2 group-hover:border-indigo-500/50">
              <div class="flex justify-between items-start mb-8">
                <div class="space-y-1">
                  <h3 class="text-xl font-black text-white group-hover:text-indigo-400 transition-colors leading-tight">{{ subject.name }}</h3>
                  <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">{{ batch?.name }}</span>
                    <span class="w-1 h-1 bg-slate-700 rounded-full"></span>
                    <span class="text-[10px] font-black uppercase tracking-widest text-indigo-400">الترم {{ subject.term == 1 ? 'الأول' : 'الثاني' }}</span>
                  </div>
                </div>
                <div class="p-3 bg-white/5 rounded-2xl text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
              </div>

              <!-- Alerts / Stats -->
              <div v-if="subject.needed_from_batch > 0 && !subject.is_batch_delegate" class="mb-6 p-4 bg-amber-500/10 border border-amber-500/20 rounded-2xl animate-pulse">
                <div class="flex items-center gap-2 text-amber-500 mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span class="text-xs font-black uppercase tracking-widest">ملاحظة التوزيع</span>
                </div>
                <p class="text-sm font-bold text-amber-200/80">متبقي لك <span class="text-amber-400">{{ subject.needed_from_batch }}</span> نسخة تستلمها من مندوب الدفعة.</p>
              </div>

              <div v-if="subject.is_batch_delegate && subject.my_inventory == 0" class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-2xl">
                 <p class="text-xs font-black text-red-400">المخزون صفر، لا يمكن توزيع نسخ في الوقت الحالي.</p>
              </div>

              <!-- Mini Stats Grid -->
              <div class="grid grid-cols-2 gap-4 mb-8">
                <div class="bg-white/2 rounded-2xl p-4 border border-white/5 group-hover:border-white/10 transition-colors">
                    <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">الطلاب</span>
                    <span class="block text-xl font-black text-white">{{ subject.total_students ?? 0 }}</span>
                </div>
                <div class="bg-white/2 rounded-2xl p-4 border border-white/5 group-hover:border-white/10 transition-colors">
                    <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">الرصيد</span>
                    <span class="block text-xl font-black text-indigo-400">{{ subject.my_inventory ?? 0 }}</span>
                </div>
              </div>

              <!-- Distribution Section for Batch Delegates -->
              <div v-if="subject.is_batch_delegate && subject.section_delegates?.length" class="mb-8 space-y-4">
                 <div class="flex items-center justify-between px-1">
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">توزيع للسكاشن</span>
                    <span class="text-[10px] font-bold text-indigo-400">{{ subject.section_delegates.length }} مندوب نشط</span>
                 </div>
                 <div class="max-h-48 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                    <div v-for="delegate in subject.section_delegates" :key="delegate.id" class="flex items-center justify-between p-3 bg-white/5 rounded-xl border border-white/5 hover:border-white/10 transition-colors">
                        <div class="flex flex-col">
                            <span class="text-xs font-black text-slate-200">{{ delegate.name }}</span>
                            <span class="text-[10px] font-bold text-slate-500">يحتاج: {{ delegate.needed }} نسخة</span>
                        </div>
                        <button 
                            @click="openTransfer(subject, delegate)"
                            :disabled="subject.my_inventory == 0"
                            class="px-3 py-1.5 bg-indigo-600/20 hover:bg-indigo-600 text-indigo-400 hover:text-white text-[10px] font-black rounded-lg transition-all disabled:opacity-20 disabled:cursor-not-allowed"
                        >
                            توزيع
                        </button>
                    </div>
                 </div>
              </div>

              <!-- Progress Section -->
              <div class="mt-auto">
                <div class="flex justify-between items-end mb-3 px-1">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">نسبة الإنجاز</span>
                        <span class="text-lg font-black text-emerald-400">{{ subject.total_students > 0 ? Math.round(((subject.delivered_students ?? 0) / subject.total_students) * 100) : 0 }}%</span>
                    </div>
                    <span class="text-xs font-bold text-slate-400">{{ subject.delivered_students ?? 0 }} / {{ subject.total_students ?? 0 }}</span>
                </div>
                <div class="relative h-3 bg-white/5 rounded-full overflow-hidden border border-white/5 p-[2px]">
                    <div 
                        class="h-full bg-gradient-to-r from-indigo-600 to-emerald-500 rounded-full transition-all duration-1000 ease-out"
                        :style="{ width: (subject.total_students > 0 ? ((subject.delivered_students ?? 0) / subject.total_students) * 100 : 0) + '%' }"
                    >
                        <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                    </div>
                </div>
              </div>

              <!-- Action -->
              <div class="mt-8 grid grid-cols-1 gap-3">
                <Link 
                    :href="route('delivery.show', subject.id)" 
                    class="block w-full text-center py-4 rounded-2xl bg-indigo-600 border border-indigo-600 text-white font-black text-sm hover:shadow-[0_0_20px_-5px_rgba(79,70,229,0.5)] transition-all duration-300"
                >
                  بدء عملية التسليم للطلاب
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Transfer Modal -->
        <div v-if="transferModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md animate-in fade-in duration-300">
            <div class="bg-slate-900 border border-white/10 w-full max-w-md rounded-[40px] p-8 shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-600/10 blur-3xl rounded-full"></div>
                
                <h2 class="text-2xl font-black text-white mb-2">توزيع عهدة</h2>
                <p class="text-slate-400 text-sm mb-8 font-medium">نقل نسخ من مادة <span class="text-indigo-400">{{ activeTransfer.subject.name }}</span> إلى المندوب <span class="text-white">{{ activeTransfer.delegate.name }}</span></p>

                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mr-1">الكمية المراد نقلها</label>
                        <input 
                            v-model="transferForm.quantity" 
                            type="number" 
                            class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 text-white outline-none focus:ring-2 focus:ring-indigo-500/50"
                            :placeholder="'بحد أقصى ' + activeTransfer.subject.my_inventory"
                        >
                        <p v-if="transferForm.quantity > activeTransfer.subject.my_inventory" class="text-red-400 text-[10px] font-bold">الكمية أكبر من رصيدك الحالي!</p>
                    </div>

                    <div class="flex gap-4">
                        <button 
                            @click="submitTransfer"
                            :disabled="!transferForm.quantity || transferForm.quantity <= 0 || transferForm.quantity > activeTransfer.subject.my_inventory || transferForm.processing"
                            class="flex-1 bg-indigo-600 text-white py-4 rounded-2xl font-black text-sm hover:bg-indigo-500 transition-all disabled:opacity-20"
                        >
                            {{ transferForm.processing ? 'جارٍ النقل...' : 'تأكيد النقل' }}
                        </button>
                        <button @click="transferModal = false" class="px-6 bg-white/5 text-slate-400 font-bold rounded-2xl hover:bg-white/10 transition-all">إلغاء</button>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref, reactive } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatsCard   from '@/Components/UI/StatsCard.vue';
import EmptyState  from '@/Components/UI/EmptyState.vue';

const props = defineProps({
  subjects:    { type: Array,  default: () => [] },
  batch:       { type: Object, default: null },
  assignments: { type: Array,  default: () => [] },
});

const summary = computed(() => {
  const totalStudents  = props.subjects.reduce((s, x) => s + (x.total_students ?? 0), 0);
  const totalDelivered = props.subjects.reduce((s, x) => s + (x.delivered_students ?? 0), 0);
  const totalInventory = props.subjects.reduce((s, x) => s + (x.my_inventory ?? 0), 0);
  return { 
    total_subjects: props.subjects.length, 
    total_students: totalStudents, 
    total_delivered: totalDelivered, 
    total_inventory: totalInventory 
  };
});

// Transfer Modal Logic
const transferModal = ref(false);
const activeTransfer = reactive({ subject: null, delegate: null });
const transferForm = useForm({
    subject_id: null,
    to_user_id: null,
    quantity: 1,
    notes: 'توزيع عهدة من مندوب الدفعة'
});

const openTransfer = (subject, delegate) => {
    activeTransfer.subject = subject;
    activeTransfer.delegate = delegate;
    transferForm.subject_id = subject.id;
    transferForm.to_user_id = delegate.id;
    transferForm.quantity = Math.min(delegate.needed, subject.my_inventory);
    transferModal.value = true;
};

const submitTransfer = () => {
    transferForm.post(route('transfers.store'), {
        onSuccess: () => {
            transferModal.value = false;
            transferForm.reset();
        }
    });
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.05); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.3); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.5); }

.del-dash {
    background-color: #0f172a;
}

.subject-glass-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.animate-in {
    animation-fill-mode: both;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInTop {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

@keyframes slideInBottom {
    from { transform: translateY(40px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.fade-in { animation-name: fadeIn; }
.slide-in-from-top-4 { animation-name: slideInTop; }
.slide-in-from-bottom-4 { animation-name: slideInBottom; }
.slide-in-from-bottom-8 { animation-name: slideInBottom; animation-duration: 1.2s; }
</style>
