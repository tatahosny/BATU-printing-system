<template>
  <div class="flex flex-col gap-1.5">
    <label v-if="label" class="text-sm font-semibold text-slate-700">{{ label }}</label>
    <input
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      class="px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm shadow-sm placeholder-slate-400
             focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500
             transition-all duration-200"
      v-bind="$attrs"
    />
  </div>
</template>

<script setup>
defineProps(['label', 'modelValue']);
defineEmits(['update:modelValue']);
</script>
