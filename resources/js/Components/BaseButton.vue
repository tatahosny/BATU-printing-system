<template>
  <button
    :class="[
      'inline-flex items-center justify-center px-4 py-2 rounded-lg font-bold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2',
      variant === 'primary' ? 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500' :
      variant === 'secondary' ? 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 focus:ring-slate-500' :
      variant === 'danger' ? 'bg-red-50 text-red-600 hover:bg-red-100 focus:ring-red-500' : ''
    ]"
    :disabled="loading"
  >
    <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <slot />
  </button>
</template>

<script setup>
defineProps({
  variant: { default: 'primary' },
  loading: { default: false }
});
</script>
