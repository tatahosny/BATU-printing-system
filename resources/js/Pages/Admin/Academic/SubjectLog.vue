<template>
  <AuthenticatedLayout>
    <div class="log-page" dir="rtl">
      <!-- Header -->
      <div class="header-section mb-6 flex justify-between items-end">
        <div>
          <Link :href="route('admin.academic.batch.show', subject.batch_id)" class="text-indigo-400 hover:text-indigo-300 text-sm font-bold flex items-center gap-1 mb-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            العودة للدفعة
          </Link>
          <h1 class="text-2xl font-black text-white flex items-center gap-3">
            سجل عمليات المادة: <span class="text-indigo-400">{{ subject.name }}</span>
          </h1>
          <p class="text-sm text-slate-400 mt-1">تتبع كافة حركات العهدة الخاصة بهذه المادة (توزيع، تعديل، استرجاع)</p>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="bg-slate-900/50 border border-white/10 rounded-2xl p-4 mb-6 flex flex-wrap gap-4 items-center">
        <div class="flex-1 min-w-[250px]">
          <div class="relative">
            <input 
              type="text" 
              v-model="searchQuery" 
              @input="handleSearch"
              placeholder="ابحث باسم المندوب أو المنفذ..." 
              class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 pr-10"
            />
            <svg class="w-5 h-5 text-slate-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-slate-900/40 border border-white/5 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
          <table class="w-full text-right text-sm">
            <thead class="bg-slate-800/50 text-slate-300 text-[11px] uppercase tracking-wider font-black">
              <tr>
                <th class="py-4 px-6 font-bold">نوع العملية</th>
                <th class="py-4 px-6 font-bold">المنفذ</th>
                <th class="py-4 px-6 font-bold text-center">الكمية</th>
                <th class="py-4 px-6 font-bold text-center">الرصيد قبل</th>
                <th class="py-4 px-6 font-bold text-center">الرصيد بعد</th>
                <th class="py-4 px-6 font-bold">التفاصيل / المندوب</th>
                <th class="py-4 px-6 font-bold">التاريخ والوقت</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-slate-300">
              <tr v-if="!transactions.data.length">
                <td colspan="7" class="py-12 text-center text-slate-500">
                  <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 mb-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    لا توجد أي حركات مسجلة لهذه المادة حتى الآن.
                  </div>
                </td>
              </tr>
              <tr v-for="tx in transactions.data" :key="tx.id" class="hover:bg-white/5 transition-colors group">
                <td class="py-4 px-6">
                  <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border" :class="getTypeClass(tx.type)">
                    {{ getTypeLabel(tx.type) }}
                  </span>
                </td>
                <td class="py-4 px-6 font-black text-white">{{ tx.user?.name || 'نظام' }}</td>
                <td class="py-4 px-6 text-center font-black text-indigo-400" dir="ltr">{{ tx.quantity }}</td>
                <td class="py-4 px-6 text-center text-slate-400 font-bold" dir="ltr">{{ tx.before_qty }}</td>
                <td class="py-4 px-6 text-center font-black text-white" dir="ltr">{{ tx.after_qty }}</td>
                <td class="py-4 px-6">
                  <p class="text-xs text-slate-100 font-bold mb-1 max-w-[250px] truncate" :title="tx.description">{{ tx.description }}</p>
                  <p v-if="tx.to_user_id" class="text-[10px] text-slate-500 font-black uppercase tracking-widest">
                    <span class="text-indigo-400">إلى:</span> {{ tx.to_user?.name }}
                  </p>
                </td>
                <td class="py-4 px-6 text-[11px] text-slate-500 font-black uppercase tracking-widest" dir="ltr">
                  {{ formatDate(tx.created_at) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div v-if="transactions.links && transactions.data.length" class="p-4 border-t border-white/5 bg-slate-800/30 flex justify-center">
          <Pagination :links="transactions.links" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue'; // تأكد من وجود هذا المكون أو حذفه إذا لم يكن موجوداً

const props = defineProps({
  subject: Object,
  transactions: Object,
  filters: Object,
});

const searchQuery = ref(props.filters.search || '');

const handleSearch = debounce(() => {
  router.get(route('admin.academic.subject.log', props.subject.id), { search: searchQuery.value }, { preserveState: true, preserveScroll: true, replace: true });
}, 300);

const getTypeLabels = {
  add_stock: 'إضافة للمخزن',
  distribute: 'توزيع لمندوب',
  receive: 'استلام عهدة',
  return: 'استرجاع للمخزن',
  reset: 'تصفير رصيد',
  adjustment: 'تعديل يدوي'
};

const getTypeClasses = {
  add_stock: 'bg-emerald-500/10 text-emerald-400',
  distribute: 'bg-indigo-500/10 text-indigo-400',
  receive: 'bg-blue-500/10 text-blue-400',
  return: 'bg-amber-500/10 text-amber-400',
  reset: 'bg-red-500/10 text-red-400',
  adjustment: 'bg-slate-500/10 text-slate-400'
};

function getTypeLabel(type) {
  return getTypeLabels[type] || type;
}

function getTypeClass(type) {
  return getTypeClasses[type] || 'bg-slate-500/10 text-slate-400';
}

function formatDate(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleString('ar-EG', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}
</script>

<style scoped>
.log-page {
  padding: 1.5rem;
  max-width: 1200px;
  margin: 0 auto;
}
</style>
