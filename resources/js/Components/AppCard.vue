<template>
  <div class="bg-white border border-slate-200 rounded-[24px] p-6 hover:border-indigo-500/50 hover:shadow-xl hover:shadow-indigo-500/5 transition-all cursor-pointer group">
    <div class="flex justify-between items-center">
      <slot />
      <div class="text-slate-300 group-hover:text-indigo-500 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
    </div>
  </div>
</template>
