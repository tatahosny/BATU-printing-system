<script setup>
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-xl font-bold text-red-400">حذف الحساب</h2>
            <p class="mt-1 text-sm text-slate-400">بمجرد حذف حسابك، سيتم حذف جميع موارده وبياناته بشكل دائم.</p>
        </header>

        <Button variant="danger" @click="confirmUserDeletion">حذف الحساب نهائياً</Button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-8 bg-slate-900 border border-white/5 rounded-3xl">
                <h2 class="text-2xl font-black text-white mb-4">هل أنت متأكد من حذف الحساب؟</h2>

                <p class="text-sm text-slate-400 mb-8 leading-relaxed">
                    بمجرد حذف حسابك، سيتم حذف جميع موارده وبياناته بشكل دائم. يرجى إدخال كلمة المرور الخاصة بك لتأكيد رغبتك في حذف حسابك نهائيًا.
                </p>

                <div class="space-y-4">
                    <label class="block text-sm font-bold text-slate-300">كلمة المرور</label>
                    <input
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all"
                        placeholder="أدخل كلمة المرور للتأكيد"
                        @keyup.enter="deleteUser"
                    />
                    <p v-if="form.errors.password" class="mt-2 text-sm text-red-400 font-medium">{{ form.errors.password }}</p>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <Button variant="secondary" @click="closeModal">إلغاء</Button>
                    <Button
                        variant="danger"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        تأكيد حذف الحساب
                    </Button>
                </div>
            </div>
        </Modal>
    </section>
</template>
