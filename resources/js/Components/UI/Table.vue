<script setup>
defineProps({
    headers: {
        type: Array,
        required: true,
    },
    items: {
        type: Array,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="flow-root overflow-x-auto scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
        <div class="inline-block min-w-full align-middle">
            <div class="overflow-hidden bg-slate-900/20">
                <table class="min-w-full divide-y divide-white/5 border-separate border-spacing-0">
                    <thead class="bg-white/2 backdrop-blur-md">
                        <tr>
                            <th
                                v-for="(header, idx) in headers"
                                :key="header"
                                scope="col"
                                :class="[
                                    'py-5 text-right text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] px-8',
                                    idx === 0 ? 'pr-8' : '',
                                    idx === headers.length - 1 ? 'pl-8' : ''
                                ]"
                            >
                                {{ header }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-if="loading">
                            <td :colspan="headers.length" class="py-20 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <div class="h-2 w-2 bg-indigo-500 rounded-full animate-bounce"></div>
                                    <div class="h-2 w-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                    <div class="h-2 w-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                </div>
                                <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mt-4">جاري التحميل...</p>
                            </td>
                        </tr>
                        <tr v-else-if="items.length === 0">
                            <td :colspan="headers.length" class="py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-16 h-16 bg-white/5 rounded-2xl flex items-center justify-center text-3xl opacity-20">📊</div>
                                    <p class="text-xs font-black text-slate-600 uppercase tracking-widest">لا توجد بيانات متاحة حالياً</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="(item, index) in items" :key="index" class="hover:bg-white/2 transition-all duration-300 group/row">
                            <slot name="row" :item="item" :index="index" />
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar {
    height: 4px;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.1);
}
</style>
