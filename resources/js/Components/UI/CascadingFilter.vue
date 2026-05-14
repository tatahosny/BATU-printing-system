<template>
  <div class="cascading-filter" dir="rtl">
    <!-- الجامعة -->
    <div class="cf-group">
      <label class="cf-label">الجامعة</label>
      <select class="cf-select" v-model="selected.university_id" @change="onUniversityChange">
        <option value="">-- اختر الجامعة --</option>
        <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
      </select>
    </div>

    <!-- الكلية -->
    <div class="cf-group">
      <label class="cf-label">الكلية</label>
      <select class="cf-select" v-model="selected.college_id" @change="onCollegeChange" :disabled="!filteredColleges.length">
        <option value="">-- اختر الكلية --</option>
        <option v-for="c in filteredColleges" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
    </div>

    <!-- القسم -->
    <div class="cf-group">
      <label class="cf-label">القسم</label>
      <select class="cf-select" v-model="selected.department_id" @change="onDeptChange" :disabled="!filteredDepartments.length">
        <option value="">-- اختر القسم --</option>
        <option v-for="d in filteredDepartments" :key="d.id" :value="d.id">{{ d.name }}</option>
      </select>
    </div>

    <!-- الفرقة -->
    <div class="cf-group" v-if="showBatch">
      <label class="cf-label">الفرقة</label>
      <select class="cf-select" v-model="selected.batch_id" @change="onBatchChange" :disabled="!filteredBatches.length">
        <option value="">-- اختر الفرقة --</option>
        <option v-for="b in filteredBatches" :key="b.id" :value="b.id">{{ b.name }}</option>
      </select>
    </div>

    <!-- المادة -->
    <div class="cf-group" v-if="showSubject">
      <label class="cf-label">المادة</label>
      <select class="cf-select" v-model="selected.subject_id" @change="emit('update:modelValue', selected)" :disabled="!filteredSubjects.length">
        <option value="">-- اختر المادة --</option>
        <option v-for="s in filteredSubjects" :key="s.id" :value="s.id">{{ s.name }}</option>
      </select>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  universities: { type: Array, default: () => [] },
  subjects:     { type: Array, default: () => [] },
  modelValue:   { type: Object, default: () => ({}) },
  showBatch:    { type: Boolean, default: true },
  showSubject:  { type: Boolean, default: true },
});
const emit = defineEmits(['update:modelValue']);

const selected = ref({
  university_id: props.modelValue?.university_id || '',
  college_id:    props.modelValue?.college_id    || '',
  department_id: props.modelValue?.department_id || '',
  batch_id:      props.modelValue?.batch_id      || '',
  subject_id:    props.modelValue?.subject_id    || '',
});

const filteredColleges = computed(() => {
  if (!selected.value.university_id) return [];
  const uni = props.universities.find(u => u.id == selected.value.university_id);
  return uni?.colleges || [];
});

const filteredDepartments = computed(() => {
  if (!selected.value.college_id) return [];
  const col = filteredColleges.value.find(c => c.id == selected.value.college_id);
  return col?.departments || [];
});

const filteredBatches = computed(() => {
  if (!selected.value.department_id) return [];
  const dept = filteredDepartments.value.find(d => d.id == selected.value.department_id);
  return dept?.batches || [];
});

const filteredSubjects = computed(() => {
  if (!selected.value.batch_id) return [];
  return props.subjects.filter(s => s.batch_id == selected.value.batch_id);
});

function onUniversityChange() {
  selected.value.college_id = '';
  selected.value.department_id = '';
  selected.value.batch_id = '';
  selected.value.subject_id = '';
  emit('update:modelValue', selected.value);
}
function onCollegeChange() {
  selected.value.department_id = '';
  selected.value.batch_id = '';
  selected.value.subject_id = '';
  emit('update:modelValue', selected.value);
}
function onDeptChange() {
  selected.value.batch_id = '';
  selected.value.subject_id = '';
  emit('update:modelValue', selected.value);
}
function onBatchChange() {
  selected.value.subject_id = '';
  emit('update:modelValue', selected.value);
}
</script>

<style scoped>
.cascading-filter { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end; }
.cf-group { display: flex; flex-direction: column; gap: 0.35rem; flex: 1; min-width: 140px; }
.cf-label { font-size: 0.75rem; color: #94a3b8; font-weight: 500; }
.cf-select {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px;
  color: #e2e8f0;
  padding: 0.5rem 0.75rem;
  font-size: 0.85rem;
  outline: none;
  transition: border-color 0.2s;
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: left 0.5rem center;
  background-size: 1rem;
  padding-left: 2rem;
}
.cf-select:focus { border-color: #6366f1; }
.cf-select:disabled { opacity: 0.4; cursor: not-allowed; }
.cf-select option { background: #1e293b; color: #e2e8f0; }
</style>
