<template>
  <AuthenticatedLayout>
    <div class="log-page" dir="rtl">
      <!-- Header -->
      <div class="header-section mb-6 flex justify-between items-end">
        <div>
          <Link :href="route('admin.delegates.index')" class="text-indigo-400 hover:text-indigo-300 text-sm font-bold flex items-center gap-1 mb-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            العودة لقائمة المستخدمين
          </Link>
          <h1 class="text-2xl font-black text-white flex items-center gap-3">
            سجل نشاط المستخدم: <span class="text-indigo-400">{{ delegate.name }}</span>
          </h1>
          <p class="text-sm text-slate-400 mt-1">تتبع كافة تحركات المستخدم من تصفح، وبحث، ورفع ملفات</p>
        </div>
      </div>

      <!-- Timeline/Table -->
      <div class="bg-slate-900/40 border border-white/5 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
          <table class="w-full text-right text-sm">
            <thead class="bg-slate-800/50 text-slate-300 text-[11px] uppercase tracking-wider font-black">
              <tr>
                <th class="py-4 px-6 font-bold">نوع النشاط</th>
                <th class="py-4 px-6 font-bold">الصفحة / المسار</th>
                <th class="py-4 px-6 font-bold">التفاصيل</th>
                <th class="py-4 px-6 font-bold text-left">التاريخ والوقت</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-slate-300">
              <tr v-if="!logs.data.length">
                <td colspan="4" class="py-12 text-center text-slate-500">
                  <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 mb-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    لا توجد أي نشاطات مسجلة لهذا المستخدم حتى الآن.
                  </div>
                </td>
              </tr>
              <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-800/30 transition-colors">
                <td class="py-4 px-6">
                  <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold" :class="getActionClass(log.action)">
                    {{ getActionLabel(log.action) }}
                  </span>
                </td>
                <td class="py-4 px-6 text-slate-400 font-mono text-xs" dir="ltr">/{{ log.url }}</td>
                <td class="py-4 px-6">
                  <!-- Search payload -->
                  <div v-if="log.action === 'search' && log.payload?.query" class="text-sm">
                    بحث عن: <span class="font-bold text-amber-400">"{{ log.payload.query }}"</span>
                  </div>
                  
                  <!-- File upload payload -->
                  <div v-else-if="log.action === 'file_upload' && log.payload?.files" class="text-sm">
                    قام برفع ملفات:
                    <ul class="list-disc list-inside mt-1">
                      <li v-for="file in log.payload.files" :key="file" class="text-emerald-400 font-bold text-xs">{{ file }}</li>
                    </ul>
                  </div>
                  
                  <!-- Action execution payload -->
                  <div v-else-if="log.action === 'action_execution' && log.payload?.inputs" class="text-sm text-slate-400">
                    <span class="italic text-xs">عملية إدخال بيانات</span>
                  </div>

                  <div v-else class="text-sm text-slate-500">
                    -
                  </div>
                </td>
                <td class="py-4 px-6 text-[11px] text-slate-400 font-medium text-left" dir="ltr">
                  {{ formatDate(log.created_at) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div v-if="logs.links && logs.data.length" class="p-4 border-t border-white/5 bg-slate-800/30 flex justify-center">
          <Pagination :links="logs.links" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue'; 

const props = defineProps({
  delegate: Object,
  logs: Object,
});

const getActionLabels = {
  page_view: 'تصفح صفحة',
  search: 'بحث',
  file_upload: 'رفع ملفات',
  action_execution: 'تنفيذ عملية'
};

const getActionClasses = {
  page_view: 'bg-blue-500/10 text-blue-400',
  search: 'bg-amber-500/10 text-amber-400',
  file_upload: 'bg-emerald-500/10 text-emerald-400',
  action_execution: 'bg-indigo-500/10 text-indigo-400'
};

function getActionLabel(action) {
  return getActionLabels[action] || action;
}

function getActionClass(action) {
  return getActionClasses[action] || 'bg-slate-500/10 text-slate-400';
}

function formatDate(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleString('ar-EG', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
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
