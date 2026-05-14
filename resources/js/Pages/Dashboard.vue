<template>
  <AuthenticatedLayout>
    <div class="dash-container min-h-screen relative overflow-hidden" dir="rtl">
      <!-- Background Decorative Elements -->
      <div class="absolute top-[-10%] right-[-10%] w-[40%] h-[40%] bg-indigo-600/10 blur-[150px] rounded-full pointer-events-none"></div>
      <div class="absolute bottom-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-600/10 blur-[150px] rounded-full pointer-events-none"></div>

      <div class="relative z-10 p-6 lg:p-10 max-w-[1600px] mx-auto space-y-10">
        
        <!-- Premium Welcome Banner -->
        <div class="welcome-card relative overflow-hidden rounded-[40px] p-8 lg:p-12 border border-white/10 shadow-2xl animate-in fade-in slide-in-from-top-10 duration-1000">
          <div class="relative z-20 flex flex-col lg:flex-row justify-between items-center gap-8 text-center lg:text-right">
            <div class="space-y-4">
              <div class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-500/10 border border-indigo-500/20 rounded-full">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                </span>
                <span class="text-xs font-black text-indigo-400 uppercase tracking-widest">لوحة التحكم النشطة</span>
              </div>
              <h1 class="text-4xl lg:text-6xl font-black text-white leading-tight">
                مرحباً بك مجدداً، <span class="text-transparent bg-clip-text bg-gradient-to-l from-indigo-400 to-blue-400">{{ $page.props.auth.user.name }}</span>
              </h1>
              <p class="text-slate-400 text-lg max-w-2xl font-medium opacity-80">
                نظام BATU المتطور لإدارة وتوزيع المطبوعات الجامعية. راقب الإحصائيات، أدر المخزون، وتابع أداء المناديب في مكان واحد.
              </p>
            </div>
            
            <div v-if="$page.props.auth.user.role === 'admin'" class="flex flex-wrap justify-center gap-4">
              <Link :href="route('admin.inventory.index')" class="premium-btn primary group">
                <span>إدارة المخزون</span>
                <svg class="w-5 h-5 group-hover:translate-x-[-4px] transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
              </Link>
              <Link :href="route('admin.academic.index')" class="premium-btn secondary group">
                <span>الإدارة الأكاديمية</span>
                <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
              </Link>
            </div>
            <div v-else class="flex flex-wrap justify-center gap-4">
              <div class="px-6 py-4 bg-white/5 border border-white/10 rounded-3xl backdrop-blur-md">
                <p class="text-sm font-bold text-slate-300">أهلاً بك في نظام التوزيع الميداني</p>
                <p class="text-[10px] text-slate-500 mt-1 uppercase tracking-widest font-black">جاهز للبدء في تسليم الكتب للطلاب</p>
              </div>
            </div>
          </div>
          <!-- Abstract Shapes -->
          <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-600/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
          <div class="absolute bottom-0 left-0 w-48 h-48 bg-blue-600/10 rounded-full -ml-24 -mb-24 blur-2xl"></div>
        </div>

        <!-- Dynamic Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 animate-in fade-in slide-in-from-bottom-10 duration-1000 delay-200">
          <StatsCard label="إجمالي الطلاب"  :value="stats.total_students"   variant="blue"   suffix="طالب">
            <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></template>
          </StatsCard>
          <StatsCard label="تم التسليم"      :value="stats.total_delivered"  variant="green"  suffix="طالب">
            <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></template>
          </StatsCard>
          <StatsCard label="المواد الدراسية" :value="stats.total_subjects"   variant="purple" suffix="مادة">
            <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg></template>
          </StatsCard>
          <StatsCard label="المناديب النشطون" :value="stats.active_delegates" variant="amber" suffix="مندوب">
            <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a4 4 0 018 0v2"/></svg></template>
          </StatsCard>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-10 animate-in fade-in slide-in-from-bottom-10 duration-1000 delay-500">
          
          <!-- Performance Overview -->
          <div class="xl:col-span-2 space-y-10">
            <div class="glass-section p-8 rounded-[40px] border border-white/5 shadow-xl">
              <div class="flex items-center justify-between mb-8">
                <div>
                  <h2 class="text-2xl font-black text-white tracking-tight">تحليل أداء التوزيع الإجمالي</h2>
                  <p class="text-slate-500 font-medium">نسبة الإنجاز مقارنة بالطلاب المسجلين</p>
                </div>
                <div class="text-right">
                  <span class="text-4xl font-black text-emerald-400">{{ stats.delivery_rate }}%</span>
                </div>
              </div>
              <div class="relative h-6 bg-white/5 rounded-full overflow-hidden border border-white/5 p-1">
                <div 
                    class="h-full bg-gradient-to-r from-indigo-600 via-purple-600 to-emerald-500 rounded-full transition-all duration-1000 ease-out shadow-[0_0_20px_rgba(99,102,241,0.3)]"
                    :style="{ width: stats.delivery_rate + '%' }"
                >
                    <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                </div>
              </div>
              <div class="flex justify-between mt-4 text-xs font-black uppercase tracking-widest text-slate-500">
                <span>{{ stats.total_delivered }} مسلم</span>
                <span>{{ stats.total_students }} إجمالي</span>
              </div>
            </div>

            <!-- Universities Grid -->
            <div class="glass-section p-8 rounded-[40px] border border-white/5 shadow-xl">
              <div class="flex items-center justify-between mb-8 px-2">
                <h2 class="text-2xl font-black text-white flex items-center gap-3">
                  <div class="w-2 h-8 bg-blue-500 rounded-full"></div>
                  نسب التسليم حسب الجامعة
                </h2>
                <Link :href="route('admin.academic.index')" class="text-sm font-bold text-indigo-400 hover:text-indigo-300 transition-colors">عرض كافة الجامعات ←</Link>
              </div>
              
              <div v-if="!university_stats.length" class="py-20 flex flex-col items-center justify-center border border-dashed border-white/10 rounded-[32px]">
                <EmptyState title="لا توجد بيانات حالياً" />
              </div>

              <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="uni in university_stats" :key="uni.id" class="uni-glass-card group p-6 rounded-[32px] bg-white/2 border border-white/5 hover:border-indigo-500/30 transition-all duration-300">
                  <div class="flex justify-between items-start mb-6">
                    <div>
                      <h3 class="text-lg font-black text-white group-hover:text-indigo-400 transition-colors">{{ uni.name }}</h3>
                      <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mt-1">{{ uni.subjects_count }} مادة · {{ uni.colleges_count }} كلية</p>
                    </div>
                    <div class="p-3 bg-white/5 rounded-2xl text-slate-400 group-hover:text-indigo-400 transition-colors">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                  </div>
                  <div class="space-y-3">
                    <div class="flex justify-between text-xs font-bold text-slate-400 px-1">
                      <span>الإنجاز</span>
                      <span class="text-emerald-400">{{ uni.delivery_rate }}%</span>
                    </div>
                    <div class="h-2 bg-white/5 rounded-full overflow-hidden">
                      <div class="h-full bg-emerald-500/60 rounded-full transition-all duration-1000" :style="{ width: uni.delivery_rate + '%' }"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Access Sidebar -->
          <div class="space-y-8">
            <div class="glass-section p-8 rounded-[40px] border border-white/5 shadow-xl h-full">
              <h2 class="text-2xl font-black text-white mb-8 pr-2">اختصارات النظام</h2>
              <div class="grid grid-cols-1 gap-4">
                <template v-for="link in filteredQuickLinks" :key="link.href">
                  <Link :href="link.href" class="quick-access-btn group">
                    <div class="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-xl shadow-transparent group-hover:shadow-indigo-500/20">
                      <div class="w-6 h-6" v-html="link.icon"></div>
                    </div>
                    <div class="flex-1 text-right">
                      <span class="block text-sm font-black text-slate-200 group-hover:text-white transition-colors leading-tight">{{ link.label }}</span>
                      <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-1 group-hover:text-indigo-300">فتح الوحدة ←</span>
                    </div>
                  </Link>
                </template>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatsCard   from '@/Components/UI/StatsCard.vue';
import EmptyState  from '@/Components/UI/EmptyState.vue';

const props = defineProps({
  stats:            { type: Object, default: () => ({}) },
  university_stats: { type: Array,  default: () => [] },
});

const page = usePage();
const userRole = computed(() => page.props.auth.user.role);

const quickLinks = [
  { 
    label: 'إدارة المخزون الاستراتيجي', 
    href: route('admin.inventory.index'), 
    roles: ['admin'],
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>` 
  },
  { 
    label: 'الجامعات والكليات الجامعية', 
    href: route('admin.academic.index'), 
    roles: ['admin'],
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>` 
  },
  { 
    label: 'إدارة شؤون المناديب', 
    href: route('admin.delegates.index'), 
    roles: ['admin'],
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>` 
  },
  { 
    label: 'سجل العمليات والرقابة', 
    href: route('admin.reports.logs'), 
    roles: ['admin'],
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 17h6"/><path d="M9 12h6"/><path d="M9 7h6"/></svg>` 
  },
  { 
    label: 'إحصائيات أداء المناديب', 
    href: route('admin.reports.delegates-stats'), 
    roles: ['admin'],
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>` 
  },
  { 
    label: 'لوحة إدارة الطلاب', 
    href: props.university_stats?.[0]?.batch_id ? route('admin.academic.batch.show', page.props.auth.user.assignments?.[0]?.batch_id) : '#', 
    roles: ['general_delegate'],
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>` 
  },
];

const filteredQuickLinks = computed(() => {
  return quickLinks.filter(link => link.roles.includes(userRole.value));
});
</script>

<style scoped>
.dash-container {
    background-color: #0f172a;
    font-family: 'Inter', 'Noto Sans Arabic', sans-serif;
}

.welcome-card {
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.4) 0%, rgba(15, 23, 42, 0.4) 100%);
    backdrop-filter: blur(20px);
}

.premium-btn {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 16px 32px;
    border-radius: 20px;
    font-weight: 800;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.premium-btn.primary {
    background: #6366f1;
    color: white;
    box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
}

.premium-btn.primary:hover {
    background: #4f46e5;
    transform: translateY(-2px);
    box-shadow: 0 20px 40px -10px rgba(99, 102, 241, 0.5);
}

.premium-btn.secondary {
    background: rgba(255, 255, 255, 0.05);
    color: #cbd5e1;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.premium-btn.secondary:hover {
    background: rgba(255, 255, 255, 0.08);
    transform: translateY(-2px);
}

.glass-section {
    background: rgba(30, 41, 59, 0.4);
    backdrop-filter: blur(20px);
}

.quick-access-btn {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px;
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.04);
    transition: all 0.3s ease;
}

.quick-access-btn:hover {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(99, 102, 241, 0.2);
    transform: translateX(-4px);
}

.animate-in {
    animation-fill-mode: both;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInTop {
    from { transform: translateY(-40px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

@keyframes slideInBottom {
    from { transform: translateY(40px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.fade-in { animation: fadeIn 0.8s ease-out; }
.slide-in-from-top-10 { animation: slideInTop 1s cubic-bezier(0.2, 0.8, 0.2, 1); }
.slide-in-from-bottom-10 { animation: slideInBottom 1s cubic-bezier(0.2, 0.8, 0.2, 1); }

@media (max-width: 1024px) {
    .premium-btn { padding: 12px 24px; font-size: 0.875rem; }
}
</style>
