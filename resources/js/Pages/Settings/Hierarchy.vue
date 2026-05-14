<template>
  <div class="min-h-screen bg-slate-50 p-10">
    <div class="max-w-4xl mx-auto">
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-2xl font-bold text-slate-900">Academic Structure</h1>
          <p class="text-slate-500">Define your organization's hierarchy from University down to Subjects.</p>
        </div>
        <BaseButton @click="showUnivModal = true">+ Add University</BaseButton>
      </div>

      <div v-for="univ in universities" :key="univ.id" class="mb-6 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-indigo-600 text-white rounded-lg flex items-center justify-center font-bold">U</div>
            <h3 class="font-bold text-slate-800">{{ univ.name }}</h3>
          </div>
          <button @click="openCollegeModal(univ)" class="text-xs font-bold text-indigo-600 hover:underline">+ Add College</button>
        </div>

        <div class="p-5 space-y-4">
          <div v-for="college in univ.colleges" :key="college.id" class="pl-6 border-l-2 border-slate-100">
            <div class="flex justify-between items-center mb-2">
              <h4 class="text-sm font-bold text-slate-700">🏢 {{ college.name }}</h4>
              <button class="text-[10px] uppercase tracking-wider font-black text-slate-400 hover:text-indigo-500">+ Add Dept</button>
            </div>

            <div class="grid grid-cols-2 gap-3 mt-3">
              <div v-for="dept in college.departments" :key="dept.id" class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex justify-between items-center">
                <span class="text-xs font-medium text-slate-600">{{ dept.name }}</span>
                <span class="text-[10px] bg-white px-2 py-1 rounded border border-slate-200 text-slate-400">
                  {{ dept.batches_count || 0 }} Batches
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showUnivModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-[100]">
      <div class="bg-white p-8 rounded-3xl w-full max-w-md shadow-2xl">
        <h2 class="text-xl font-bold mb-4">Add New University</h2>
        <input v-model="univForm.name" type="text" placeholder="e.g. Cairo University" class="w-full p-3 border border-slate-200 rounded-xl mb-6 outline-none focus:ring-2 focus:ring-indigo-500">
        <div class="flex gap-3">
          <BaseButton class="flex-1" @click="submitUniversity">Save University</BaseButton>
          <button @click="showUnivModal = false" class="flex-1 text-sm font-bold text-slate-500">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseButton from '@/Components/BaseButton.vue';

defineProps({ universities: Array });

const showUnivModal = ref(false);
const univForm = useForm({ name: '' });

const submitUniversity = () => {
  univForm.post(route('hierarchy.university.store'), {
    onSuccess: () => {
        showUnivModal.value = false;
        univForm.reset();
    }
  });
};
</script>
