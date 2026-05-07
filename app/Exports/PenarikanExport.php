<?php

namespace App\Exports;

use App\Models\Penarikan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class PenarikanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $filter;

    public function __construct($filter = null)
    {
        $this->filter = $filter;
    }

    public function collection()
    {
        $query = Penarikan::with(['masyarakat', 'pns'])
            ->orderBy('tanggal_penarikan', 'desc');

        // Apply filters if exist
        if ($this->filter) {
            if (isset($this->filter['bulan'])) {
                $query->whereMonth('tanggal_penarikan', $this->filter['bulan']);
            }
            if (isset($this->filter['tahun'])) {
                $query->whereYear('tanggal_penarikan', $this->filter['tahun']);
            }
            if (isset($this->filter['status'])) {
                $query->where('status', $this->filter['status']);
            }
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Anggota',
            'Tanggal Penarikan',
            'Jumlah Uang',
            'E-Wallet',
            'Nomor E-Wallet',
            'Status',
            'Alasan Penolakan'
        ];
    }

    public function map($penarikan): array
    {
        // Get user name
        $namaUser = $penarikan->masyarakat->nama ?? $penarikan->pns->nama ?? 'Unknown';

        return [
            $penarikan->id_penarikan,
            $namaUser,
            Carbon::parse($penarikan->tanggal_penarikan)->format('d-m-Y H:i'),
            'Rp ' . number_format($penarikan->jumlah_uang, 0, ',', '.'),
            $penarikan->jenis_ewallet ?? '-',
            $penarikan->nomor_ewallet ?? '-',
            ucfirst($penarikan->status),
            $penarikan->alasan_penolakan ?? '-'
        ];
    }

public function styles(Worksheet $sheet)
{
    $totalRows = $this->collection()->count();
    $lastRow = $totalRows + 2;
    $totalAmount = $this->collection()->sum('jumlah_uang');

    // ✅ SET NILAI SEBELUM MERGE
    // Total Transaksi
    $sheet->setCellValue("A{$lastRow}", 'TOTAL TRANSAKSI: ' . $totalRows . ' data');
    
    // Total Nominal
    $sheet->setCellValue("C{$lastRow}", 'TOTAL NOMINAL: Rp ' . number_format($totalAmount, 0, ',', '.'));

    // ✅ MERGE CELLS
    $sheet->mergeCells("A{$lastRow}:B{$lastRow}");
    $sheet->mergeCells("C{$lastRow}:D{$lastRow}");

    return [
        1 => [
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '10B981']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ]
        ],
        $lastRow => [
            'font' => [
                'bold' => true,
                'size' => 11
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEF3C7']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ]
        ]
    ];
}

    public function title(): string
    {
        return 'Data Penarikan';
    }
}