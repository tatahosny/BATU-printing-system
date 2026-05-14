<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Section;
use App\Models\Batch;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Throwable;

class BatchStudentsImport implements ToModel, WithHeadingRow, SkipsOnError, WithBatchInserts, WithChunkReading
{
    use SkipsErrors;

    protected int $batchId;
    protected array $hierarchy;
    protected array $sectionCache = [];
    protected array $processedIds = []; // تتبع الأكواد في هذا الرفع لمنع التكرار الداخلي
    protected int $imported = 0;
    protected int $skipped  = 0;

    public function __construct(int $batchId, array $hierarchy)
    {
        $this->batchId   = $batchId;
        $this->hierarchy = $hierarchy;

        // بناء cache للسكاشن حسب الاسم
        $sections = Section::where('batch_id', $batchId)->get();
        foreach ($sections as $sec) {
            $this->sectionCache[trim($sec->name)] = $sec->id;
        }
    }

    public function model(array $row): ?Student
    {
        // البحث عن القيم بمرونة عالية
        $name       = $this->findValue($row, ['name', 'student_name', 'الاسم', 'اسم_الطالب', 'اسم']);
        $externalId = $this->findValue($row, ['id', 'code', 'student_code', 'academic_id', 'كود', 'الكود', 'كود_الطالب', 'رقم_الجلوس']);
        $sectionName= $this->findValue($row, ['section', 'group', 'السكشن', 'سكشن', 'مجموعة']);

        if (!$externalId || !$name) {
            return null;
        }

        $externalId = trim((string)$externalId);
        
        // منع التكرار داخل نفس ملف الإكسيل
        if (isset($this->processedIds[$externalId])) {
            $this->skipped++;
            return null;
        }
        $this->processedIds[$externalId] = true;

        // تحديد الـ section_id من الاسم
        $sectionId = null;
        if ($sectionName) {
            $cleanSection = trim((string)$sectionName);
            if (isset($this->sectionCache[$cleanSection])) {
                $sectionId = $this->sectionCache[$cleanSection];
            } else {
                $existingSection = Section::where('batch_id', $this->batchId)->where('name', $cleanSection)->first();
                if ($existingSection) {
                    $sectionId = $existingSection->id;
                } else {
                    $newSection = Section::create(['name' => $cleanSection, 'batch_id' => $this->batchId]);
                    $sectionId = $newSection->id;
                }
                $this->sectionCache[$cleanSection] = $sectionId;
            }
        } else {
            $sectionId = $this->findSectionByRange($externalId);
        }

        $this->imported++;

        // --- منطق التنظيف الذكي للتكرارات (Smart Duplicate Cleanup) ---
        // نتحقق من وجود تكرارات سابقة لهذا الطالب ونقوم بدمجها، مع الحفاظ على حالة "تم التسليم"
        $duplicates = Student::withTrashed()
            ->where('student_external_id', $externalId)
            ->where('batch_id', $this->batchId)
            ->whereNull('subject_id')
            ->get();

        if ($duplicates->count() > 1) {
            // نختار السجل الذي يحتوي على حالة "تم التسليم" كالسجل الأساسي
            $master = $duplicates->where('is_received', true)->first() ?? $duplicates->first();
            
            // حذف باقي التكرارات نهائياً من قاعدة البيانات
            Student::where('student_external_id', $externalId)
                ->where('batch_id', $this->batchId)
                ->whereNull('subject_id')
                ->where('id', '!=', $master->id)
                ->forceDelete();
        }
        // -----------------------------------------------------------

        return Student::withTrashed()->updateOrCreate(
            [
                'student_external_id' => $externalId,
                'batch_id'            => $this->batchId,
                'subject_id'          => null,
            ],
            [
                'name'          => trim((string)$name),
                'university_id' => $this->hierarchy['university_id'] ?? null,
                'college_id'    => $this->hierarchy['college_id']    ?? null,
                'department_id' => $this->hierarchy['department_id'] ?? null,
                'section_id'    => $sectionId,
                'deleted_at'    => null,
            ]
        );
    }

    /**
     * البحث عن قيمة بمرونة
     */
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

    /**
     * إيجاد السكشن بناءً على النطاق الرقمي
     */
    protected function findSectionByRange($externalId): ?int
    {
        $numId = (int) preg_replace('/\D/', '', (string)$externalId);
        if ($numId <= 0) return null;

        foreach ($this->sectionCache as $name => $sectionId) {
            $section = Section::find($sectionId);
            if ($section && $section->range_start && $section->range_end) {
                $start = (int) preg_replace('/\D/', '', $section->range_start);
                $end   = (int) preg_replace('/\D/', '', $section->range_end);
                if ($numId >= $start && $numId <= $end) return $sectionId;
            }
        }
        return null;
    }

    public function batchSize(): int  { return 200; }
    public function chunkSize(): int  { return 200; }
    public function getImported(): int { return $this->imported; }
    public function getSkipped(): int  { return $this->skipped; }
}
