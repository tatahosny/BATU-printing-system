<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class StudentsImport implements ToModel, WithHeadingRow
{
    private $hierarchy;
    private $processedIds = [];
    private $masterCodes = null; // كاش أكواد الكشف الرئيسي
    private $imported = 0;
    private $skipped = 0;

    public function __construct(array $hierarchy)
    {
        $this->hierarchy = $hierarchy;

        // عند رفع كشف مادة: جلب كل أكواد الطلاب من الكشف الرئيسي مرة واحدة
        if (!empty($hierarchy['subject_id'])) {
            $this->masterCodes = Student::where('batch_id', $hierarchy['batch_id'])
                ->whereNull('subject_id')
                ->pluck('section_id', 'student_external_id')
                ->toArray();
        }
    }

    public function model(array $row)
    {
        $externalId = trim((string)$this->findValue($row, ['id', 'code', 'student_code', 'academic_id', 'كود', 'الكود', 'كود_الطالب', 'رقم_الجلوس']));
        $name = trim((string)$this->findValue($row, ['name', 'student_name', 'الاسم', 'اسم_الطالب', 'اسم']));

        if (!$externalId || !$name) {
            return null;
        }

        // منع التكرار داخل نفس الملف
        if (isset($this->processedIds[$externalId])) {
            $this->skipped++;
            return null;
        }
        $this->processedIds[$externalId] = true;

        // === الفلتر الحاسم ===
        // عند رفع كشف مادة: نقبل فقط الطلاب الموجودين في الكشف الرئيسي
        if ($this->masterCodes !== null) {
            if (!array_key_exists($externalId, $this->masterCodes)) {
                $this->skipped++;
                return null; // هذا الطالب غير موجود في الكشف الرئيسي — نتجاهله
            }
            // أخذ السكشن من الكشف الرئيسي مباشرة
            $sectionId = $this->masterCodes[$externalId];
        } else {
            $sectionId = $this->hierarchy['section_id'] ?? null;
        }

        $this->imported++;

        return Student::withTrashed()->updateOrCreate(
            [
                'student_external_id' => $externalId,
                'batch_id'            => $this->hierarchy['batch_id'],
                'subject_id'          => $this->hierarchy['subject_id'] ?? null,
            ],
            [
                'name'          => $name,
                'university_id' => $this->hierarchy['university_id'] ?? null,
                'college_id'    => $this->hierarchy['college_id'] ?? null,
                'department_id' => $this->hierarchy['department_id'] ?? null,
                'section_id'    => $sectionId,
                'deleted_at'    => null,
            ]
        );
    }

    public function getImported(): int { return $this->imported; }
    public function getSkipped(): int { return $this->skipped; }

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
