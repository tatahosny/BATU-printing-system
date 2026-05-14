<template>
  <div class="progress-bar-wrapper">
    <div v-if="label || showValue" class="progress-bar-header">
      <span class="progress-bar-label">{{ label }}</span>
      <span class="progress-bar-value">{{ current }} / {{ total }}
        <span class="progress-bar-pct">({{ percentage }}%)</span>
      </span>
    </div>
    <div class="progress-bar-track" :style="{ height: height + 'px' }">
      <div
        class="progress-bar-fill"
        :class="`progress-bar-fill--${colorVariant}`"
        :style="{ width: percentage + '%' }"
      >
        <span v-if="percentage > 15 && showInnerText" class="progress-bar-inner-text">{{ percentage }}%</span>
      </div>
    </div>
    <div v-if="sublabel" class="progress-bar-sublabel">{{ sublabel }}</div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  current:       { type: Number, default: 0 },
  total:         { type: Number, default: 100 },
  label:         { type: String, default: '' },
  sublabel:      { type: String, default: '' },
  height:        { type: Number, default: 10 },
  showValue:     { type: Boolean, default: true },
  showInnerText: { type: Boolean, default: false },
});

const percentage = computed(() =>
  props.total > 0 ? Math.min(100, Math.round((props.current / props.total) * 100)) : 0
);

const colorVariant = computed(() => {
  const p = percentage.value;
  if (p >= 90) return 'green';
  if (p >= 60) return 'blue';
  if (p >= 30) return 'amber';
  return 'red';
});
</script>

<style scoped>
.progress-bar-wrapper { width: 100%; }
.progress-bar-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 6px;
}
.progress-bar-label { font-size: 0.8rem; color: #94a3b8; font-weight: 500; }
.progress-bar-value { font-size: 0.8rem; color: #cbd5e1; font-weight: 600; }
.progress-bar-pct   { color: #64748b; font-weight: 400; }
.progress-bar-sublabel { font-size: 0.72rem; color: #64748b; margin-top: 4px; }

.progress-bar-track {
  background: rgba(255,255,255,0.06);
  border-radius: 999px;
  overflow: hidden;
  width: 100%;
}
.progress-bar-fill {
  height: 100%;
  border-radius: 999px;
  transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex; align-items: center; justify-content: flex-end;
  padding-right: 6px;
  position: relative;
}
.progress-bar-fill--green { background: linear-gradient(90deg, #059669, #10b981); }
.progress-bar-fill--blue  { background: linear-gradient(90deg, #2563eb, #3b82f6); }
.progress-bar-fill--amber { background: linear-gradient(90deg, #d97706, #f59e0b); }
.progress-bar-fill--red   { background: linear-gradient(90deg, #dc2626, #ef4444); }

.progress-bar-inner-text { font-size: 0.65rem; color: #fff; font-weight: 700; white-space: nowrap; }
</style>
