<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Transfer;
use App\Services\InventoryService;
use App\Http\Requests\TransferStockRequest;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function __construct(protected InventoryService $inventoryService) {}

    public function store(TransferStockRequest $request)
    {
        $fromUser = auth()->user();
        $data     = $request->validated();

        return DB::transaction(function () use ($data, $fromUser) {
            // التحقق من الرصيد للمندوب (الأدمن محدود أيضاً بمخزنه الرئيسي)
            $sourceInv = Inventory::forUser($fromUser->id)
                ->forSubject($data['subject_id'])
                ->lockForUpdate()
                ->first();

            if (!$fromUser->isAdmin() && (!$sourceInv || $sourceInv->quantity < $data['quantity'])) {
                return back()->with('error', 'رصيدك الحالي غير كافٍ. المتاح: ' . ($sourceInv?->quantity ?? 0));
            }

            // خصم من المرسل
            if ($sourceInv && $sourceInv->quantity >= $data['quantity']) {
                $sourceInv->decrement('quantity', $data['quantity']);
            }

            // إضافة للمستلم
            $destInv = Inventory::firstOrCreate(
                ['user_id' => $data['to_user_id'], 'subject_id' => $data['subject_id']],
                ['quantity' => 0, 'initial_quantity' => 0]
            );
            $destInv->increment('quantity', $data['quantity']);
            $destInv->increment('initial_quantity', $data['quantity']);

            // تسجيل في جدول transfers
            Transfer::create([
                'subject_id'   => $data['subject_id'],
                'from_user_id' => $fromUser->id,
                'to_user_id'   => $data['to_user_id'],
                'quantity'     => $data['quantity'],
                'notes'        => $data['notes'] ?? null,
            ]);

            return back()->with('success', 'تمت عملية نقل العهدة بنجاح');
        });
    }
}
