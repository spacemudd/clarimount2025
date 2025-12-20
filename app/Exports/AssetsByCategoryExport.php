<?php

declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AssetsByCategoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $groupedAssets;

    public function __construct($groupedAssets)
    {
        $this->groupedAssets = $groupedAssets;
    }

    public function collection()
    {
        $data = collect();
        
        // Add summary data grouped by company and category
        foreach ($this->groupedAssets as $group) {
            $data->push([
                'company' => $group['company'],
                'category' => $group['category'],
                'count' => $group['count'],
            ]);
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [
            'الشركة / Company',
            'الفئة / Category',
            'العدد / Count',
        ];
    }

    public function map($row): array
    {
        return [
            $row['company'],
            $row['category'],
            $row['count'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}

