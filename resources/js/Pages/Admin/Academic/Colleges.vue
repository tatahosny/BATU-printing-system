<template>
  <div class="p-8 max-w-5xl mx-auto">
    <nav class="flex items-center gap-2 text-sm font-bold text-slate-400 mb-8">
      <Link :href="route('admin.academic.index')" class="hover:text-indigo-600">الجامعات</Link>
      <span>/</span>
      <span class="text-slate-900">{{ university.name }}</span>
    </nav>

    <div class="flex justify-between items-center mb-10">
      <div>
        <h1 class="text-3xl font-black text-slate-900">الكليات المتاحة</h1>
        <p class="text-slate-500 mt-2">إدارة كليات جامعة {{ university.name }}</p>
      </div>
      <button @click="openAddModal" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-bold hover:shadow-lg hover:shadow-indigo-500/30 transition-all">
        + إضافة كلية جديدة
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <AppCard v-for="college in colleges" :key="college.id" @click="goToCollege(college.id)">
        <div>
          <h3 class="text-lg font-black text-slate-800">{{ college.name }}</h3>
          <p class="text-xs text-slate-400 font-bold mt-1 uppercase">{{ college.departments_count }} قسم علمي</p>
        </div>

        <div class="flex gap-2 ml-4">
           <button @click.stop="editItem(college)" class="p-2 hover:bg-slate-100 rounded-lg text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" stroke-width="2" stroke-linecap="round"/></svg></button>
           <button @click.stop="deleteItem(college.id, 'college')" class="p-2 hover:bg-red-50 rounded-lg text-red-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round"/></svg></button>
        </div>
      </AppCard>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppCard from '@/Components/AppCard.vue';

const props = defineProps({ university: Object, colleges: Array });

const goToCollege = (id) => router.get(route('admin.academic.departments', id));

const deleteItem = (id, type) => {
    if(confirm('هل أنت متأكد؟ سيتم حذف كافة الأقسام والمواد التابعة لهذه الكلية!')) {
        router.delete(route('admin.academic.destroy', {id, type}));
    }
}
</script>
