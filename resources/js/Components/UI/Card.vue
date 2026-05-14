<script setup>
defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    noPadding: { type: Boolean, default: false },
});
</script>

<template>
    <div class="card-ui overflow-hidden rounded-2xl transition-all duration-300">
        <div v-if="title || subtitle || $slots.header" class="px-6 py-5 border-b border-white/5 bg-white/2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 v-if="title" class="text-base font-bold leading-6 text-white/90">
                        {{ title }}
                    </h3>
                    <p v-if="subtitle" class="mt-1 text-xs text-slate-500 font-medium">
                        {{ subtitle }}
                    </p>
                </div>
                <div v-if="$slots.headerAction">
                    <slot name="headerAction" />
                </div>
            </div>
            <slot name="header" />
        </div>
        <div :class="noPadding ? '' : 'p-6'">
            <slot />
        </div>
        <div v-if="$slots.footer" class="px-6 py-4 bg-white/2 border-t border-white/5">
            <slot name="footer" />
        </div>
    </div>
</template>

<style scoped>
.card-ui {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,0.06);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}
.card-ui:hover {
    border-color: rgba(99,102,241,0.3);
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
}
</style>
