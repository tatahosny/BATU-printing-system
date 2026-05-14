<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import Modal from '@/Components/UI/Modal.vue';
import Input from '@/Components/UI/Input.vue';

const props = defineProps({
    universities: Array,
});

const showModal = ref(false);
const searchQuery = ref('');

const filteredUniversities = computed(() => {
    if (!searchQuery.value) return props.universities;
    return props.universities.filter(uni => 
        uni.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const form = useForm({
    id: null,
    name: '',
    type: 'university',
    parent_id: null,
});

const submit = () => {
    form.post(route('admin.academic.upsert'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
};

const deleteItem = (id) => {
    if (confirm('سيتم حذف الجامعة وكافة الكليات والأقسام والمواد التابعة لها.. هل أنت متأكد؟')) {
        form.delete(route('admin.academic.destroy', { id: id, type: 'university' }));
    }
};
</script>

<template>
    <Head title="إدارة الجامعات" />

    <AuthenticatedLayout>
        <template #header>
            إدارة الهيكل الأكاديمي
        </template>

        <div class="max-w-7xl mx-auto space-y-10 pb-20">
            <!-- Header Section -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-500/10 border border-indigo-500/20 rounded-full">
                        <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">مستوى النظام العالمي</span>
                    </div>
                    <h2 class="text-3xl lg:text-4xl font-black text-white tracking-tight">الجامعات والمؤسسات</h2>
                    <p class="text-slate-400 font-medium max-w-xl">إدارة المؤسسات التعليمية المسجلة في النظام والتحكم في هيكلها الهرمي.</p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
                    <div class="relative group flex-1 sm:w-64">
                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-focus-within:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="بحث عن جامعة..." 
                            class="w-full bg-slate-900/50 border border-white/5 rounded-2xl py-3 pr-11 pl-4 text-sm text-white placeholder:text-slate-600 focus:border-indigo-500/50 focus:ring-0 transition-all shadow-inner"
                        />
                    </div>
                    <Button @click="showModal = true" class="!rounded-2xl !py-4 shadow-xl shadow-indigo-600/20">
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        إضافة جامعة جديدة
                    </Button>
                </div>
            </div>

            <!-- Grid -->
            <div v-if="filteredUniversities.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                <Card v-for="uni in filteredUniversities" :key="uni.id" no-padding class="group relative overflow-hidden !rounded-[32px] border-white/5 bg-slate-900/40 backdrop-blur-xl hover:border-indigo-500/30 transition-all duration-500">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    
                    <div class="p-8 relative z-10">
                        <div class="flex justify-between items-start mb-8">
                            <div class="w-14 h-14 bg-white/5 border border-white/5 rounded-2xl flex items-center justify-center text-3xl shadow-2xl group-hover:bg-indigo-600 group-hover:text-white group-hover:scale-110 transition-all duration-500 group-hover:shadow-indigo-600/30">
                                🏫
                            </div>
                            <button @click="deleteItem(uni.id)" class="p-2.5 text-slate-600 hover:text-rose-500 hover:bg-rose-500/10 rounded-xl transition-all duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>

                        <div class="space-y-4 mb-10">
                            <h3 class="text-2xl font-black text-white group-hover:text-indigo-300 transition-colors duration-300">{{ uni.name }}</h3>
                            
                            <div class="grid grid-cols-3 gap-3">
                                <div class="bg-white/5 rounded-2xl p-3 border border-white/5 text-center">
                                    <p class="text-[8px] font-black text-slate-500 uppercase mb-1">كليات</p>
                                    <p class="text-sm font-black text-indigo-400">{{ uni.colleges_count }}</p>
                                </div>
                                <div class="bg-white/5 rounded-2xl p-3 border border-white/5 text-center">
                                    <p class="text-[8px] font-black text-slate-500 uppercase mb-1">مواد</p>
                                    <p class="text-sm font-black text-purple-400">{{ uni.subjects_count }}</p>
                                </div>
                                <div class="bg-white/5 rounded-2xl p-3 border border-white/5 text-center">
                                    <p class="text-[8px] font-black text-slate-500 uppercase mb-1">طلاب</p>
                                    <p class="text-sm font-black text-emerald-400">{{ uni.students_count }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-500/5 border border-emerald-500/10 rounded-full w-fit">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                <span class="text-[9px] font-black text-emerald-500/70 uppercase tracking-[0.2em]">متصل بالقاعدة</span>
                            </div>
                        </div>

                        <Link :href="route('admin.academic.university.show', uni.id)" class="block">
                            <button class="w-full py-4 px-6 bg-white/5 hover:bg-indigo-600 text-white rounded-[20px] font-black text-sm transition-all duration-300 flex items-center justify-between group/btn shadow-inner">
                                <span>عرض الهيكل الإداري</span>
                                <svg class="w-5 h-5 group-hover/btn:translate-x-[-4px] transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            </button>
                        </Link>
                    </div>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center py-32 bg-slate-900/20 rounded-[40px] border-2 border-dashed border-white/5">
                <div class="w-24 h-24 bg-white/5 rounded-[30px] flex items-center justify-center text-5xl mb-8 animate-bounce">🏛️</div>
                <h3 class="text-2xl font-black text-white mb-2">لا توجد جامعات</h3>
                <p class="text-slate-500 font-medium mb-10">ابدأ ببناء الهيكل الأكاديمي عبر إضافة أول جامعة للمنظومة.</p>
                <Button @click="showModal = true" size="lg" class="shadow-2xl shadow-indigo-600/20">إضافة جامعة الآن</Button>
            </div>
        </div>

        <!-- Add University Modal -->
        <Modal :show="showModal" @close="showModal = false" max-width="md">
            <div class="bg-slate-900 border border-white/10 rounded-[32px] overflow-hidden shadow-2xl">
                <form @submit.prevent="submit" class="p-10 space-y-8">
                    <div>
                        <h2 class="text-2xl font-black text-white mb-2">إضافة جامعة جديدة</h2>
                        <p class="text-sm text-slate-400 font-medium">أدخل الاسم الرسمي للجامعة لضمها لقاعدة البيانات.</p>
                    </div>

                    <Input
                        label="اسم الجامعة الرسمي"
                        v-model="form.name"
                        placeholder="مثلاً: جامعة الملك سعود"
                        required
                        :error="form.errors.name"
                        class="!bg-slate-800/50"
                    />

                    <div class="flex gap-4 pt-4">
                        <Button type="submit" class="flex-1 !rounded-2xl !py-4 shadow-lg shadow-indigo-600/20" :loading="form.processing">تأكيد الإضافة</Button>
                        <Button type="button" variant="secondary" class="flex-1 !rounded-2xl !py-4" @click="showModal = false">إلغاء</Button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
