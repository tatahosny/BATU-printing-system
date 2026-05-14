<template>
  <div class="max-w-4xl mx-auto p-8">
    <div class="bg-white border-2 border-dashed border-slate-200 rounded-[40px] p-12 text-center hover:border-indigo-500 transition-all"
         @dragover.prevent @drop.prevent="handleDrop">
      <input type="file" multiple @change="handleFileSelect" class="hidden" ref="fileInput">
      <div class="space-y-4">
        <div class="bg-indigo-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-indigo-600">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h2 class="text-xl font-black text-slate-900">اسحب ملفات الإكسل هنا</h2>
        <p class="text-slate-400 font-bold text-sm">يمكنك رفع من 2 إلى 5 ملفات للمادة الواحدة</p>
        <button @click="$refs.fileInput.click()" class="bg-slate-900 text-white px-8 py-3 rounded-2xl font-black text-sm">اختر الملفات</button>
      </div>
    </div>

    <div v-if="files.length > 0" class="mt-8 space-y-3">
      <div v-for="(file, index) in files" :key="index" class="bg-white p-4 rounded-2xl border border-slate-100 flex justify-between items-center">
        <span class="font-bold text-slate-700">{{ file.name }}</span>
        <button @click="removeFile(index)" class="text-red-500 font-black text-xs">حذف</button>
      </div>
      <button @click="uploadFiles" :disabled="loading" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black shadow-lg shadow-indigo-200">
        {{ loading ? 'جاري المعالجة ودمج البيانات...' : 'بدء الرفع والدمج الذكي' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({ subject: Object });
const files = ref([]);
const loading = ref(false);

const handleFileSelect = (e) => { files.value = [...files.value, ...e.target.files]; };
const handleDrop = (e) => { files.value = [...files.value, ...e.dataTransfer.files]; };
const removeFile = (i) => files.value.splice(i, 1);

const uploadFiles = () => {
  loading.value = true;
  const formData = new FormData();
  files.value.forEach(file => formData.append('files[]', file));

  router.post(route('inventory.upload', props.subject.id), formData, {
    onFinish: () => loading.value = false
  });
};
</script>
