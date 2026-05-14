<template>
  <AuthenticatedLayout>
    <div class="max-w-7xl mx-auto" dir="rtl">
      <div class="flex justify-between items-center mb-8 bg-white p-8 rounded-[35px] shadow-sm border border-slate-100">
        <div>
          <h1 class="text-3xl font-black text-slate-900 italic">مادة: {{ subject.name }}</h1>
          <p class="text-slate-500 font-bold mt-2">إدارة المذكرات والكتب الملحقة بالمادة</p>
        </div>
        <button @click="openAddMaterialModal" class="bg-indigo-600 text-white px-6 py-4 rounded-2xl font-black hover:bg-indigo-700 transition-all">
          + إضافة مذكرة/كتاب
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-for="material in subject.materials" :key="material.id" class="bg-white rounded-[35px] p-6 border border-slate-100 shadow-sm hover:shadow-xl transition-all group">
          <div class="flex justify-between mb-4">
            <div class="text-3xl">📚</div>
            <span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-lg text-xs font-black">
              {{ material.students_count }} طالب
            </span>
          </div>

          <h3 class="text-xl font-black text-slate-800 mb-4">{{ material.display_name }}</h3>

          <div class="space-y-3 mb-6">
            <div class="flex justify-between text-sm font-bold text-slate-500">
              <span>الترم:</span>
              <span class="text-indigo-600">الترم {{ subject.term == 1 ? 'الأول' : 'الثاني' }}</span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2">
            <Link :href="route('admin.materials.students', material.id)" class="bg-slate-900 text-white text-center py-3 rounded-xl text-xs font-black hover:bg-indigo-600 transition-all">
              عرض كشف الطلاب
            </Link>
            <button @click="triggerUpload(material.id)" class="bg-slate-100 text-slate-700 py-3 rounded-xl text-xs font-black hover:bg-indigo-100 transition-all">
              تحديث الشيت 📤
            </button>
          </div>
        </div>
      </div>
    </div>

    <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload">
  </AuthenticatedLayout>
</template>
