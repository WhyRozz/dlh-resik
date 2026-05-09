<?php

namespace App\Exports;

use App\Models\Masyarakat;
use App\Models\Pns;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DataPenggunaExport implements WithMultipleSheets
{
    protected $filter;

    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Jika filter = 'all' atau 'asn', buat sheet PNS per dinas
        if ($this->filter === 'all' || $this->filter === 'asn') {
            $pnsByDinas = DB::table('pns')
                ->leftJoin('dinas', 'pns.id_dinas', '=', 'dinas.id_dinas')
                ->select('dinas.nama_dinas', DB::raw('COUNT(*) as total'))
                ->groupBy('dinas.id_dinas', 'dinas.nama_dinas')
                ->get();

            foreach ($pnsByDinas as $dinas) {
                if ($dinas->nama_dinas) {
                    $sheets[] = new DinasSheet($dinas->nama_dinas, $this->filter);
                }
            }
        }

        // Jika filter = 'all' atau 'masyarakat', buat sheet Masyarakat
        if ($this->filter === 'all' || $this->filter === 'masyarakat') {
            $sheets[] = new MasyarakatSheet($this->filter);
        }

        // Fallback: kalau tidak ada sheet, buat minimal 1 sheet
        if (empty($sheets)) {
            $sheets[] = new MasyarakatSheet($this->filter);
        }

        return $sheets;
    }
}

// Sheet untuk masing-masing Dinas
class DinasSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $dinasName;
    protected $filter;

    public function __construct($dinasName, $filter)
    {
        $this->dinasName = $dinasName;
        $this->filter = $filter;
    }

    public function collection()
    {
        return DB::table('pns')
            ->leftJoin('dinas', 'pns.id_dinas', '=', 'dinas.id_dinas')
            ->select(
                'pns.nama',
                'pns.email',
                DB::raw("'PNS' as jenis_pengguna"),
                'pns.no_telepon',
                'pns.jenis_kelamin',
                'pns.tanggal_lahir',
                'dinas.nama_dinas',
                'pns.kode_anggota',
                'pns.saldo',
                'pns.created_at'
            )
            ->where('dinas.nama_dinas', $this->dinasName)
            ->orderBy('pns.created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'Email',
            'Jenis Pengguna',
            'No Telepon',
            'Jenis Kelamin',
            'Tanggal Lahir',
            'Dinas/Instansi',
            'Kode Anggota',
            'Saldo',
            'Tanggal Bergabung'
        ];
    }

    public function map($user): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $user->nama,
            $user->email,
            $user->jenis_pengguna,
            $user->no_telepon ?? '-',
            $user->jenis_kelamin ?? '-',
            $user->tanggal_lahir ? \Carbon\Carbon::parse($user->tanggal_lahir)->format('d-m-Y') : '-',
            $user->nama_dinas ?? '-',
            $user->kode_anggota ?? '-',
            'Rp ' . number_format($user->saldo, 0, ',', '.'),
            \Carbon\Carbon::parse($user->created_at)->format('d-m-Y H:i')
        ];
    }

    public function styles(Worksheet $sheet)
{
    $totalRows = $this->collection()->count();
    $lastRow = $totalRows + 2;

    // AUTO-SIZE semua kolom (otomatis menyesuaikan isi)
    foreach (range('A', 'K') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // PAKSA Excel hitung lebar kolom
    $sheet->calculateColumnWidths();

    // Style header
    $sheet->getStyle('A1:K1')->applyFromArray([
        'font' => [
            'bold' => true,
            'size' => 12,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => '2E8B57']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ]
    ]);

    // Style total row
    $sheet->getStyle("A{$lastRow}:K{$lastRow}")->applyFromArray([
        'font' => [
            'bold' => true,
            'size' => 12
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FFFF00']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER
        ]
    ]);

    // Add total row
    $sheet->mergeCells("A{$lastRow}:B{$lastRow}");
    $sheet->setCellValue("A{$lastRow}", 'TOTAL PENGGUNA:');
    $sheet->setCellValue("C{$lastRow}", $totalRows);

    // Style all cells border
    $sheet->getStyle("A1:K{$lastRow}")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER
        ]
    ]);

    return [];
}

    public function title(): string
    {
        // Sheet title max 31 characters
        $title = substr($this->dinasName, 0, 31);
        return $title;
    }
}

// Sheet untuk Masyarakat
class MasyarakatSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $filter;

    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    public function collection()
    {
        return DB::table('masyarakat')
            ->select(
                'masyarakat.nama',
                'masyarakat.email',
                DB::raw("'Masyarakat' as jenis_pengguna"),
                'masyarakat.no_telepon',
                'masyarakat.jenis_kelamin',
                'masyarakat.tanggal_lahir',
                DB::raw("'Masyarakat Umum' as nama_dinas"),
                'masyarakat.barcode_id as kode_anggota',
                'masyarakat.saldo',
                'masyarakat.created_at'
            )
            ->orderBy('masyarakat.created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'Email',
            'Jenis Pengguna',
            'No Telepon',
            'Jenis Kelamin',
            'Tanggal Lahir',
            'Dinas/Instansi',
            'Kode Anggota',
            'Saldo',
            'Tanggal Bergabung'
        ];
    }

    public function map($user): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $user->nama,
            $user->email,
            $user->jenis_pengguna,
            $user->no_telepon ?? '-',
            $user->jenis_kelamin ?? '-',
            $user->tanggal_lahir ? \Carbon\Carbon::parse($user->tanggal_lahir)->format('d-m-Y') : '-',
            $user->nama_dinas,
            $user->kode_anggota ?? '-',
            'Rp ' . number_format($user->saldo, 0, ',', '.'),
            \Carbon\Carbon::parse($user->created_at)->format('d-m-Y H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $totalRows = $this->collection()->count();
        $lastRow = $totalRows + 2;

        // AUTO-SIZE semua kolom (otomatis menyesuaikan isi)
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // PAKSA Excel hitung lebar kolom (INI KUNCINYA!)
        $sheet->calculateColumnWidths();
        
        // Style header
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2E8B57']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Style total row
        $sheet->getStyle("A{$lastRow}:K{$lastRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF00']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);

        // Add total row
        $sheet->mergeCells("A{$lastRow}:B{$lastRow}");
        $sheet->setCellValue("A{$lastRow}", 'TOTAL PENGGUNA:');
        $sheet->setCellValue("C{$lastRow}", $totalRows);

        // Style all cells border
        $sheet->getStyle("A1:K{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        return [];
    }

    public function title(): string
    {
        return 'Masyarakat';
    }
}