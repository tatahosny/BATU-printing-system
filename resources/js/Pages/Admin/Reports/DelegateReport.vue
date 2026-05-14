<template>
  <div class="p-8">
    <h1 class="text-3xl font-black text-slate-900 mb-8">تصفية حسابات المناديب</h1>

    <div class="bg-white rounded-[32px] border border-slate-100 overflow-hidden shadow-sm">
      <table class="w-full text-right">
        <thead class="bg-slate-50 border-b border-slate-100">
          <tr>
            <th class="p-6 font-black text-slate-500 text-xs uppercase">المندوب</th>
            <th class="p-6 font-black text-slate-500 text-xs uppercase">إجمالي المستلم</th>
            <th class="p-6 font-black text-slate-500 text-xs uppercase">تم توزيعه</th>
            <th class="p-6 font-black text-slate-500 text-xs uppercase">مرتجعات</th>
            <th class="p-6 font-black text-slate-500 text-xs uppercase text-indigo-600">الرصيد الحالي</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="report in reports" :key="report.id" class="hover:bg-slate-50 transition-colors">
            <td class="p-6 font-bold text-slate-900">{{ report.name }}</td>
            <td class="p-6 font-bold text-slate-600">{{ report.received }}</td>
            <td class="p-6 font-bold text-green-600">{{ report.delivered }}</td>
            <td class="p-6 font-bold text-orange-500">{{ report.returned }}</td>
            <td class="p-6 font-black text-indigo-600">{{ report.stock }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
