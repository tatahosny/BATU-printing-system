<template>
  <AuthenticatedLayout>
    <div class="inv-page" dir="rtl">
      <!-- Header -->
      <div class="inv-header">
        <div>
          <h1 class="inv-title">إدارة المخزون</h1>
          <p class="inv-subtitle">تتبع وتوزيع المواد على المناديب</p>
        </div>
        <button class="btn-primary" @click="showAddModal = true">
          <span>＋</span> إضافة مخزون
        </button>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <StatsCard label="إجمالي المخزون" :value="stats.total_stock" variant="blue" suffix="نسخة">
          <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 10V11"/></svg></template>
        </StatsCard>
        <StatsCard label="المخزن الرئيسي" :value="stats.main_store_stock" variant="purple" suffix="نسخة">
          <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg></template>
        </StatsCard>
        <StatsCard label="عند المناديب" :value="stats.delegates_stock" variant="amber" suffix="نسخة">
          <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></template>
        </StatsCard>
        <StatsCard label="تم التسليم" :value="stats.total_delivered" variant="green" suffix="طالب">
          <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></template>
        </StatsCard>
      </div>

      <!-- Tabs -->
      <div class="tabs">
        <button v-for="t in tabs" :key="t.key" class="tab-btn" :class="{ active: activeTab === t.key }" @click="activeTab = t.key">
          {{ t.label }}
        </button>
      </div>

      <!-- Tab: Main Store -->
      <div v-if="activeTab === 'main'" class="tab-content">
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr><th>المادة</th><th>الفرقة</th><th>المخزون الحالي</th><th>الموزّع</th><th>المسلّم</th><th>إجراء</th></tr>
            </thead>
            <tbody>
              <tr v-if="!main_store.length"><td colspan="6"><EmptyState title="لا يوجد مخزون حتى الآن" description="ابدأ بإضافة كمية من زر إضافة مخزون" /></td></tr>
              <tr v-for="item in main_store" :key="item.id">
                <td><span class="badge-subject">{{ item.subject?.name }}</span></td>
                <td class="text-muted">{{ item.subject?.batch?.name }}</td>
                <td><span class="qty-badge qty-badge--blue">{{ item.quantity }}</span></td>
                <td><span class="qty-badge qty-badge--amber">{{ item.initial_quantity - item.quantity }}</span></td>
                <td>
                  <ProgressBar :current="item.delivered_count" :total="item.total_students" :show-value="false" />
                  <small class="text-muted">{{ item.delivered_count }}/{{ item.total_students }}</small>
                </td>
                <td>
                  <button class="btn-sm btn-sm--indigo" @click="openDistribute(item)">توزيع</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tab: Delegate Inventories -->
      <div v-if="activeTab === 'delegates'" class="tab-content">
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr><th>المندوب</th><th>المادة</th><th>الرصيد</th><th>المسلّم</th><th>التقدم</th><th>إجراء</th></tr>
            </thead>
            <tbody>
              <tr v-if="!delegate_inventories.length"><td colspan="6"><EmptyState title="لا توجد عهد للمناديب" /></td></tr>
              <tr v-for="inv in delegate_inventories" :key="inv.id">
                <td>
                  <div class="delegate-info">
                    <span class="delegate-avatar">{{ inv.user?.name?.[0] }}</span>
                    <span>{{ inv.user?.name }}</span>
                  </div>
                </td>
                <td class="text-muted">{{ inv.subject?.name }}</td>
                <td><span class="qty-badge" :class="inv.quantity > 0 ? 'qty-badge--green' : 'qty-badge--red'">{{ inv.quantity }}</span></td>
                <td class="text-muted">{{ inv.delivered_count }}</td>
                <td style="min-width:120px">
                  <ProgressBar :current="inv.delivered_count" :total="inv.initial_quantity" :show-value="false" />
                </td>
                <td class="actions-cell">
                  <button class="btn-sm btn-sm--amber" @click="openEdit(inv)">تعديل</button>
                  <button class="btn-sm btn-sm--red" @click="confirmReset(inv)">تصفير</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tab: Transaction History -->
      <div v-if="activeTab === 'history'" class="tab-content">
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr><th>العملية</th><th>المادة</th><th>المنفذ</th><th>الكمية</th><th>قبل</th><th>بعد</th><th>التاريخ</th></tr>
            </thead>
            <tbody>
              <tr v-if="!recent_transactions.length"><td colspan="7"><EmptyState title="لا توجد حركات بعد" /></td></tr>
              <tr v-for="tx in recent_transactions" :key="tx.id">
                <td><span class="type-badge" :class="`type-badge--${tx.type}`">{{ typeLabel(tx.type) }}</span></td>
                <td class="text-muted">{{ tx.subject?.name }}</td>
                <td class="text-muted">{{ tx.user?.name }}</td>
                <td class="text-center font-bold">{{ tx.quantity }}</td>
                <td class="text-muted text-center">{{ tx.before_qty }}</td>
                <td class="text-muted text-center">{{ tx.after_qty }}</td>
                <td class="text-muted text-sm">{{ formatDate(tx.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal: Add Stock -->
      <div v-if="showAddModal" class="modal-overlay" @click.self="showAddModal = false">
        <div class="modal-box">
          <div class="modal-header">
            <h2>إضافة مخزون للمادة</h2>
            <button @click="showAddModal = false" class="modal-close">✕</button>
          </div>
          <form @submit.prevent="submitAddStock" class="modal-body">
            <CascadingFilter :universities="universities_tree" :subjects="all_subjects" v-model="addForm" />
            <div class="form-group">
              <label class="form-label">الكمية</label>
              <input type="number" v-model="addForm.quantity" min="1" class="form-input" placeholder="أدخل الكمية" required />
            </div>
            <div class="modal-footer">
              <button type="button" class="btn-secondary" @click="showAddModal = false">إلغاء</button>
              <button type="submit" class="btn-primary" :disabled="addForm.processing">
                {{ addForm.processing ? 'جارٍ الحفظ...' : 'إضافة للمخزن' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal: Distribute -->
      <div v-if="showDistributeModal" class="modal-overlay" @click.self="showDistributeModal = false">
        <div class="modal-box">
          <div class="modal-header">
            <h2>توزيع عهدة</h2>
            <button @click="showDistributeModal = false" class="modal-close">✕</button>
          </div>
          <form @submit.prevent="submitDistribute" class="modal-body">
            <div class="info-box">
              <span>المادة:</span><strong>{{ distributeForm.subject_name }}</strong>
              <span>المتاح:</span><strong class="text-green">{{ distributeForm.available }}</strong>
            </div>
            <div class="form-group">
              <label class="form-label">المندوب</label>
              <select v-model="distributeForm.delegate_id" class="form-select" required>
                <option value="">-- اختر المندوب --</option>
                <option v-for="d in all_delegates" :key="d.id" :value="d.id">{{ d.name }} ({{ roleLabel(d.role) }})</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">الكمية</label>
              <input type="number" v-model="distributeForm.quantity" :max="distributeForm.available" min="1" class="form-input" required />
            </div>
            <div class="modal-footer">
              <button type="button" class="btn-secondary" @click="showDistributeModal = false">إلغاء</button>
              <button type="submit" class="btn-primary">توزيع</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal: Edit Quantity -->
      <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
        <div class="modal-box">
          <div class="modal-header">
            <h2>تعديل العهدة</h2>
            <button @click="showEditModal = false" class="modal-close">✕</button>
          </div>
          <form @submit.prevent="submitEdit" class="modal-body">
            <div class="form-group">
              <label class="form-label">الكمية الجديدة</label>
              <input type="number" v-model="editForm.quantity" min="0" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">سبب التعديل</label>
              <input type="text" v-model="editForm.reason" class="form-input" placeholder="اختياري" />
            </div>
            <div class="modal-footer">
              <button type="button" class="btn-secondary" @click="showEditModal = false">إلغاء</button>
              <button type="submit" class="btn-primary">حفظ التغييرات</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatsCard    from '@/Components/UI/StatsCard.vue';
import ProgressBar  from '@/Components/UI/ProgressBar.vue';
import EmptyState   from '@/Components/UI/EmptyState.vue';
import CascadingFilter from '@/Components/UI/CascadingFilter.vue';

const props = defineProps({
  main_store:           { type: Array, default: () => [] },
  delegate_inventories: { type: Array, default: () => [] },
  stats:                { type: Object, default: () => ({}) },
  recent_transactions:  { type: Array, default: () => [] },
  all_delegates:        { type: Array, default: () => [] },
  all_subjects:         { type: Array, default: () => [] },
  universities_tree:    { type: Array, default: () => [] },
});

const tabs = [
  { key: 'main',      label: 'المخزن الرئيسي' },
  { key: 'delegates', label: 'عهد المناديب' },
  { key: 'history',   label: 'سجل الحركات' },
];
const activeTab = ref('main');

// Add Modal
const showAddModal = ref(false);
const addForm = ref({ university_id: '', college_id: '', department_id: '', batch_id: '', subject_id: '', quantity: '' });
function submitAddStock() {
  router.post(route('admin.inventory.add-main'), {
    subject_id: addForm.value.subject_id,
    quantity: addForm.value.quantity,
  }, { onSuccess: () => { showAddModal.value = false; } });
}

// Distribute Modal
const showDistributeModal = ref(false);
const distributeForm = ref({ subject_id: null, subject_name: '', available: 0, delegate_id: '', quantity: '' });
function openDistribute(item) {
  distributeForm.value = { subject_id: item.subject_id, subject_name: item.subject?.name, available: item.quantity, delegate_id: '', quantity: '' };
  showDistributeModal.value = true;
}
function submitDistribute() {
  router.post(route('admin.inventory.distribute'), {
    subject_id: distributeForm.value.subject_id,
    delegate_id: distributeForm.value.delegate_id,
    quantity: distributeForm.value.quantity,
  }, { onSuccess: () => { showDistributeModal.value = false; } });
}

// Edit Modal
const showEditModal = ref(false);
const editForm = ref({ id: null, quantity: '', reason: '' });
function openEdit(inv) {
  editForm.value = { id: inv.id, quantity: inv.quantity, reason: '' };
  showEditModal.value = true;
}
function submitEdit() {
  router.patch(route('admin.inventory.update-qty', editForm.value.id), {
    quantity: editForm.value.quantity,
    reason: editForm.value.reason,
  }, { onSuccess: () => { showEditModal.value = false; } });
}

// Reset
function confirmReset(inv) {
  if (confirm(`هل أنت متأكد من تصفير عهدة ${inv.user?.name}؟`)) {
    router.post(route('admin.inventory.reset-qty', inv.id));
  }
}

// Helpers
const typeLabels = { add_stock: 'إضافة', distribute: 'توزيع', receive: 'استلام', return: 'استرجاع', reset: 'تصفير', adjustment: 'تعديل', delivery: 'تسليم', cancel: 'إلغاء' };
function typeLabel(t) { return typeLabels[t] || t; }
function roleLabel(r) { return r === 'general_delegate' ? 'مندوب دفعة' : 'مندوب سكشن'; }
function formatDate(d) { return d ? new Date(d).toLocaleDateString('ar-EG', { day: '2-digit', month: 'short', year: 'numeric' }) : ''; }

// universities_tree is passed directly as a prop — no override needed
</script>

<style scoped>
.inv-page { padding: 1.5rem; max-width: 1400px; margin: 0 auto; }
.inv-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.inv-title  { font-size: 1.5rem; font-weight: 700; color: #f1f5f9; margin: 0; }
.inv-subtitle { font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0; }

.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }

.tabs { display: flex; gap: 0.25rem; background: rgba(255,255,255,0.04); border-radius: 10px; padding: 4px; margin-bottom: 1.5rem; width: fit-content; }
.tab-btn { padding: 0.5rem 1.25rem; border-radius: 7px; border: none; background: transparent; color: #94a3b8; cursor: pointer; font-size: 0.85rem; font-weight: 500; transition: all 0.2s; }
.tab-btn.active { background: #6366f1; color: #fff; }

.tab-content { animation: fadeIn 0.2s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

.table-wrap { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 12px; overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: rgba(255,255,255,0.04); color: #94a3b8; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.75rem 1rem; text-align: right; border-bottom: 1px solid rgba(255,255,255,0.06); }
.data-table td { padding: 0.85rem 1rem; color: #cbd5e1; font-size: 0.85rem; border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: rgba(255,255,255,0.02); }

.text-muted { color: #64748b; }
.text-green { color: #34d399; }
.font-bold  { font-weight: 700; }
.text-center { text-align: center; }
.text-sm { font-size: 0.78rem; }

.badge-subject { background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.2); color: #a78bfa; padding: 4px 12px; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; }
.qty-badge { padding: 4px 12px; border-radius: 10px; font-weight: 900; font-size: 0.85rem; }
.qty-badge--blue   { background: rgba(59,130,246,0.1);  color: #60a5fa; border: 1px solid rgba(59,130,246,0.2); }
.qty-badge--green  { background: rgba(16,185,129,0.1);  color: #34d399; border: 1px solid rgba(16,185,129,0.2); }
.qty-badge--amber  { background: rgba(245,158,11,0.1);  color: #fbbf24; border: 1px solid rgba(245,158,11,0.2); }
.qty-badge--red    { background: rgba(239,68,68,0.1);   color: #f87171; border: 1px solid rgba(239,68,68,0.2); }

.type-badge { padding: 3px 12px; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid transparent; }
.type-badge--add_stock  { background: rgba(16,185,129,0.1); color: #34d399; border-color: rgba(16,185,129,0.2); }
.type-badge--distribute { background: rgba(99,102,241,0.1); color: #a78bfa; border-color: rgba(99,102,241,0.2); }
.type-badge--receive    { background: rgba(59,130,246,0.1);  color: #60a5fa; border-color: rgba(59,130,246,0.2); }
.type-badge--return     { background: rgba(245,158,11,0.1);  color: #fbbf24; border-color: rgba(245,158,11,0.2); }
.type-badge--reset      { background: rgba(239,68,68,0.1);   color: #f87171; border-color: rgba(239,68,68,0.2); }
.type-badge--adjustment { background: rgba(148,163,184,0.1);  color: #94a3b8; border-color: rgba(148,163,184,0.2); }
.type-badge--delivery   { background: rgba(16,185,129,0.1);  color: #34d399; border-color: rgba(16,185,129,0.2); }
.type-badge--cancel     { background: rgba(239,68,68,0.1);   color: #f87171; border-color: rgba(239,68,68,0.2); }

.delegate-info { display: flex; align-items: center; gap: 0.5rem; }
.delegate-avatar { width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: #fff; flex-shrink: 0; }
.actions-cell { display: flex; gap: 0.4rem; }

.btn-primary   { display: flex; align-items: center; gap: 0.4rem; padding: 0.6rem 1.25rem; background: #6366f1; color: #fff; border: none; border-radius: 9px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; }
.btn-primary:hover { background: #4f46e5; transform: translateY(-1px); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.btn-secondary { padding: 0.6rem 1.25rem; background: rgba(255,255,255,0.06); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); border-radius: 9px; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; }

.btn-sm { padding: 4px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.15s; }
.btn-sm--indigo { background: rgba(99,102,241,0.15); color: #818cf8; }
.btn-sm--amber  { background: rgba(245,158,11,0.15);  color: #fbbf24; }
.btn-sm--red    { background: rgba(239,68,68,0.15);   color: #f87171; }
.btn-sm:hover   { filter: brightness(1.2); }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 100; display: flex; align-items: center; justify-content: center; padding: 1rem; }
.modal-box { background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; width: 100%; max-width: 540px; overflow: hidden; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.06); }
.modal-header h2 { font-size: 1.05rem; font-weight: 700; color: #f1f5f9; margin: 0; }
.modal-close { background: none; border: none; color: #64748b; font-size: 1.1rem; cursor: pointer; padding: 4px; border-radius: 6px; }
.modal-close:hover { color: #f87171; background: rgba(239,68,68,0.1); }
.modal-body { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 0.5rem; }

.form-group { display: flex; flex-direction: column; gap: 0.4rem; }
.form-label  { font-size: 0.8rem; color: #94a3b8; font-weight: 500; }
.form-input, .form-select { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e2e8f0; padding: 0.6rem 0.85rem; font-size: 0.85rem; outline: none; transition: border-color 0.2s; width: 100%; }
.form-input:focus, .form-select:focus { border-color: #6366f1; }
.form-select option { background: #1e293b; }

.info-box { display: flex; gap: 0.75rem; flex-wrap: wrap; background: rgba(99,102,241,0.08); border: 1px solid rgba(99,102,241,0.2); border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.85rem; color: #94a3b8; align-items: center; }
.info-box strong { color: #e2e8f0; }
</style>
