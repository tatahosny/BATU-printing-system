<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import Modal from '@/Components/UI/Modal.vue';
import Input from '@/Components/UI/Input.vue';
import Table from '@/Components/UI/Table.vue';

const props = defineProps({ 
    delegates: Array,
    academic_tree: Array
});

const searchQuery = ref('');
const filteredDelegates = computed(() => {
    if (!searchQuery.value) return props.delegates;
    const q = searchQuery.value.toLowerCase();
    return props.delegates.filter(d => 
        d.name.toLowerCase().includes(q) || 
        d.email.toLowerCase().includes(q)
    );
});

const showModal = ref(false);
const showAssignModal = ref(false);
const showDetailsModal = ref(false);
const selectedUser = ref(null);
const selectedUserDetails = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'section_delegate',
});

const assignForm = useForm({
    university_id: '',
    college_id: '',
    department_id: '',
    batch_id: '',
    section_id: '',
});

const submit = () => {
    form.post(route('admin.delegates.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};

const openAssignModal = (user) => {
    selectedUser.value = user;
    const assignment = user.assignments?.[0] || {};
    assignForm.university_id = assignment.university_id || '';
    assignForm.college_id = assignment.college_id || '';
    assignForm.department_id = assignment.department_id || '';
    assignForm.batch_id = assignment.batch_id || '';
    assignForm.section_id = assignment.section_id || '';
    showAssignModal.value = true;
};

const openDetailsModal = (user) => {
    selectedUserDetails.value = user;
    showDetailsModal.value = true;
};

const submitAssign = () => {
    // Find department for selected batch
    const selectedBatch = batches.value.find(b => b.id == assignForm.batch_id);
    if (selectedBatch) {
        assignForm.department_id = selectedBatch.department_id;
    }

    assignForm.post(route('admin.delegates.assign', selectedUser.value.id), {
        onSuccess: () => {
            showAssignModal.value = false;
        }
    });
};

const colleges = computed(() => {
    const uni = props.academic_tree.find(u => u.id == assignForm.university_id);
    return uni ? uni.colleges : [];
});

const batches = computed(() => {
    const selectedCol = colleges.value.find(c => c.id == assignForm.college_id);
    if (!selectedCol) return [];
    return selectedCol.departments.flatMap(d => d.batches);
});

const sections = computed(() => {
    const batch = batches.value.find(b => b.id == assignForm.batch_id);
    return batch ? batch.sections : [];
});

const getRoleBadgeClass = (role) => {
    switch (role) {
        case 'admin': return 'bg-rose-500/10 text-rose-400 border border-rose-500/20';
        case 'general_delegate': return 'bg-purple-500/10 text-purple-400 border border-purple-500/20';
        case 'section_delegate': return 'bg-blue-500/10 text-blue-400 border border-blue-500/20';
        default: return 'bg-slate-500/10 text-slate-400 border border-slate-500/20';
    }
};

const getRoleName = (role) => {
    switch (role) {
        case 'admin': return 'مدير نظام';
        case 'general_delegate': return 'مندوب عام';
        case 'section_delegate': return 'مندوب سكشن';
        default: return role;
    }
};
</script>

<template>
    <Head title="إدارة المستخدمين والمناديب" />

    <AuthenticatedLayout>
        <template #header>
            إدارة المستخدمين والمناديب
        </template>

        <div class="max-w-7xl mx-auto space-y-8 pb-10">
            <!-- Header Section -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div>
                    <h2 class="text-3xl font-black text-white tracking-tight">إدارة المناديب</h2>
                    <p class="text-slate-400 text-sm mt-1">إضافة وتعيين صلاحيات المناديب ومتابعة نشاطهم في النظام</p>
                </div>
                
                <div class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto">
                    <div class="relative w-full sm:w-72">
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            placeholder="بحث بالاسم أو البريد..." 
                            class="w-full bg-slate-900/50 border border-white/10 rounded-2xl py-3 px-4 pr-10 text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all shadow-inner"
                        >
                        <svg class="w-5 h-5 text-slate-500 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <Button @click="showModal = true" class="w-full sm:w-auto shadow-xl shadow-indigo-500/20 rounded-2xl h-12 px-6">
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        إضافة حساب جديد
                    </Button>
                </div>
            </div>

            <!-- Delegates Content (Responsive) -->
            <div class="space-y-6">
                <!-- Mobile Card View -->
                <div class="lg:hidden grid grid-cols-1 gap-6">
                    <div v-for="item in filteredDelegates" :key="item.id" class="bg-slate-900/40 border border-white/5 rounded-[32px] p-6 space-y-6 backdrop-blur-xl animate-page-entry">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-indigo-500/10 text-indigo-400 rounded-2xl flex items-center justify-center font-black text-xl border border-indigo-500/20">
                                {{ item.name.charAt(0) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-black text-white text-lg truncate">{{ item.name }}</div>
                                <span :class="[
                                    'inline-block px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border mt-1',
                                    getRoleBadgeClass(item.role)
                                ]">
                                    {{ getRoleName(item.role) }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 py-4 border-y border-white/5">
                            <div class="text-center">
                                <span class="block text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">النشاط</span>
                                <span class="text-xl font-black text-emerald-400">{{ item.activity_logs_count || 0 }}</span>
                            </div>
                            <div class="text-center">
                                <span class="block text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">العهدة</span>
                                <span class="text-xl font-black text-amber-400">{{ item.total_stock || 0 }}</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <span class="block text-[9px] font-black text-slate-500 uppercase tracking-widest">النطاق المعين</span>
                            <div v-if="item.assignments && item.assignments.length > 0" class="bg-white/5 rounded-2xl p-4 border border-white/5">
                                <p class="font-black text-slate-200 text-xs mb-1">{{ item.assignments[0].university?.name }}</p>
                                <p class="text-[10px] text-slate-500 font-bold">{{ item.assignments[0].college?.name }} · {{ item.assignments[0].batch?.name }}</p>
                            </div>
                            <div v-else class="text-xs text-slate-600 font-bold italic py-2">لا يوجد نطاق معين</div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button 
                                @click="openDetailsModal(item)"
                                class="flex-1 bg-slate-800 text-white font-black text-xs py-3 rounded-2xl hover:bg-slate-700 transition-colors"
                            >
                                التفاصيل
                            </button>
                            <button 
                                v-if="item.role !== 'admin'"
                                @click="openAssignModal(item)"
                                class="flex-1 bg-indigo-600 text-white font-black text-xs py-3 rounded-2xl hover:bg-indigo-500 transition-colors shadow-lg shadow-indigo-600/20"
                            >
                                تعيين نطاق
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table View -->
                <Card no-padding class="hidden lg:block overflow-hidden border border-white/5 shadow-2xl bg-slate-900/20 backdrop-blur-sm rounded-[32px]">
                    <Table 
                        :headers="['المستخدم', 'الصلاحية', 'النشاط', 'العهدة', 'النطاق المعين', 'إجراءات']"
                        :items="filteredDelegates"
                    >
                        <template #row="{ item }">
                            <td class="py-6 pl-4 pr-3 text-sm sm:pl-8 border-b border-white/5">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-indigo-500/10 text-indigo-400 rounded-[18px] flex items-center justify-center font-black text-lg border border-indigo-500/20 shadow-sm">
                                        {{ item.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <div class="font-black text-white leading-tight text-base">{{ item.name }}</div>
                                        <div class="text-[11px] text-slate-500 font-bold mt-1" dir="ltr">{{ item.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-6 text-sm border-b border-white/5">
                                <span :class="[
                                    'px-3 py-1.5 rounded-full text-[10px] font-black shadow-sm uppercase tracking-widest border',
                                    getRoleBadgeClass(item.role)
                                ]">
                                    {{ getRoleName(item.role) }}
                                </span>
                            </td>
                            <td class="px-3 py-6 text-sm border-b border-white/5 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="font-black text-emerald-400 text-lg leading-none">{{ item.activity_logs_count || 0 }}</span>
                                    <span class="text-[9px] text-slate-500 font-black uppercase mt-1">عملية</span>
                                </div>
                            </td>
                            <td class="px-3 py-6 text-sm border-b border-white/5 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="font-black text-amber-400 text-lg leading-none">{{ item.total_stock || 0 }}</span>
                                    <span class="text-[9px] text-slate-500 font-black uppercase mt-1">نسخة عهدة</span>
                                </div>
                            </td>
                            <td class="px-3 py-6 text-sm text-slate-300 border-b border-white/5">
                                <div v-if="item.assignments && item.assignments.length > 0" class="space-y-1.5">
                                    <p class="font-black text-slate-200 text-xs">{{ item.assignments[0].university?.name }}</p>
                                    <div class="flex items-center gap-2 text-[10px] text-slate-500 font-bold">
                                        <span>{{ item.assignments[0].college?.name }}</span>
                                        <span class="w-1 h-1 bg-slate-700 rounded-full"></span>
                                        <span>{{ item.assignments[0].batch?.name }}</span>
                                    </div>
                                    <p v-if="item.assignments[0].section" class="text-indigo-400 font-black text-[10px] uppercase tracking-wider">
                                        سكشن: {{ item.assignments[0].section.name }}
                                    </p>
                                </div>
                                <span v-else class="text-[11px] font-bold text-slate-600 italic">غير معين</span>
                            </td>
                            <td class="px-3 py-6 text-sm border-b border-white/5 text-left pl-8">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        @click="openDetailsModal(item)"
                                        class="p-2.5 rounded-xl bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white transition-all border border-white/5 shadow-lg"
                                        title="عرض التفاصيل"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <button 
                                        v-if="item.role !== 'admin'"
                                        @click="openAssignModal(item)"
                                        class="p-2.5 rounded-xl bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500 hover:text-white transition-all border border-indigo-500/20 shadow-lg"
                                        title="تعيين النطاق"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    </button>
                                </div>
                            </td>
                        </template>
                    </Table>
                </Card>
            </div>
        </div>

        <!-- Modals -->
        <!-- Create Delegate Modal -->
        <Modal :show="showModal" @close="showModal = false" max-width="md">
            <div class="bg-slate-900 border border-white/10 rounded-[32px] overflow-hidden shadow-2xl">
                <form @submit.prevent="submit" class="p-8 space-y-6">
                    <div>
                        <h2 class="text-2xl font-black text-white">إضافة مستخدم جديد</h2>
                        <p class="text-slate-400 text-sm mt-1">تأكد من إدخال بيانات صحيحة لتمكين المندوب من العمل</p>
                    </div>

                    <div class="space-y-4">
                        <Input
                            label="الاسم الكامل"
                            v-model="form.name"
                            placeholder="أدخل اسم المندوب"
                            required
                            :error="form.errors.name"
                        />

                        <Input
                            label="البريد الإلكتروني"
                            v-model="form.email"
                            type="email"
                            placeholder="example@mail.com"
                            required
                            :error="form.errors.email"
                        />

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <Input
                                label="كلمة المرور"
                                v-model="form.password"
                                type="password"
                                required
                                :error="form.errors.password"
                            />
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mr-1">الصلاحية</label>
                                <select v-model="form.role" class="w-full bg-slate-800 border border-white/10 rounded-xl py-3 px-4 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all shadow-inner">
                                    <option value="admin">مدير نظام</option>
                                    <option value="general_delegate">مندوب عام</option>
                                    <option value="section_delegate">مندوب سكشن</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <Button type="submit" class="flex-1 rounded-2xl h-12" :loading="form.processing">إنشاء الحساب</Button>
                        <Button type="button" variant="secondary" class="flex-1 rounded-2xl h-12" @click="showModal = false">إلغاء</Button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Assign Scope Modal -->
        <Modal :show="showAssignModal" @close="showAssignModal = false" max-width="md">
            <div class="bg-slate-900 border border-white/10 rounded-[32px] overflow-hidden shadow-2xl">
                <form @submit.prevent="submitAssign" class="p-8 space-y-6">
                    <div>
                        <h2 class="text-2xl font-black text-white">تعيين نطاق العمل</h2>
                        <p class="text-slate-400 text-sm mt-1">حدد السكاشن أو الدفعات التي سيديرها <span class="text-indigo-400">{{ selectedUser?.name }}</span></p>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mr-1">الجامعة</label>
                            <select v-model="assignForm.university_id" class="w-full bg-slate-800 border border-white/10 rounded-xl py-3 px-4 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all shadow-inner">
                                <option value="">اختر الجامعة</option>
                                <option v-for="uni in academic_tree" :key="uni.id" :value="uni.id">{{ uni.name }}</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mr-1">الكلية</label>
                                <select v-model="assignForm.college_id" :disabled="!assignForm.university_id" class="w-full bg-slate-800 border border-white/10 rounded-xl py-3 px-4 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all disabled:opacity-50">
                                    <option value="">اختر الكلية</option>
                                    <option v-for="col in colleges" :key="col.id" :value="col.id">{{ col.name }}</option>
                                </select>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mr-1">الفرقة</label>
                                <select v-model="assignForm.batch_id" :disabled="!assignForm.college_id" class="w-full bg-slate-800 border border-white/10 rounded-xl py-3 px-4 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all disabled:opacity-50">
                                    <option value="">اختر الفرقة</option>
                                    <option v-for="batch in batches" :key="batch.id" :value="batch.id">{{ batch.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div v-if="selectedUser?.role === 'section_delegate'" class="space-y-1.5">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mr-1">السكشن المحدد</label>
                            <select v-model="assignForm.section_id" :disabled="!assignForm.batch_id" class="w-full bg-slate-800 border border-white/10 rounded-xl py-3 px-4 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all disabled:opacity-50">
                                <option value="">كل السكاشن</option>
                                <option v-for="sec in sections" :key="sec.id" :value="sec.id">{{ sec.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <Button type="submit" class="flex-1 rounded-2xl h-12" :loading="assignForm.processing">حفظ التعيين</Button>
                        <Button type="button" variant="secondary" class="flex-1 rounded-2xl h-12" @click="showAssignModal = false">إلغاء</Button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Account Details Modal -->
        <Modal :show="showDetailsModal" @close="showDetailsModal = false" max-width="md">
            <div class="bg-slate-900 border border-white/10 rounded-[32px] overflow-hidden shadow-2xl p-8 space-y-6">
                <div class="flex justify-between items-start">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-indigo-600 text-white rounded-[24px] flex items-center justify-center font-black text-2xl shadow-xl shadow-indigo-500/20">
                            {{ selectedUserDetails?.name.charAt(0) }}
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-white">{{ selectedUserDetails?.name }}</h3>
                            <p class="text-slate-400 text-xs mt-0.5" dir="ltr">{{ selectedUserDetails?.email }}</p>
                        </div>
                    </div>
                    <span :class="[
                        'px-3 py-1 rounded-full text-[9px] font-black shadow-sm uppercase tracking-widest border',
                        getRoleBadgeClass(selectedUserDetails?.role)
                    ]">
                        {{ getRoleName(selectedUserDetails?.role) }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-800/50 border border-white/5 rounded-2xl p-4 text-center">
                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest block mb-1">الرصيد الحالي</span>
                        <span class="text-2xl font-black text-amber-400">{{ selectedUserDetails?.total_stock || 0 }}</span>
                        <span class="text-[9px] text-slate-500 block font-bold">نسخة كتاب</span>
                    </div>
                    <div class="bg-slate-800/50 border border-white/5 rounded-2xl p-4 text-center">
                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest block mb-1">إجمالي النشاط</span>
                        <span class="text-2xl font-black text-emerald-400">{{ selectedUserDetails?.activity_logs_count || 0 }}</span>
                        <span class="text-[9px] text-slate-500 block font-bold">إجراء مسجل</span>
                    </div>
                </div>

                <div class="space-y-3">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mr-1">نطاق العمل المعين</span>
                    <div v-if="selectedUserDetails?.assignments && selectedUserDetails.assignments.length > 0" class="bg-indigo-500/5 border border-indigo-500/20 rounded-2xl p-5 relative overflow-hidden">
                        <div class="absolute right-0 top-0 bottom-0 w-1 bg-indigo-500"></div>
                        <p class="font-black text-slate-100 text-sm mb-1">{{ selectedUserDetails.assignments[0].university?.name }}</p>
                        <p class="text-xs text-slate-400 font-bold">
                            {{ selectedUserDetails.assignments[0].college?.name }} • {{ selectedUserDetails.assignments[0].batch?.name }}
                        </p>
                        <div v-if="selectedUserDetails.assignments[0].section" class="mt-3 inline-block bg-indigo-500/10 text-indigo-400 px-3 py-1 rounded-lg text-[10px] font-black border border-indigo-500/20">
                            سكشن: {{ selectedUserDetails.assignments[0].section.name }}
                        </div>
                    </div>
                    <div v-else class="bg-rose-500/5 border border-rose-500/20 rounded-2xl p-5 text-center">
                        <p class="text-rose-400 text-xs font-black">لم يتم تعيين نطاق أكاديمي لهذا المندوب بعد</p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-4">
                    <Link 
                        v-if="selectedUserDetails"
                        :href="route('admin.delegates.activity', selectedUserDetails.id)"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs py-4 rounded-2xl transition-all shadow-xl shadow-indigo-500/20 text-center uppercase tracking-widest"
                    >
                        عرض سجل النشاط الكامل
                    </Link>
                    <Button type="button" variant="secondary" class="w-full rounded-2xl h-12" @click="showDetailsModal = false">إغلاق</Button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}
</style>
