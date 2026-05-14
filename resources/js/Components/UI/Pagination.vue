<template>
  <div v-if="links && links.length > 3" class="flex flex-wrap gap-1">
    <template v-for="(link, p) in links" :key="p">
      <div 
        v-if="link.url === null" 
        class="px-3 py-2 text-sm text-slate-400 bg-slate-800/30 border border-white/5 rounded-lg opacity-50 cursor-not-allowed" 
        v-html="link.label" 
      />
      <Link 
        v-else 
        :href="link.url"
        class="px-3 py-2 text-sm border rounded-lg transition-colors hover:bg-slate-700/50 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
        :class="{ 
          'bg-indigo-600 text-white border-indigo-500 hover:bg-indigo-700 font-bold': link.active, 
          'bg-slate-800/50 text-slate-300 border-white/10': !link.active 
        }" 
        v-html="link.label" 
      />
    </template>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  links: {
    type: Array,
    required: true,
  },
});
</script>
