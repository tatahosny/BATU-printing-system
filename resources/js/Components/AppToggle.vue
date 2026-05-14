<template>
  <button
    @click="$emit('update:modelValue', !modelValue)"
    :class="modelValue ? 'bg-indigo-600' : 'bg-slate-200'"
    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
  >
    <span
      :class="modelValue ? 'translate-x-6' : 'translate-x-1'"
      class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
    />
  </button>
</template>
<script setup>defineProps(['modelValue']); defineEmits(['update:modelValue']);</script>
