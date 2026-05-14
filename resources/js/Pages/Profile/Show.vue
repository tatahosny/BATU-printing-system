<template>
  <Head title="الملف الشخصي والإحصائيات" />

  <div class="min-h-screen bg-[#F8FAFC] pb-12">
    <div class="bg-slate-900 pt-12 pb-24 px-8 rounded-b-[50px]">
      <div class="max-w-4xl mx-auto flex items-center gap-6">
        <div class="w-20 h-20 bg-indigo-500 rounded-[28px] border-4 border-slate-800 flex items-center justify-center text-3xl font-black text-white shadow-2xl">
          {{ $page.props.auth.user.name.charAt(0) }}
        </div>
        <div>
          <h1 class="text-2xl font-bold text-white">{{ $page.props.auth.user.name }}</h1>
          <p class="text-indigo-300 text-sm font-medium">{{ $page.props.auth.user.role.replace('_', ' ') }}</p>
        </div>
      </div>
    </div>

    <div class="max-w-4xl mx-auto px-6 -mt-12">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-6 rounded-[32px] shadow-xl shadow-slate-200/50 border border-slate-100 relative overflow-hidden">
          <div class="absolute -right-4 -top-4 w-16 h-16 bg-blue-50 rounded-full"></div>
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">إجمالي المستلم (العُهدة)</p>
          <p class="text-3xl font-black text-slate-900">{{ stats.total_received }} <span class="text-sm font-bold text-slate-300">نسخة</span></p>
        </div>

        <div class="bg-white p-6 rounded-[32px] shadow-xl shadow-slate-200/50 border border-slate-100 relative overflow-hidden">
          <div class="absolute -right-4 -top-4 w-16 h-16 bg-green-50 rounded-full"></div>
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">تم توزيعه للطلاب</p>
          <p class="text-3xl font-black text-green-600">{{ stats.total_delivered }} <span class="text-sm font-bold text-slate-300">طالب</span></p>
        </div>

        <div class="bg-indigo-600 p-6 rounded-[32px] shadow-xl shadow-indigo-200 border border-indigo-500 relative overflow-hidden">
          <p class="text-[10px] font-black text-indigo-200 uppercase tracking-widest mb-1">الرصيد الحالي معك</p>
          <p class="text-3xl font-black text-white">{{ stats.current_stock }} <span class="text-sm font-bold opacity-50">نسخة</span></p>
        </div>
      </div>

      <div class="mt-10 bg-white rounded-[40px] border border-slate-200 p-8 shadow-sm">
        <h3 class="text-lg font-black text-slate-900 mb-6 flex items-center gap-2">
          <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round"/></svg>
          آخر عمليات الاستلام
        </h3>

        <div class="space-y-4">
          <div v-for="log in recentActivity" :key="log.id" class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl border border-slate-100">
            <div>
              <p class="text-sm font-bold text-slate-800">استلمت من {{ log.from_user.name }}</p>
              <p class="text-[10px] text-slate-400 font-bold uppercase">مادة: {{ log.subject.name }}</p>
            </div>
            <div class="bg-white px-4 py-2 rounded-xl border border-slate-200 font-black text-indigo-600">
              +{{ log.quantity }}
            </div>
          </div>

          <div v-if="recentActivity.length === 0" class="py-10 text-center text-slate-400 italic">
            لا توجد عمليات استلام مسجلة حالياً
          </div>
        </div>
      </div>

      <button @click="logout" class="w-full mt-8 py-5 bg-red-50 text-red-500 rounded-[24px] font-black text-sm hover:bg-red-500 hover:text-white transition-all duration-300 border border-red-100 shadow-sm">
        تسجيل الخروج من النظام
      </button>
    </div>
  </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';

defineProps({
  stats: Object,
  recentActivity: Array
});

const logout = () => router.post(route('logout'));
</script>
