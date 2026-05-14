<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * إضافة كمية للمخزن الرئيسي (الأدمن)
     */
    public function addStock(User $admin, Subject $subject, int $quantity, string $description = ''): Inventory
    {
        return DB::transaction(function () use ($admin, $subject, $quantity, $description) {
            $inventory = Inventory::firstOrCreate(
                ['user_id' => $admin->id, 'subject_id' => $subject->id],
                ['quantity' => 0, 'initial_quantity' => 0]
            );

            $before = $inventory->quantity;

            $inventory->increment('quantity', $quantity);
            $inventory->increment('initial_quantity', $quantity);
            $inventory->refresh();

            $this->logTransaction($inventory, $admin, $subject, 'add_stock', $quantity, $before, $inventory->quantity, null, null, $description ?: "إضافة {$quantity} نسخة للمخزن الرئيسي - مادة: {$subject->name}");

            return $inventory;
        });
    }

    /**
     * توزيع كمية من الأدمن إلى مندوب
     */
    public function distributeToDelegate(User $admin, User $delegate, Subject $subject, int $quantity, string $notes = ''): array
    {
        return DB::transaction(function () use ($admin, $delegate, $subject, $quantity, $notes) {
            // جلب مخزن الأدمن مع Lock لمنع Race Conditions
            $adminInventory = Inventory::forUser($admin->id)
                ->forSubject($subject->id)
                ->lockForUpdate()
                ->first();

            if (!$adminInventory || $adminInventory->quantity < $quantity) {
                return ['success' => false, 'message' => "رصيد المخزن الرئيسي غير كافٍ. المتاح: " . ($adminInventory?->quantity ?? 0)];
            }

            $adminBefore = $adminInventory->quantity;
            $adminInventory->decrement('quantity', $quantity);

            // إضافة للمندوب
            $delegateInventory = Inventory::firstOrCreate(
                ['user_id' => $delegate->id, 'subject_id' => $subject->id],
                ['quantity' => 0, 'initial_quantity' => 0]
            );

            $delegateBefore = $delegateInventory->quantity;
            $delegateInventory->increment('quantity', $quantity);
            $delegateInventory->increment('initial_quantity', $quantity);

            $adminInventory->refresh();
            $delegateInventory->refresh();

            // تسجيل حركة الخروج من الأدمن
            $this->logTransaction($adminInventory, $admin, $subject, 'distribute', $quantity, $adminBefore, $adminInventory->quantity, $admin->id, $delegate->id, "توزيع {$quantity} نسخة على المندوب: {$delegate->name}");

            // تسجيل حركة الدخول للمندوب
            $this->logTransaction($delegateInventory, $delegate, $subject, 'receive', $quantity, $delegateBefore, $delegateInventory->quantity, $admin->id, $delegate->id, "استلام {$quantity} نسخة من الأدمن");

            return ['success' => true, 'message' => "تم توزيع {$quantity} نسخة على {$delegate->name} بنجاح"];
        });
    }

    /**
     * استرجاع كمية من مندوب للمخزن الرئيسي
     */
    public function returnToMainStore(User $admin, Inventory $delegateInventory, int $quantity, string $reason = ''): array
    {
        return DB::transaction(function () use ($admin, $delegateInventory, $quantity, $reason) {
            $delegateInventory->lockForUpdate()->refresh();

            if ($delegateInventory->quantity < $quantity) {
                return ['success' => false, 'message' => "كمية الاسترجاع أكبر من رصيد المندوب. الرصيد الحالي: {$delegateInventory->quantity}"];
            }

            $delegateBefore = $delegateInventory->quantity;
            $delegateInventory->decrement('quantity', $quantity);
            $delegateInventory->decrement('initial_quantity', $quantity);

            // إضافة للمخزن الرئيسي
            $adminInventory = Inventory::firstOrCreate(
                ['user_id' => $admin->id, 'subject_id' => $delegateInventory->subject_id],
                ['quantity' => 0, 'initial_quantity' => 0]
            );

            $adminBefore = $adminInventory->quantity;
            $adminInventory->increment('quantity', $quantity);
            $adminInventory->increment('initial_quantity', $quantity);

            $delegateInventory->refresh();
            $adminInventory->refresh();

            $description = "استرجاع {$quantity} نسخة من " . $delegateInventory->user->name . ". السبب: " . ($reason ?: 'غير محدد');
            $this->logTransaction($delegateInventory, $admin, $delegateInventory->subject, 'return', $quantity, $delegateBefore, $delegateInventory->quantity, $delegateInventory->user_id, $admin->id, $description);
            $this->logTransaction($adminInventory, $admin, $delegateInventory->subject, 'receive', $quantity, $adminBefore, $adminInventory->quantity, $delegateInventory->user_id, $admin->id, $description);

            return ['success' => true, 'message' => "تم استرجاع {$quantity} نسخة بنجاح"];
        });
    }

    /**
     * تصفير عهدة مندوب بالكامل
     */
    public function resetInventory(User $admin, Inventory $inventory, string $reason = ''): array
    {
        return DB::transaction(function () use ($admin, $inventory, $reason) {
            $inventory->lockForUpdate()->refresh();
            $currentQty = $inventory->quantity;

            if ($currentQty === 0) {
                return ['success' => false, 'message' => 'الرصيد بالفعل صفر'];
            }

            $this->logTransaction($inventory, $admin, $inventory->subject, 'reset', $currentQty, $currentQty, 0, $inventory->user_id, $admin->id, "تصفير عهدة المندوب: {$inventory->user->name}. السبب: " . ($reason ?: 'غير محدد'));

            $inventory->update(['quantity' => 0, 'initial_quantity' => 0]);

            return ['success' => true, 'message' => "تم تصفير عهدة {$inventory->user->name} (كانت {$currentQty} نسخة)"];
        });
    }

    /**
     * تعديل يدوي للكمية
     */
    public function adjustQuantity(User $admin, Inventory $inventory, int $newQty, string $reason = ''): array
    {
        return DB::transaction(function () use ($admin, $inventory, $newQty, $reason) {
            $inventory->lockForUpdate()->refresh();
            $oldQty = $inventory->quantity;

            $this->logTransaction($inventory, $admin, $inventory->subject, 'adjustment', abs($newQty - $oldQty), $oldQty, $newQty, null, null, "تعديل يدوي من {$oldQty} إلى {$newQty}. السبب: " . ($reason ?: 'غير محدد'));

            $inventory->update(['quantity' => $newQty]);

            return ['success' => true, 'message' => "تم تحديث الكمية من {$oldQty} إلى {$newQty}"];
        });
    }

    /**
     * تسجيل حركة المخزون
     */
    private function logTransaction(Inventory $inventory, User $actor, Subject $subject, string $type, int $quantity, int $before, int $after, ?int $fromUserId, ?int $toUserId, string $description): void
    {
        InventoryTransaction::create([
            'inventory_id' => $inventory->id,
            'user_id'      => $actor->id,
            'subject_id'   => $subject->id,
            'type'         => $type,
            'quantity'     => $quantity,
            'before_qty'   => $before,
            'after_qty'    => $after,
            'from_user_id' => $fromUserId,
            'to_user_id'   => $toUserId,
            'description'  => $description,
        ]);
    }
}
