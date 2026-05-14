<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Table from '@/Components/UI/Table.vue';

const props = defineProps({
    subject: Object,
    students: Object
});
</script>

<template>
    <Head :title="'طلاب مادة - ' + subject.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2 text-sm text-gray-500 font-medium">
                <Link :href="route('admin.academic.index')" class="hover:text-indigo-600 transition-colors">الجامعات</Link>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <Link v-if="subject?.batch_id" :href="route('admin.academic.batch.show', subject.batch_id)" class="hover:text-indigo-600 transition-colors">
                    {{ subject.batch?.name || 'الدفعة' }}
                </Link>
                <span v-else class="text-gray-400">...</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span class="text-white/90">{{ subject.name }}</span>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-8">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-black text-white">كشف طلاب المادة</h2>
                    <p class="text-sm text-slate-500 mt-1">عرض جميع الطلاب المسجلين في هذه المادة من الإكسيل المرفوع</p>
                </div>
                <div class="flex bg-white/5 text-white px-6 py-3 rounded-2xl border border-white/5 items-center gap-4 shadow-xl">
                    <div class="text-right border-l border-white/10 pl-4 ml-4">
                        <p class="text-[10px] font-bold uppercase tracking-widest opacity-50">إجمالي الطلاب</p>
                        <p class="text-xl font-black">{{ students?.total || 0 }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold uppercase tracking-widest opacity-50">تم التسليم</p>
                        <p class="text-xl font-black text-emerald-400">
                            {{ students?.data?.filter(s => s.is_received).length || 0 }}
                        </p>
                    </div>
                </div>
            </div>

            <Card no-padding class="overflow-hidden rounded-[32px] border-gray-100 shadow-xl bg-white">
                <Table 
                    :headers="['كود الطالب', 'الاسم الكامل', 'السكشن', 'المسؤول (المندوب)', 'الحالة']"
                    :items="students.data"
                >
                    <template #row="{ item }">
                        <td class="py-4 pl-4 pr-3 text-sm font-bold text-white sm:pl-6">#{{ item.student_external_id }}</td>
                        <td class="px-3 py-4 text-sm text-slate-300 font-medium">{{ item.name }}</td>
                        <td class="px-3 py-4 text-sm">
                            <span v-if="item.section" class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs font-bold">
                                {{ item.section.name }}
                            </span>
                            <span v-else class="text-gray-300 text-xs">غير محدد</span>
                        </td>
                        <td class="px-3 py-4 text-sm">
                            <div v-if="item.delegate" class="flex items-center gap-2">
                                <div class="w-6 h-6 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-[10px] font-bold">
                                    {{ item.delegate.name?.charAt(0) || '?' }}
                                </div>
                                <span class="text-gray-600 font-bold text-xs">{{ item.delegate.name }}</span>
                            </div>
                            <span v-else class="text-gray-300 text-xs italic">بانتظار المطابقة</span>
                        </td>
                        <td class="px-3 py-4 text-sm">
                            <span :class="item.is_received ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-50 text-amber-600'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wide">
                                {{ item.is_received ? 'تم الاستلام' : 'بانتظار التسليم' }}
                            </span>
                        </td>
                    </template>
                </Table>
                
                <div v-if="students.data.length === 0" class="text-center py-20 bg-gray-50/50">
                    <p class="text-gray-400 italic">لا توجد بيانات طلاب لهذه المادة حالياً.</p>
                </div>

                <!-- Pagination -->
                <div v-if="students.links.length > 3" class="p-8 border-t border-gray-50 flex justify-center gap-2">
                    <template v-for="(link, k) in students.links" :key="k">
                        <Link v-if="link.url"
                            :href="link.url" v-html="link.label"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all"
                            :class="link.active ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white text-gray-400 hover:bg-gray-50'" />
                        <span v-else
                            v-html="link.label"
                            class="px-4 py-2 rounded-xl text-xs font-bold bg-gray-50 text-gray-300 border border-gray-100 cursor-not-allowed" />
                    </template>
                </div>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
