<template>
  <div class="empty-state">
    <div class="empty-state__icon">
      <slot name="icon">
        <svg viewBox="0 0 64 64" fill="none">
          <circle cx="32" cy="32" r="28" fill="rgba(99,102,241,0.08)" />
          <path d="M22 32h20M32 22v20" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round"/>
        </svg>
      </slot>
    </div>
    <h3 class="empty-state__title">{{ title }}</h3>
    <p v-if="description" class="empty-state__description">{{ description }}</p>
    <slot name="action" />
  </div>
</template>

<script setup>
defineProps({
  title:       { type: String, default: 'لا توجد بيانات' },
  description: { type: String, default: '' },
});
</script>

<style scoped>
.empty-state {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 3rem 2rem; text-align: center;
  gap: 0.75rem;
}
.empty-state__icon { width: 72px; height: 72px; margin-bottom: 0.5rem; opacity: 0.9; }
.empty-state__icon svg { width: 100%; height: 100%; }
.empty-state__title { font-size: 1rem; font-weight: 600; color: #cbd5e1; margin: 0; }
.empty-state__description { font-size: 0.85rem; color: #64748b; margin: 0; max-width: 28rem; }
</style>
