<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class SectionMatchingImport implements ToModel, WithHeadingRow
{
    private $subjectId;

    public function __construct($subjectId)
    {
        $this->subjectId = $subjectId;
    }

    public function model(array $row)
    {
        $delegateId = auth()->id();
        
        // البحث عن الكود في الملف المرفوع
        $studentExternalId = $this->findValue($row, ['id', 'code', 'student_code', 'academic_id', 'كود', 'الكود', 'كود_الطالب']);

        if (!$studentExternalId) {
            return null;
        }

        // البحث عن الطالب في الكشوفات الرئيسية لهذه المادة
        $student = Student::where('subject_id', $this->subjectId)
            ->where('student_external_id', $studentExternalId)
            ->first();

        if ($student) {
            // إذا وجدنا الطالب، نربطه بالمندوب ونزود عهدة المندوب
            // نتأكد أولاً أنه لم يتم تعيينه لمندوب آخر أو لنفس المندوب مسبقاً لمنع التكرار
            if ($student->delegate_id !== $delegateId) {
                $student->update([
                    'delegate_id' => $delegateId
                ]);

                // زيادة عهدة المندوب آلياً بواقع نسخة واحدة لهذا الطالب
                $inventory = Inventory::firstOrCreate(
                    ['user_id' => $delegateId, 'subject_id' => $this->subjectId],
                    ['quantity' => 0, 'initial_quantity' => 0]
                );
                
                $inventory->increment('quantity', 1);
            }
        }

        return null; // لا نحتاج لإنشاء سجل جديد هنا، فقط تحديث
    }

    private function findValue(array $row, array $keys)
    {
        foreach ($row as $key => $value) {
            $cleanKey = Str::slug($key, '_');
            foreach ($keys as $target) {
                if ($cleanKey === $target || $key === $target || str_contains($key, $target)) {
                    return $value;
                }
            }
        }
        return null;
    }
}
