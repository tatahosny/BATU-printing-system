<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import Modal from '@/Components/UI/Modal.vue';
import Input from '@/Components/UI/Input.vue';
import FileUpload from '@/Components/UI/FileUpload.vue';
import Table from '@/Components/UI/Table.vue';

const props = defineProps({
    batch: Object,
    potential_supervisors: Array,
    batch_students: Object,
    filters: Object
});

const showModal = ref(false);
const modalType = ref('');
const activeTab = ref('academic'); // 'academic' or 'students'

// Student Search
const studentSearch = ref(props.filters?.search || '');
const supervisorSearch = ref('');

const watchStudentSearch = (val) => {
    router.get(route('admin.academic.batch.show', props.batch.id), { search: val }, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
};

// Simple debounced search (manual)
let searchTimeout;
const handleStudentSearch = (e) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        watchStudentSearch(e.target.value);
    }, 500);
};

const filteredSupervisors = computed(() => {
    if (!supervisorSearch.value) return props.potential_supervisors;
    const q = supervisorSearch.value.toLowerCase();
    return props.potential_supervisors.filter(u => 
        u.name.toLowerCase().includes(q) || 
        u.email.toLowerCase().includes(q)
    );
});

const form = useForm({
    id: null,
    name: '',
    type: '',
    parent_id: props.batch.id,
    university_id: props.batch.university_id || props.batch.department?.college?.university_id,
    college_id: props.batch.college_id || props.batch.department?.college?.id,
    department_id: props.batch.department_id,
    section_id: null,
    term: '1',
    range_start: '',
    range_end: '',
});

const autoAssign = () => {
    if (confirm('سيتم توزيع جميع طلاب الدفعة على السكاشن والمناديب بناءً على نطاقات الأكواد المعرفة. هل تريد الاستمرار؟')) {
        router.post(route('admin.academic.batch.auto-assign', props.batch.id));
    }
};

const assignForm = useForm({
    user_id: '',
    university_id: props.batch.university_id || props.batch.department?.college?.university_id,
    college_id: props.batch.college_id || props.batch.department?.college?.id,
    department_id: props.batch.department_id,
    batch_id: props.batch.id,
    section_id: null,
});

const uploadForm = useForm({
    sheets: [],
    batch_id: props.batch.id
});

const openModal = (type, section = null) => {
    form.reset();
    form.type = type;
    form.parent_id = props.batch.id;
    
    if (type === 'assign_supervisor') {
        assignForm.reset();
        supervisorSearch.value = '';
        assignForm.section_id = section.id;
        assignForm.university_id = props.batch.university_id || props.batch.department?.college?.university_id;
        assignForm.college_id = props.batch.college_id || props.batch.department?.college?.id;
        assignForm.department_id = props.batch.department_id;
        assignForm.batch_id = props.batch.id;
        modalType.value = 'تعيين مسؤول سكشن';
    } else if (section) {
        // Edit mode
        form.id = section.id;
        form.name = section.name;
        form.range_start = section.range_start;
        form.range_end = section.range_end;
        modalType.value = 'تعديل سكشن';
    } else {
        modalType.value = type === 'section' ? 'سكشن' : 'مادة';
    }
    showModal.value = true;
};

const transferForm = useForm({
    subject_id: '',
    delegate_id: '',
    quantity: 1,
});

const openTransferModal = (section) => {
    const supervisor = section.assignments?.find(a => a.section_id == section.id)?.user;
    if (!supervisor) {
        alert('يجب تعيين مسؤول للسكشن أولاً ليتم التسليم له');
        return;
    }
    transferForm.reset();
    transferForm.delegate_id = supervisor.id;
    modalType.value = 'تسليم عهدة للسكشن: ' + section.name;
    showModal.value = true;
};

const submitTransfer = () => {
    transferForm.post(route('admin.inventory.distribute'), {
        onSuccess: () => {
            showModal.value = false;
            transferForm.reset();
        }
    });
};

const submit = () => {
    if (modalType.value.startsWith('تسليم عهدة للسكشن')) {
        submitTransfer();
        return;
    }

    if (modalType.value === 'تعيين مسؤول سكشن') {
        if (!assignForm.user_id) {
            alert('يرجى اختيار المندوب أولاً');
            return;
        }
        assignForm.post(route('admin.delegates.assign', assignForm.user_id), {
            onSuccess: () => {
                showModal.value = false;
                assignForm.reset();
            },
        });
        return;
    }

    form.post(route('admin.academic.upsert'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
};

const deleteItem = (id, type) => {
    if (confirm('هل أنت متأكد من حذف هذا العنصر؟')) {
        form.delete(route('admin.academic.destroy', { id, type }));
    }
};

const toggleSubject = (subject) => {
    form.post(route('admin.academic.subject.toggle', subject.id));
};

const handleUpload = (subjectId, files) => {
    uploadForm.sheets = files;
    uploadForm.post(route('admin.inventory.upload-master', subjectId), {
        onSuccess: () => {
            uploadForm.reset();
            alert('تم رفع الكشوفات بنجاح!');
        }
    });
};

const handleMasterUpload = (files) => {
    uploadForm.sheets = files;
    uploadForm.post(route('admin.inventory.upload-master-list'), {
        onSuccess: () => {
            uploadForm.reset();
            alert('تم تحديث الكشف الرئيسي للدفعة بنجاح!');
        }
    });
};

const handleBatchStudentsUpload = (files) => {
    if (!files || files.length === 0) return;
    
    const formData = new FormData();
    formData.append('sheet', files[0]);
    
    router.post(route('admin.academic.batch.upload-students', props.batch.id), formData, {
        onSuccess: () => {
            alert('تم رفع كشف طلاب الدفعة بنجاح!');
        },
        onError: (errors) => {
            alert('خطأ في الرفع: ' + (errors.sheet || 'حدث خطأ غير متوقع'));
        }
    });
};

const getSectionSupervisor = (section) => {
    const assignment = section.assignments?.find(a => a.section_id === section.id);
    return assignment ? assignment.user.name : 'لا يوجد مسؤول';
};
</script>

<template>
    <Head :title="'إدارة ' + batch.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-500">
                <Link :href="route('admin.academic.index')" class="hover:text-indigo-400 transition-colors">الجامعات</Link>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
                <Link v-if="batch.university_id || batch.department?.college?.university_id" 
                    :href="route('admin.academic.university.show', batch.university_id || batch.department?.college?.university_id)" 
                    class="hover:text-indigo-400 transition-colors">
                    {{ batch.university?.name || batch.department?.college?.university?.name || 'الجامعة' }}
                </Link>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
                <span class="text-slate-300">{{ batch.name }}</span>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-10 pb-20">
            <!-- Header Section -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-2xl shadow-xl shadow-indigo-600/20">🎓</div>
                        <h2 class="text-4xl font-black text-white tracking-tight">{{ batch.name }}</h2>
                    </div>
                    <p class="text-slate-400 font-medium">إدارة السكاشن، المواد، والطلاب ضمن النطاق الأكاديمي للدفعة.</p>
                </div>

                <div class="flex items-center bg-slate-900/50 p-1.5 rounded-[20px] border border-white/5 shadow-inner backdrop-blur-xl">
                    <button 
                        @click="activeTab = 'academic'"
                        :class="activeTab === 'academic' ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-600/20' : 'text-slate-500 hover:text-slate-300'"
                        class="px-8 py-3 rounded-[14px] text-xs font-black uppercase tracking-widest transition-all duration-300"
                    >
                        الهيكل الأكاديمي
                    </button>
                    <button 
                        @click="activeTab = 'students'"
                        :class="activeTab === 'students' ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-600/20' : 'text-slate-500 hover:text-slate-300'"
                        class="px-8 py-3 rounded-[14px] text-xs font-black uppercase tracking-widest transition-all duration-300"
                    >
                        كشف الطلاب
                    </button>
                </div>
            </div>

            <!-- Stats Dashboard -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-slate-900/40 p-8 rounded-[32px] border border-white/5 backdrop-blur-xl group hover:border-indigo-500/20 transition-all duration-500">
                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest block mb-4">طلاب الدفعة</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black text-white group-hover:text-indigo-400 transition-colors">{{ batch.stats?.master_students_count || 0 }}</span>
                        <span class="text-[10px] font-black text-slate-600 uppercase">طالب</span>
                    </div>
                </div>
                <div class="bg-slate-900/40 p-8 rounded-[32px] border border-white/5 backdrop-blur-xl group hover:border-purple-500/20 transition-all duration-500">
                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest block mb-4">إجمالي المطلوب</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black text-white group-hover:text-purple-400 transition-colors">{{ batch.stats?.total_expected_operations || 0 }}</span>
                        <span class="text-[10px] font-black text-slate-600 uppercase">نسخة</span>
                    </div>
                </div>
                <div class="bg-slate-900/40 p-8 rounded-[32px] border border-white/5 backdrop-blur-xl group hover:border-blue-500/20 transition-all duration-500">
                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest block mb-4">العهدة الموزعة</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black text-indigo-400 group-hover:text-indigo-300 transition-colors">{{ batch.stats?.total_distributed_inventory || 0 }}</span>
                        <span class="text-[10px] font-black text-slate-600 uppercase">نسخة</span>
                    </div>
                </div>
                <div class="bg-slate-900/40 p-8 rounded-[32px] border border-white/5 backdrop-blur-xl group hover:border-emerald-500/20 transition-all duration-500">
                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest block mb-4">نسبة التسليم</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black text-emerald-400 group-hover:text-emerald-300 transition-colors">{{ batch.stats?.total_actual_delivered || 0 }}</span>
                        <span class="text-[10px] font-black text-slate-600 uppercase">نسخة</span>
                    </div>
                </div>
            </div>

            <div v-if="activeTab === 'academic'" class="grid grid-cols-1 lg:grid-cols-4 gap-10">
                <!-- Sections Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="flex justify-between items-center px-4">
                        <h3 class="text-xs font-black text-slate-500 uppercase tracking-[0.2em] flex items-center gap-3">
                            <span class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></span>
                            السكاشن الفرعية
                        </h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div v-for="section in batch.sections" :key="section.id" class="bg-slate-900/40 rounded-[32px] border border-white/5 p-6 hover:border-indigo-500/30 transition-all duration-500 group/section shadow-2xl">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-black text-white text-lg group-hover/section:text-indigo-400 transition-colors">{{ section.name }}</h4>
                                        <button v-if="$page.props.auth.user.role === 'admin'" @click="openModal('section', section)" class="p-1.5 text-slate-600 hover:text-indigo-400 hover:bg-indigo-500/10 rounded-lg transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full" :class="section.assignments?.length ? 'bg-emerald-500' : 'bg-slate-700'"></div>
                                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">{{ getSectionSupervisor(section) }}</span>
                                    </div>
                                </div>
                                <button v-if="$page.props.auth.user.role === 'admin'" @click="deleteItem(section.id, 'section')" class="p-2 text-slate-600 hover:text-rose-500 hover:bg-rose-500/10 rounded-xl transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>

                            <div v-if="section.range_start" class="mb-6 flex gap-2">
                                <span class="text-[9px] font-black bg-white/5 text-slate-400 px-3 py-1 rounded-full border border-white/5">{{ section.range_start }}</span>
                                <svg class="w-3 h-3 text-slate-600 self-center" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
                                <span class="text-[9px] font-black bg-white/5 text-slate-400 px-3 py-1 rounded-full border border-white/5">{{ section.range_end }}</span>
                            </div>

                            <div class="grid grid-cols-2 gap-3 mb-6">
                                <div class="bg-white/2 p-3 rounded-2xl border border-white/5 text-center col-span-2">
                                    <p class="text-[7px] font-black text-slate-500 uppercase mb-1">طلاب الكشف الرئيسي</p>
                                    <p class="text-sm font-black text-white">{{ section.master_students_count ?? 0 }}</p>
                                </div>
                                <div class="bg-white/2 p-3 rounded-2xl border border-white/5 text-center">
                                    <p class="text-[7px] font-black text-indigo-500/50 uppercase mb-1">استلم من الأدمن</p>
                                    <p class="text-sm font-black text-indigo-400">{{ section.delegate_received_total ?? 0 }}</p>
                                </div>
                                <div class="bg-white/2 p-3 rounded-2xl border border-white/5 text-center">
                                    <p class="text-[7px] font-black text-amber-500/50 uppercase mb-1">الرصيد المتبقي</p>
                                    <p class="text-sm font-black text-amber-400">{{ section.delegate_current_inventory ?? 0 }}</p>
                                </div>
                            </div>

                            <div class="space-y-4 mb-6">
                                <div class="flex justify-between items-center px-1">
                                    <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest">إجمالي تسليمات المادة</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="bg-slate-950/50 p-2 rounded-xl border border-white/5 text-center">
                                        <p class="text-[6px] font-black text-slate-600 uppercase mb-1">المستهدف</p>
                                        <p class="text-xs font-black text-white">{{ section.total_students_count ?? 0 }}</p>
                                    </div>
                                    <div class="bg-slate-950/50 p-2 rounded-xl border border-white/5 text-center">
                                        <p class="text-[6px] font-black text-emerald-500/50 uppercase mb-1">تم تسليمه</p>
                                        <p class="text-xs font-black text-emerald-400">{{ section.delivered_students_count ?? 0 }}</p>
                                    </div>
                                    <div class="bg-slate-950/50 p-2 rounded-xl border border-white/5 text-center">
                                        <p class="text-[6px] font-black text-rose-500/50 uppercase mb-1">باقي</p>
                                        <p class="text-xs font-black text-rose-400">{{ section.remaining_students_count ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2 mb-6">
                                <div class="flex justify-between items-end">
                                    <span class="text-[8px] font-black text-slate-600 uppercase tracking-widest">معدل الإنجاز العام</span>
                                    <span class="text-[10px] font-black text-indigo-400">
                                        {{ section.total_students_count > 0 ? Math.round((section.delivered_students_count / section.total_students_count) * 100) : 0 }}%
                                    </span>
                                </div>
                                <div class="h-1 bg-white/5 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-indigo-600 to-emerald-500 transition-all duration-1000 shadow-[0_0_8px_rgba(99,102,241,0.3)]"
                                         :style="{ width: (section.total_students_count > 0 ? (section.delivered_students_count / section.total_students_count) * 100 : 0) + '%' }"></div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <button v-if="$page.props.auth.user.role === 'admin'" @click="openModal('assign_supervisor', section)" class="w-full py-3 px-4 bg-white/5 hover:bg-white/10 text-white rounded-[16px] text-[10px] font-black uppercase tracking-widest transition-all duration-300 border border-white/5">
                                    {{ section.assignments?.length ? 'تغيير المسؤول' : 'تعيين مندوب سكشن' }}
                                </button>
                                <button v-if="section.assignments?.length" @click="openTransferModal(section)" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-[16px] text-[10px] font-black uppercase tracking-widest transition-all duration-300 shadow-xl shadow-indigo-600/20">
                                    تسليم عهدة للسكشن
                                </button>
                            </div>
                        </div>
                        
                        <Button v-if="$page.props.auth.user.role === 'admin'" variant="secondary" @click="openModal('section')" class="w-full !rounded-[24px] !py-4 shadow-xl border-white/10">+ إضافة سكشن جديد</Button>
                    </div>
                </div>

                <!-- Subjects Main Column -->
                <div class="lg:col-span-3 space-y-8">
                    <div class="flex justify-between items-center px-4">
                        <h3 class="text-xs font-black text-slate-500 uppercase tracking-[0.2em] flex items-center gap-3">
                            <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
                            المواد الدراسية الفعالة ({{ batch.subjects.length }})
                        </h3>
                        <Button v-if="$page.props.auth.user.role === 'admin'" @click="openModal('subject')" class="!rounded-2xl !py-3 shadow-xl shadow-indigo-600/10">
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            إضافة مادة
                        </Button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div v-for="subject in batch.subjects" :key="subject.id" class="bg-slate-900/40 rounded-[40px] border border-white/5 overflow-hidden flex flex-col group hover:border-indigo-500/30 transition-all duration-500 shadow-2xl backdrop-blur-xl">
                            <!-- Card Header -->
                            <div class="p-8 border-b border-white/5 relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/5 via-transparent to-transparent"></div>
                                <div class="relative z-10 flex justify-between items-start">
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-3">
                                            <h4 class="text-2xl font-black text-white group-hover:text-indigo-300 transition-colors">{{ subject.name }}</h4>
                                            <span :class="subject.term == 1 ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' : 'bg-purple-500/10 text-purple-400 border-purple-500/20'" class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border">
                                                الترم {{ subject.term == 1 ? 'الأول' : 'الثاني' }}
                                            </span>
                                        </div>
                                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">إدارة عامة للدفعة</p>
                                    </div>
                                    <div v-if="$page.props.auth.user.role === 'admin'" class="flex gap-2">
                                        <button @click="toggleSubject(subject)" :class="subject.is_visible_to_section ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' : 'text-slate-600 bg-white/5 border-white/5'" class="p-3 rounded-2xl border transition-all shadow-inner">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        <button @click="deleteItem(subject.id, 'subject')" class="p-3 text-slate-600 bg-white/5 border border-white/5 hover:text-rose-500 hover:bg-rose-500/10 rounded-2xl transition-all shadow-inner">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Dashboard Grid -->
                            <div class="px-8 py-6 bg-slate-900/40 grid grid-cols-2 lg:grid-cols-4 gap-4 border-b border-white/5">
                                <div class="text-center">
                                    <p class="text-[8px] font-black text-slate-600 uppercase tracking-widest mb-1">الطلاب</p>
                                    <p class="text-lg font-black text-white">{{ subject.real_total_students || 0 }}</p>
                                </div>
                                <div v-if="$page.props.auth.user.role === 'admin'" class="text-center border-r border-white/5">
                                    <p class="text-[8px] font-black text-amber-500/50 uppercase tracking-widest mb-1">مخزون</p>
                                    <p class="text-lg font-black text-amber-400">{{ subject.main_inventory || 0 }}</p>
                                </div>
                                <div class="text-center border-r border-white/5">
                                    <p class="text-[8px] font-black text-indigo-500/50 uppercase tracking-widest mb-1">عهدة</p>
                                    <p class="text-lg font-black text-indigo-400">{{ subject.distributed_inventory || 0 }}</p>
                                </div>
                                <div class="text-center border-r border-white/5">
                                    <p class="text-[8px] font-black text-emerald-500/50 uppercase tracking-widest mb-1">تم</p>
                                    <p class="text-lg font-black text-emerald-400">{{ subject.actual_delivered || 0 }}</p>
                                </div>
                            </div>

                            <!-- Progress Track -->
                            <div class="px-8 pt-8">
                                <div class="flex justify-between items-end mb-3">
                                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em]">معدل التسليم الكلي</span>
                                    <span class="text-sm font-black text-white">
                                        {{ subject.real_total_students > 0 ? Math.round((subject.actual_delivered / subject.real_total_students) * 100) : 0 }}%
                                    </span>
                                </div>
                                <div class="h-2 bg-white/5 rounded-full overflow-hidden border border-white/5 shadow-inner">
                                    <div class="h-full bg-gradient-to-l from-indigo-600 via-indigo-500 to-indigo-400 transition-all duration-1000 ease-out shadow-[0_0_12px_rgba(99,102,241,0.4)]"
                                         :style="{ width: (subject.real_total_students > 0 ? (subject.actual_delivered / subject.real_total_students) * 100 : 0) + '%' }"></div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="p-8 mt-auto space-y-4">
                                <div v-if="$page.props.auth.user.role === 'admin'" class="p-4 bg-white/2 border border-dashed border-white/10 rounded-[24px] group/upload hover:border-indigo-500/30 transition-all duration-300">
                                    <FileUpload label="تحديث كشف المادة (Excel)" @change="(files) => handleUpload(subject.id, files)" />
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <Link :href="route('admin.academic.subject.students', subject.id)" class="block">
                                        <button class="w-full py-4 bg-white/5 hover:bg-white/10 text-slate-200 rounded-[20px] text-[10px] font-black uppercase tracking-widest border border-white/5 transition-all duration-300">
                                            كشف الأسماء
                                        </button>
                                    </Link>
                                    <Link :href="route('delivery.show', subject.id)" class="block">
                                        <button class="w-full py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-[20px] text-[10px] font-black uppercase tracking-widest shadow-xl shadow-indigo-600/20 transition-all duration-300">
                                            واجهة التسليم
                                        </button>
                                    </Link>
                                </div>
                                <div v-if="$page.props.auth.user.role === 'admin'">
                                    <Link :href="route('admin.academic.subject.log', subject.id)" class="block">
                                        <button class="w-full py-4 bg-slate-800/40 hover:bg-slate-800/60 text-indigo-400 rounded-[20px] text-[10px] font-black uppercase tracking-widest border border-indigo-500/10 transition-all duration-300">
                                            سجل العمليات التاريخي
                                        </button>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Master List Tab Content -->
            <div v-else class="space-y-10">
                <div class="bg-slate-900/40 rounded-[40px] border border-white/5 overflow-hidden shadow-2xl backdrop-blur-xl">
                    <!-- Top Toolbar -->
                    <div class="p-10 border-b border-white/5 bg-gradient-to-br from-indigo-600/10 via-transparent to-transparent">
                        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-8">
                            <div class="space-y-3">
                                <h3 class="text-3xl font-black text-white tracking-tight">قاعدة البيانات الشاملة</h3>
                                <p class="text-slate-400 font-medium max-w-lg">الكشف الموحد لكافة طلاب الدفعة المعتمد من شؤون الطلاب، والمستخدم في التوزيع الآلي.</p>
                            </div>
                            
                            <div class="flex flex-wrap gap-4">
                                <div class="relative group w-full sm:w-80">
                                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-focus-within:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <input 
                                        type="text" 
                                        v-model="studentSearch" 
                                        @input="handleStudentSearch"
                                        placeholder="بحث بالاسم أو الكود..." 
                                        class="w-full bg-slate-950/50 border border-white/5 rounded-2xl py-3.5 pr-12 pl-4 text-sm text-white placeholder:text-slate-600 focus:border-indigo-500/50 focus:ring-0 transition-all shadow-inner"
                                    />
                                </div>
                                
                                <div v-if="$page.props.auth.user.role === 'admin'" class="flex gap-2">
                                    <button @click="autoAssign" class="px-8 py-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-emerald-600/20 transition-all duration-300">
                                        توزيع آلي للطلاب
                                    </button>
                                    <a :href="route('admin.academic.batch.download-template', batch.id)" target="_blank">
                                        <button class="p-4 bg-white/5 hover:bg-white/10 text-white rounded-2xl border border-white/10 transition-all shadow-xl">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="$page.props.auth.user.role === 'admin'" class="mt-10 p-6 bg-indigo-600/5 border border-dashed border-indigo-600/20 rounded-[32px] flex flex-col md:flex-row items-center justify-between gap-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-indigo-600/20 rounded-2xl flex items-center justify-center text-xl">📁</div>
                                <div>
                                    <p class="font-black text-white text-sm">تحديث قاعدة البيانات الرئيسية</p>
                                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">رفع ملف EXCEL ببيانات الدفعة</p>
                                </div>
                            </div>
                            <div class="w-full md:w-auto min-w-[300px]">
                                <FileUpload label="اختر الملف للمزامنة..." @change="handleBatchStudentsUpload" />
                            </div>
                        </div>
                    </div>

                    <div class="p-0">
                        <Table 
                            :headers="['كود الطالب', 'الاسم الكامل للمقرر', 'تاريخ القيد']"
                            :items="batch_students.data"
                        >
                            <template #row="{ item }">
                                <td class="py-5 pl-4 pr-3 text-base border-b border-white/5 sm:pl-10">
                                    <span class="font-black text-indigo-400">#{{ item.student_external_id }}</span>
                                </td>
                                <td class="px-3 py-5 border-b border-white/5">
                                    <p class="text-slate-100 font-black">{{ item.name }}</p>
                                </td>
                                <td class="px-3 py-5 border-b border-white/5">
                                    <span class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                                        {{ new Date(item.created_at).toLocaleDateString('ar-EG', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                                    </span>
                                </td>
                            </template>
                        </Table>
                        
                        <div v-if="batch_students.data.length === 0" class="flex flex-col items-center justify-center py-32 opacity-50">
                            <div class="text-6xl mb-6">📉</div>
                            <p class="text-slate-400 font-black uppercase tracking-[0.2em]">لا توجد بيانات طلاب حالياً</p>
                        </div>

                        <!-- Pagination Dashboard -->
                        <div v-if="batch_students.links.length > 3" class="p-8 border-t border-white/5 flex justify-center bg-slate-900/20">
                            <div class="flex gap-2">
                                <template v-for="(link, k) in batch_students.links" :key="k">
                                    <Link v-if="link.url"
                                        :href="link.url" v-html="link.label"
                                        class="px-5 py-2.5 rounded-xl text-xs font-black transition-all duration-300"
                                        :class="link.active ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-600/20' : 'bg-white/5 text-slate-500 hover:text-white hover:bg-white/10'" />
                                    <span v-else
                                        v-html="link.label"
                                        class="px-5 py-2.5 rounded-xl text-xs font-black bg-white/[0.02] text-slate-700 cursor-not-allowed border border-white/5" />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upsert Modals Overhaul -->
        <Modal :show="showModal" @close="showModal = false" max-width="md">
            <div class="bg-slate-950 border border-white/10 rounded-[40px] overflow-hidden shadow-2xl">
                <!-- Delivery Inventory Modal -->
                <div v-if="modalType.startsWith('تسليم عهدة')" class="p-12">
                    <div class="mb-10">
                        <div class="w-16 h-16 bg-indigo-600/10 border border-indigo-500/20 rounded-[24px] flex items-center justify-center text-3xl mb-6">📦</div>
                        <h2 class="text-3xl font-black text-white mb-2">تسليم عهدة للمندوب</h2>
                        <p class="text-slate-400 font-medium">قم بتحديد المادة والكمية المتاحة للتوزيع في السكشن.</p>
                    </div>
                    
                    <div class="space-y-8">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 px-1">المادة الدراسية المحددة</label>
                            <select v-model="transferForm.subject_id" class="block w-full rounded-[20px] border-white/10 bg-slate-900 py-4.5 px-5 text-white ring-1 ring-inset ring-white/5 focus:ring-2 focus:ring-indigo-600 focus:border-transparent sm:text-sm shadow-inner transition-all">
                                <option value="" class="bg-slate-900">اختر المادة من القائمة...</option>
                                <option v-for="subject in batch.subjects" :key="subject.id" :value="subject.id" class="bg-slate-900">{{ subject.name }}</option>
                            </select>
                        </div>
                        <Input
                            label="الكمية الإجمالية للمخزون"
                            type="number"
                            v-model="transferForm.quantity"
                            min="1"
                            required
                            class="!rounded-[20px] !py-4.5 !bg-slate-900"
                        />
                    </div>
                    
                    <div class="flex gap-4 mt-12">
                        <Button @click="submit" class="flex-1 !rounded-[24px] !py-5 shadow-2xl shadow-indigo-600/20" :loading="transferForm.processing">تأكيد التسليم</Button>
                        <Button variant="secondary" size="lg" class="flex-1 !rounded-[24px] !py-5" @click="showModal = false">تراجع</Button>
                    </div>
                </div>

                <!-- Delegate Assignment Modal -->
                <div v-else-if="modalType === 'تعيين مسؤول سكشن'" class="p-12">
                    <div class="mb-10">
                        <div class="w-16 h-16 bg-indigo-600/10 border border-indigo-500/20 rounded-[24px] flex items-center justify-center text-3xl mb-6">👤</div>
                        <h2 class="text-3xl font-black text-white mb-2">تعيين مندوب السكشن</h2>
                        <p class="text-slate-400 font-medium">اختر من القائمة المندوب المسؤول عن توزيع الكتب لهذا السكشن.</p>
                    </div>

                    <div class="space-y-8">
                        <div class="relative group">
                            <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-focus-within:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input 
                                v-model="supervisorSearch" 
                                type="text" 
                                placeholder="بحث عن اسم المندوب..." 
                                class="w-full bg-slate-900 border border-white/5 rounded-[20px] py-4.5 pr-12 pl-4 text-sm text-white placeholder:text-slate-600 focus:border-indigo-500/50 focus:ring-0 transition-all shadow-inner"
                            />
                        </div>
                        
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">قائمة المندوبين المتاحين</label>
                            <select v-model="assignForm.user_id" class="block w-full rounded-[20px] border-white/10 bg-slate-900 py-4.5 px-5 text-white ring-1 ring-inset ring-white/5 focus:ring-2 focus:ring-indigo-600 focus:border-transparent sm:text-sm shadow-inner transition-all">
                                <option value="" class="bg-slate-900">اختر من القائمة ({{ filteredSupervisors.length }})</option>
                                <option v-for="user in filteredSupervisors" :key="user.id" :value="user.id" class="bg-slate-900">
                                    {{ user.name }} - {{ user.email }}
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex gap-4 mt-12">
                        <Button @click="submit" class="flex-1 !rounded-[24px] !py-5 shadow-2xl shadow-indigo-600/20" :loading="assignForm.processing" :disabled="!assignForm.user_id">تأكيد التعيين</Button>
                        <Button variant="secondary" size="lg" class="flex-1 !rounded-[24px] !py-5" @click="showModal = false">إلغاء</Button>
                    </div>
                </div>

                <!-- Standard Academic Upsert Modal -->
                <form v-else @submit.prevent="submit" class="p-12 space-y-10">
                    <div>
                        <div class="w-16 h-16 bg-indigo-600/10 border border-indigo-500/20 rounded-[24px] flex items-center justify-center text-3xl mb-6">📝</div>
                        <h2 class="text-3xl font-black text-white mb-2">إضافة {{ modalType }}</h2>
                        <p class="text-slate-400 font-medium">أدخل البيانات المطلوبة لإكمال العملية بنجاح.</p>
                    </div>

                    <div class="space-y-8">
                        <Input label="الاسم التعريفي" v-model="form.name" required class="!rounded-[20px] !py-4.5 !bg-slate-900" />
                        
                        <div v-if="form.type === 'section' || modalType === 'تعديل سكشن'" class="grid grid-cols-2 gap-6">
                            <Input label="بداية النطاق الكودي" v-model="form.range_start" placeholder="مثال: 2024001" class="!rounded-[20px] !py-4.5 !bg-slate-900" />
                            <Input label="نهاية النطاق الكودي" v-model="form.range_end" placeholder="مثال: 2024100" class="!rounded-[20px] !py-4.5 !bg-slate-900" />
                        </div>

                        <div v-if="form.type === 'subject'">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1 mb-4">الفصل الدراسي المعتمد</label>
                            <div class="grid grid-cols-2 gap-4">
                                <button type="button" @click="form.term = '1'" :class="form.term === '1' ? 'border-indigo-600 bg-indigo-600 text-white shadow-xl shadow-indigo-600/20' : 'border-white/5 bg-slate-900 text-slate-500 hover:text-slate-300'" class="py-5 rounded-[20px] border-2 font-black transition-all text-xs uppercase tracking-widest">الترم الأول</button>
                                <button type="button" @click="form.term = '2'" :class="form.term === '2' ? 'border-indigo-600 bg-indigo-600 text-white shadow-xl shadow-indigo-600/20' : 'border-white/5 bg-slate-900 text-slate-500 hover:text-slate-300'" class="py-5 rounded-[20px] border-2 font-black transition-all text-xs uppercase tracking-widest">الترم الثاني</button>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <Button type="submit" class="flex-1 !rounded-[24px] !py-5 shadow-2xl shadow-indigo-600/20" :loading="form.processing">تأكيد وحفظ</Button>
                        <Button type="button" variant="secondary" size="lg" class="flex-1 !rounded-[24px] !py-5" @click="showModal = false">إلغاء</Button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(99, 102, 241, 0.3);
}
</style>
