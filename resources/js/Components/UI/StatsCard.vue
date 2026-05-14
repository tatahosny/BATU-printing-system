<template>
  <div class="stats-card" :class="[`stats-card--${variant}`, { 'stats-card--clickable': clickable }]" @click="$emit('click')">
    <div class="stats-card__icon">
      <slot name="icon" />
    </div>
    <div class="stats-card__content">
      <p class="stats-card__label">{{ label }}</p>
      <p class="stats-card__value">
        <span v-if="loading" class="stats-card__skeleton" />
        <span v-else>{{ formattedValue }}</span>
      </p>
      <p v-if="subtitle" class="stats-card__subtitle">{{ subtitle }}</p>
    </div>
    <div v-if="trend !== null" class="stats-card__trend" :class="trend >= 0 ? 'stats-card__trend--up' : 'stats-card__trend--down'">
      <svg v-if="trend >= 0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
      <svg v-else viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
      {{ Math.abs(trend) }}%
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label:     { type: String, required: true },
  value:     { type: [Number, String], default: 0 },
  subtitle:  { type: String, default: null },
  variant:   { type: String, default: 'default' }, // default, blue, green, amber, red, purple
  trend:     { type: Number, default: null },
  loading:   { type: Boolean, default: false },
  clickable: { type: Boolean, default: false },
  suffix:    { type: String, default: '' },
});

defineEmits(['click']);

const formattedValue = computed(() => {
  const v = Number(props.value);
  if (isNaN(v)) return props.value;
  return v.toLocaleString('ar-EG') + (props.suffix ? ' ' + props.suffix : '');
});
</script>

<style scoped>
.stats-card {
  background: var(--card-bg, #1e293b);
  border: 1px solid var(--card-border, rgba(255,255,255,0.08));
  border-radius: 16px;
  padding: 1.25rem 1.5rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  transition: all 0.2s ease;
  position: relative;
  overflow: hidden;
}
.stats-card::before {
  content: '';
  position: absolute;
  inset: 0;
  opacity: 0;
  transition: opacity 0.2s;
  background: radial-gradient(ellipse at top right, rgba(99,102,241,0.08), transparent 70%);
}
.stats-card--clickable { cursor: pointer; }
.stats-card--clickable:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); }
.stats-card--clickable:hover::before { opacity: 1; }

.stats-card__icon {
  width: 48px; height: 48px;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.stats-card--blue   .stats-card__icon { background: rgba(59,130,246,0.15); color: #60a5fa; }
.stats-card--green  .stats-card__icon { background: rgba(16,185,129,0.15); color: #34d399; }
.stats-card--amber  .stats-card__icon { background: rgba(245,158,11,0.15);  color: #fbbf24; }
.stats-card--red    .stats-card__icon { background: rgba(239,68,68,0.15);   color: #f87171; }
.stats-card--purple .stats-card__icon { background: rgba(139,92,246,0.15);  color: #a78bfa; }
.stats-card--default .stats-card__icon { background: rgba(148,163,184,0.1); color: #94a3b8; }

.stats-card__content { flex: 1; min-width: 0; }
.stats-card__label { font-size: 0.78rem; color: #94a3b8; margin: 0 0 0.25rem; font-weight: 500; }
.stats-card__value { font-size: 1.75rem; font-weight: 700; color: #f1f5f9; margin: 0; line-height: 1.1; }
.stats-card__subtitle { font-size: 0.75rem; color: #64748b; margin: 0.25rem 0 0; }

.stats-card__skeleton {
  display: inline-block; width: 5rem; height: 1.75rem;
  background: linear-gradient(90deg, #1e293b 25%, #334155 50%, #1e293b 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 6px;
}
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

.stats-card__trend {
  display: flex; align-items: center; gap: 2px;
  font-size: 0.75rem; font-weight: 600;
  padding: 2px 8px; border-radius: 20px;
  flex-shrink: 0;
}
.stats-card__trend svg { width: 14px; height: 14px; }
.stats-card__trend--up   { background: rgba(16,185,129,0.15); color: #34d399; }
.stats-card__trend--down { background: rgba(239,68,68,0.15);  color: #f87171; }
</style>
