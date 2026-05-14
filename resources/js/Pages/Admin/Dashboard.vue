<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import FileUpload from '@/Components/UI/FileUpload.vue';

const props = defineProps({
    stats: Object,
    academic_tree: Array
});

const uploadForm = useForm({
    batch_id: '',
    sheets: []
});

const handleFiles = (files) => {
    uploadForm.sheets = files;
};

const submitUpload = () => {
    uploadForm.post(route('admin.upload-master'), {
        onSuccess: () => uploadForm.reset()
    });
};
</script>

<template>
    <Head title="لوحة تحكم المسؤول" />

    <AuthenticatedLayout>
        <template #header>
            إحصائيات النظام العامة
        </template>

        <div class="space-y-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <Card no-padding class="relative overflow-hidden group">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">إجمالي الطلاب</p>
                                <p class="mt-1 text-3xl font-bold text-gray-900">{{ stats.total_students }}</p>
                            </div>
                            <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-indigo-600"></div>
                </Card>

                <Card no-padding class="relative overflow-hidden group">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">تم التسليم</p>
                                <p class="mt-1 text-3xl font-bold text-emerald-600">{{ stats.total_delivered }}</p>
                            </div>
                            <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-emerald-500"></div>
                </Card>

                <Card no-padding class="relative overflow-hidden group">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">عدد المواد</p>
                                <p class="mt-1 text-3xl font-bold text-gray-900">{{ stats.total_subjects }}</p>
                            </div>
                            <div class="p-3 bg-amber-50 rounded-xl text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-amber-500"></div>
                </Card>

                <Card no-padding class="relative overflow-hidden group">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">المناديب</p>
                                <p class="mt-1 text-3xl font-bold text-gray-900">{{ stats.active_delegates }}</p>
                            </div>
                            <div class="p-3 bg-rose-50 rounded-xl text-rose-600 group-hover:bg-rose-600 group-hover:text-white transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-rose-500"></div>
                </Card>
            </div>

            <!-- Master List Upload Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <Card title="رفع الكشوفات الرئيسية" subtitle="قم برفع شيتات الإكسيل للطلاب (يمكن رفع حتى 5 ملفات دفعة واحدة)">
                    <form @submit.prevent="submitUpload" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium leading-6 text-gray-900 mb-2">اختر الدفعة / الفرقة</label>
                            <select v-model="uploadForm.batch_id" class="block w-full rounded-lg border-0 py-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                                <option value="">اختر الدفعة المناسبة</option>
                                <template v-for="uni in academic_tree" :key="uni.id">
                                    <optgroup :label="uni.name">
                                        <template v-for="col in uni.colleges" :key="col.id">
                                            <template v-for="dept in col.departments" :key="dept.id">
                                                <option v-for="batch in dept.batches" :key="batch.id" :value="batch.id">
                                                    {{ col.name }} - {{ dept.name }} - {{ batch.name }}
                                                </option>
                                            </template>
                                        </template>
                                    </optgroup>
                                </template>
                            </select>
                        </div>

                        <FileUpload 
                            multiple 
                            label="ملفات الطلاب (Excel)" 
                            @change="handleFiles"
                        />

                        <Button type="submit" class="w-full" :loading="uploadForm.processing" :disabled="!uploadForm.batch_id || uploadForm.sheets.length === 0">
                            بدء المعالجة والرفع
                        </Button>
                    </form>
                </Card>

                <Card title="إرشادات الرفع" subtitle="يرجى اتباع التعليمات التالية لضمان صحة البيانات">
                    <div class="space-y-4">
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center font-bold">1</div>
                            <p class="text-sm text-gray-600">يجب أن يحتوي الملف على أعمدة باسم (id) للكود و (name) للاسم.</p>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center font-bold">2</div>
                            <p class="text-sm text-gray-600">سيقوم النظام بدمج البيانات تلقائياً ومنع التكرار بناءً على كود الطالب.</p>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center font-bold">3</div>
                            <p class="text-sm text-gray-600">يمكنك رفع ملفات متعددة لنفس الدفعة وسيتم دمجهم جميعاً.</p>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
