<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import Modal from '@/Components/UI/Modal.vue';
import Input from '@/Components/UI/Input.vue';

const props = defineProps({
    university: Object,
});

const showModal = ref(false);
const modalTitle = ref('');

const form = useForm({
    id: null,
    name: '',
    type: '',
    parent_id: null,
    university_id: props.university.id,
    college_id: null,
    department_id: null,
    term: '1',
});

const openModal = (type, parentId = null, collegeId = null, deptId = null) => {
    form.reset();
    form.type = type;
    form.parent_id = parentId || props.university.id;
    form.university_id = props.university.id;
    form.college_id = collegeId;
    form.department_id = deptId;

    const titles = {
        college: 'كلية',
        department: 'قسم',
        batch: 'فرقة دراسية',
        subject: 'مادة دراسية',
    };
    modalTitle.value = titles[type];
    showModal.value = true;
};

const submit = () => {
    form.post(route('admin.academic.upsert'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
};

const deleteItem = (id, type) => {
    if (confirm('هل أنت متأكد من حذف هذا العنصر؟ سيتم حذف جميع التوابع له.')) {
        form.delete(route('admin.academic.destroy', { id: id, type: type }));
    }
};
</script>

<template>
    <Head :title="'إدارة ' + university.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-500">
                <Link :href="route('admin.academic.index')" class="hover:text-indigo-400 transition-colors">الجامعات</Link>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
                <span class="text-slate-300">{{ university.name }}</span>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-10 pb-20">
            <!-- University Profile Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 bg-slate-900/40 p-8 rounded-[40px] border border-white/5 backdrop-blur-xl">
                <div class="flex items-center gap-6">
                    <div class="w-20 h-20 bg-indigo-600 rounded-[32px] flex items-center justify-center text-4xl shadow-2xl shadow-indigo-600/20">
                        🏫
                    </div>
                    <div class="space-y-2">
                        <h2 class="text-3xl font-black text-white tracking-tight">{{ university.name }}</h2>
                        <div class="flex items-center gap-4 text-slate-400 text-sm font-medium">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 bg-indigo-500 rounded-full"></span> {{ university.colleges?.length || 0 }} كليات</span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 bg-emerald-500 rounded-full"></span> هيكل أكاديمي معتمد</span>
                        </div>
                    </div>
                </div>
                <Button @click="openModal('college')" class="!rounded-2xl !py-4 shadow-xl shadow-indigo-600/20">
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    إضافة كلية جديدة
                </Button>
            </div>

            <!-- Colleges List -->
            <div class="space-y-12">
                <div v-for="college in university.colleges" :key="college.id" class="group/college">
                    <div class="flex justify-between items-center mb-6 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-8 bg-indigo-500 rounded-full group-hover/college:scale-y-125 transition-transform duration-300"></div>
                            <h3 class="text-2xl font-black text-white">كلية {{ college.name }}</h3>
                        </div>
                        <div class="flex gap-3">
                            <Button variant="secondary" size="sm" @click="openModal('department', college.id)" class="!rounded-xl text-[10px] uppercase font-black tracking-widest px-4">
                                + قسم جديد
                            </Button>
                            <button @click="deleteItem(college.id, 'college')" class="p-2 text-slate-600 hover:text-rose-500 hover:bg-rose-500/10 rounded-xl transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div v-for="dept in college.departments" :key="dept.id" class="bg-slate-900/20 rounded-[32px] border border-white/5 p-8 hover:border-white/10 transition-colors">
                            <div class="flex justify-between items-center mb-8 pb-4 border-b border-white/5">
                                <h4 class="font-black text-slate-100 text-lg flex items-center gap-2">
                                    <span class="text-indigo-400">#</span> قسم {{ dept.name }}
                                </h4>
                                <button @click="openModal('batch', dept.id, college.id)" class="text-[10px] font-black text-indigo-400 uppercase tracking-widest hover:text-indigo-300 transition-colors">
                                    + فرقة دراسية
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <Link 
                                    v-for="batch in dept.batches" 
                                    :key="batch.id"
                                    :href="route('admin.academic.batch.show', batch.id)"
                                    class="bg-white/2 p-5 rounded-2xl border border-white/5 hover:border-indigo-500/30 hover:bg-white/5 transition-all group/batch shadow-inner"
                                >
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-black text-slate-200 group-hover/batch:text-white transition-colors">{{ batch.name }}</span>
                                        <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center text-slate-500 group-hover/batch:bg-indigo-600 group-hover/batch:text-white transition-all shadow-lg">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex items-center gap-2">
                                        <span class="text-[10px] text-slate-500 font-black uppercase tracking-widest">
                                            {{ batch.subjects?.length || 0 }} مادة دراسية
                                        </span>
                                    </div>
                                </Link>
                            </div>

                            <div v-if="dept.batches.length === 0" class="text-center py-8 text-slate-600 text-xs font-bold italic border border-dashed border-white/5 rounded-2xl">
                                لا توجد فرق دراسية مسجلة حالياً
                            </div>
                        </div>

                        <div v-if="college.departments.length === 0" class="lg:col-span-2 text-center py-20 text-slate-600 bg-white/1 rounded-[32px] border border-dashed border-white/5">
                            <div class="text-3xl mb-4 opacity-20">📂</div>
                            <p class="font-bold text-sm">لم يتم إضافة أقسام لهذه الكلية بعد</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upsert Modal -->
        <Modal :show="showModal" @close="showModal = false" max-width="md">
            <div class="bg-slate-900 border border-white/10 rounded-[32px] overflow-hidden shadow-2xl">
                <form @submit.prevent="submit" class="p-10 space-y-8">
                    <div>
                        <h2 class="text-2xl font-black text-white mb-2">إضافة {{ modalTitle }}</h2>
                        <p class="text-sm text-slate-400 font-medium">يرجى ملء البيانات التالية بدقة.</p>
                    </div>

                    <div class="space-y-6">
                        <Input
                            label="الاسم المعتمد"
                            v-model="form.name"
                            :placeholder="'مثلاً: ' + modalTitle"
                            required
                            :error="form.errors.name"
                            class="!bg-slate-800/50"
                        />

                        <div v-if="form.type === 'subject'">
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">الترم الدراسي</label>
                            <select v-model="form.term" class="block w-full rounded-2xl border-white/10 bg-slate-800/50 py-3.5 text-white ring-1 ring-inset ring-white/5 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                                <option value="1">الفصل الدراسي الأول</option>
                                <option value="2">الفصل الدراسي الثاني</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <Button type="submit" class="flex-1 !rounded-2xl !py-4 shadow-lg shadow-indigo-600/20" :loading="form.processing">حفظ البيانات</Button>
                        <Button type="button" variant="secondary" class="flex-1 !rounded-2xl !py-4" @click="showModal = false">إلغاء</Button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
.scale-in-center {
    animation: scale-in-center 0.3s cubic-bezier(0.250, 0.460, 0.450, 0.940) both;
}
@keyframes scale-in-center {
    0% { transform: scale(0.9); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
</style>
