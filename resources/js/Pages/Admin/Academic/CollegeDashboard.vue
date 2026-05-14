<template>
  <AuthenticatedLayout>
    <div class="mb-8 bg-white p-8 rounded-[35px] border border-slate-100 shadow-sm flex justify-between items-end">
      <div>
        <h1 class="text-4xl font-black text-slate-900 italic">كلية {{ college.name }}</h1>
        <div class="flex gap-4 mt-4">
          <div class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl font-bold text-sm">📦 المخزن: 500 ورقة</div>
          <div class="bg-emerald-50 text-emerald-600 px-4 py-2 rounded-xl font-bold text-sm">📑 التسليمات: 120 شيت</div>
        </div>
      </div>
      <button @click="openAddSubjectModal" class="bg-slate-900 text-white px-6 py-4 rounded-2xl font-black hover:bg-indigo-600 transition-all">
        + إضافة مادة دراسية
      </button>
    </div>

    <div class="bg-white rounded-[35px] border border-slate-100 shadow-sm overflow-hidden">
      <table class="w-full text-right">
        <thead class="bg-slate-50 text-slate-500 font-black text-sm">
          <tr>
            <th class="p-6">المادة</th>
            <th class="p-6">القسم / الفرقة</th>
            <th class="p-6">الترم</th>
            <th class="p-6">ظهور للمندوب</th>
            <th class="p-6">عدد الطلاب (بدون تكرار)</th>
            <th class="p-6">الإجراءات</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="subject in college.subjects" :key="subject.id" class="hover:bg-slate-50/50 transition-all">
            <td class="p-6 font-black text-slate-800">{{ subject.name }}</td>
            <td class="p-6 text-slate-500 font-bold">{{ subject.department.name }} - {{ subject.batch.name }}</td>
            <td class="p-6 text-indigo-600 font-black italic">الترم {{ subject.term == 1 ? 'الأول' : 'الثاني' }}</td>
            <td class="p-6">
              <button @click="toggleVisibility(subject)" :class="subject.is_visible ? 'bg-emerald-500' : 'bg-slate-200'" class="w-12 h-6 rounded-full relative transition-all">
                <div :class="subject.is_visible ? 'translate-x-[-24px]' : 'translate-x-[-2px]'" class="absolute top-1 right-1 bg-white w-4 h-4 rounded-full transition-all"></div>
              </button>
            </td>
            <td class="p-6 font-black">{{ subject.students_count }}</td>
            <td class="p-6 flex gap-2">
              <button @click="triggerUpload(subject.id)" class="bg-slate-100 p-3 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 transition-all" title="رفع شيت الطلاب">
                📤 رفع الشيت
              </button>
              <button class="bg-slate-100 p-3 rounded-xl hover:bg-rose-50 hover:text-rose-600 transition-all">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload">
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ college: Object });
const fileInput = ref(null);
const currentSubjectId = ref(null);

const triggerUpload = (id) => {
  currentSubjectId.value = id;
  fileInput.value.click();
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  const form = useForm({
    excel_file: file,
    subject_id: currentSubjectId.value
  });

  form.post(route('admin.academic.upload-sheet'), {
    onSuccess: () => alert('تم رفع الشيت ومعالجة البيانات بنجاح!'),
  });
};
</script>
