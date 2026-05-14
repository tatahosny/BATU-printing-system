<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="$emit('close')"></div>
    <div class="relative bg-white w-full max-w-lg rounded-[32px] p-8 shadow-2xl">
      <slot />
    </div>
  </div>
</template>
<script setup>defineProps(['show']); defineEmits(['close']);</script>
