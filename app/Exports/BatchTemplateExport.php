<?php

namespace App\Exports;

use App\Models\Batch;
use App\Models\Section;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Collection;

class BatchTemplateExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected Batch $batch;
    protected array $sections;

    public function __construct(Batch $batch)
    {
        $this->batch    = $batch;
        $this->sections = $batch->sections()->orderBy('range_start')->get()->toArray();
    }

    public function title(): string
    {
        return 'كشف الطلاب';
    }

    /**
     * البيانات: نولد صفوف توضيحية لكل سكشن
     */
    public function collection(): Collection
    {
        $rows = collect();

        if (empty($this->sections)) {
            // لو مفيش سكاشن — صفوف فارغة للتعبئة
            for ($i = 1; $i <= 5; $i++) {
                $rows->push([
                    '',        // اسم الطالب
                    '',        // الرقم الجامعي
                    'سكشن 1', // السكشن (يملأه الأدمن)
                ]);
            }
        } else {
            // صف مثال لكل سكشن مع نطاقه
            foreach ($this->sections as $section) {
                $sectionLabel = $section['name'] ?? 'سكشن';
                $note = '';
                if ($section['range_start'] && $section['range_end']) {
                    $note = "من {$section['range_start']} إلى {$section['range_end']}";
                }
                // 3 صفوف فارغة لكل سكشن كأمثلة
                for ($i = 1; $i <= 3; $i++) {
                    $rows->push([
                        '',           // اسم الطالب
                        '',           // الرقم الجامعي
                        $sectionLabel,// اسم السكشن
                    ]);
                }
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'اسم الطالب',
            'الرقم الجامعي',
            'السكشن',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();
        $lastCol = 'C';

        // RTL
        $sheet->setRightToLeft(true);

        // Header row style
        $sheet->getStyle('A1:C1')->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
                'bold'  => true,
                'size'  => 12,
                'name'  => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Data rows
        if ($lastRow > 1) {
            $sheet->getStyle("A2:C{$lastRow}")->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'font' => ['name' => 'Calibri', 'size' => 11],
            ]);

            // Alternating row colors
            for ($row = 2; $row <= $lastRow; $row++) {
                $color = ($row % 2 === 0) ? 'F8F7FF' : 'FFFFFF';
                $sheet->getStyle("A{$row}:C{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($color);
            }
        }

        // Borders for all cells
        $sheet->getStyle("A1:C{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'E2E8F0'],
                ],
            ],
        ]);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);

        // Instructions row at the top
        $sheet->insertNewRowBefore(1, 2);
        $sheet->mergeCells('A1:C1');
        $sheet->setCellValue('A1', "📋 كشف طلاب الدفعة: {$this->batch->name}");
        $sheet->getStyle('A1')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E1B4B']],
            'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true, 'size' => 14, 'name' => 'Calibri'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(40);

        $sheet->mergeCells('A2:C2');
        $sheet->setCellValue('A2', "⚠️ ملاحظة: احذف الصفوف التوضيحية واملأ البيانات الحقيقية. عمود 'السكشن' يجب أن يطابق أسماء السكاشن المعرفة.");
        $sheet->getStyle('A2')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
            'font' => ['color' => ['rgb' => '92400E'], 'size' => 10, 'name' => 'Calibri'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(35);

        return [];
    }
}
