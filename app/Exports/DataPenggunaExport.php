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
    protected $kecamatanId;
    protected $desaId;
    protected $dinasId;
    protected $search;

    public function __construct($filter, $kecamatanId = '', $desaId = '', $dinasId = '', $search = '')
    {
        $this->filter = $filter;
        $this->kecamatanId = $kecamatanId;
        $this->desaId = $desaId;
        $this->dinasId = $dinasId;
        $this->search = $search;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Jika filter = 'all' atau 'asn', buat sheet PNS per dinas
        if ($this->filter === 'all' || $this->filter === 'asn') {
            $dinasQuery = DB::table('pns')
                ->leftJoin('dinas', 'pns.id_dinas', '=', 'dinas.id_dinas')
                ->select('dinas.id_dinas', 'dinas.nama_dinas', DB::raw('COUNT(*) as total'))
                ->groupBy('dinas.id_dinas', 'dinas.nama_dinas');

            // Filter by dinas_id jika ada
            if ($this->dinasId) {
                $dinasQuery->where('pns.id_dinas', $this->dinasId);
            }

            // Filter by search jika ada
            if ($this->search) {
                $dinasQuery->where('pns.nama', 'LIKE', "%{$this->search}%");
            }

            $pnsByDinas = $dinasQuery->get();

            foreach ($pnsByDinas as $dinas) {
                if ($dinas->nama_dinas) {
                    $sheets[] = new DinasSheet(
                        $dinas->nama_dinas,
                        $this->filter,
                        $this->search
                    );
                }
            }
        }

        // Jika filter = 'all' atau 'masyarakat', buat sheet Masyarakat
        if ($this->filter === 'all' || $this->filter === 'masyarakat') {
            $sheets[] = new MasyarakatSheet(
                $this->filter,
                $this->kecamatanId,
                $this->desaId,
                $this->search
            );
        }

        // Fallback: kalau tidak ada sheet, buat minimal 1 sheet
        if (empty($sheets)) {
            $sheets[] = new MasyarakatSheet($this->filter, $this->kecamatanId, $this->desaId, $this->search);
        }

        return $sheets;
    }
}

// ========================================
// Sheet untuk masing-masing Dinas (PNS)
// ========================================
class DinasSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $dinasName;
    protected $filter;
    protected $search;
    protected $no = 0;

    public function __construct($dinasName, $filter, $search = '')
    {
        $this->dinasName = $dinasName;
        $this->filter = $filter;
        $this->search = $search;
    }

    public function collection()
    {
        $query = DB::table('pns')
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
                'pns.barcode_id',
                'pns.saldo',
                'pns.created_at'
            )
            ->where('dinas.nama_dinas', $this->dinasName);

        // Filter by search jika ada
        if ($this->search) {
            $query->where('pns.nama', 'LIKE', "%{$this->search}%");
        }

        return $query->orderBy('pns.created_at', 'desc')->get();
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
            'Barcode ID',
            'Saldo',
            'Tanggal Bergabung'
        ];
    }

    public function map($user): array
    {
        $this->no++;

        return [
            $this->no,
            $user->nama,
            $user->email,
            $user->jenis_pengguna,
            $user->no_telepon ?? '-',
            $user->jenis_kelamin ?? '-',
            $user->tanggal_lahir ? \Carbon\Carbon::parse($user->tanggal_lahir)->format('d-m-Y') : '-',
            $user->nama_dinas ?? '-',
            $user->kode_anggota ?? '-',
            $user->barcode_id ?? '-',
            'Rp ' . number_format($user->saldo, 0, ',', '.'),
            \Carbon\Carbon::parse($user->created_at)->format('d-m-Y H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // ✅ PENTING: Set view ke A1 dan freeze header
        $sheet->setSelectedCell('A1');
        $sheet->freezePane('A2'); // Freeze baris header
        
        // ✅ Set lebar kolom default SEBELUM auto-size
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }

        $totalRows = $this->collection()->count();
        $lastRow = $totalRows + 2;

        // AUTO-SIZE semua kolom (setelah set width default)
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->calculateColumnWidths();

        // Style header
        $sheet->getStyle('A1:L1')->applyFromArray([
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
        $sheet->getStyle("A{$lastRow}:L{$lastRow}")->applyFromArray([
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
        $sheet->getStyle("A1:L{$lastRow}")->applyFromArray([
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
        return substr($this->dinasName, 0, 31);
    }
}

// ========================================
// Sheet untuk Masyarakat
// ========================================
class MasyarakatSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $filter;
    protected $kecamatanId;
    protected $desaId;
    protected $search;
    protected $no = 0; 

    public function __construct($filter, $kecamatanId = '', $desaId = '', $search = '')
    {
        $this->filter = $filter;
        $this->kecamatanId = $kecamatanId;
        $this->desaId = $desaId;
        $this->search = $search;
    }

    public function collection()
    {
        $query = DB::table('masyarakat')
            ->leftJoin('desa', 'masyarakat.id_desa', '=', 'desa.id_desa')
            ->leftJoin('kecamatan', 'desa.id_kecamatan', '=', 'kecamatan.id_kecamatan')
            ->select(
                'masyarakat.nama',
                'masyarakat.email',
                DB::raw("'Masyarakat' as jenis_pengguna"),
                'masyarakat.no_telepon',
                'masyarakat.jenis_kelamin',
                'masyarakat.tanggal_lahir',
                'kecamatan.nama_kecamatan',
                'desa.nama_desa',
                'masyarakat.barcode_id',
                'masyarakat.saldo',
                'masyarakat.created_at'
            );

        // Filter by kecamatan
        if ($this->kecamatanId) {
            $query->where('desa.id_kecamatan', $this->kecamatanId);
        }

        // Filter by desa
        if ($this->desaId) {
            $query->where('masyarakat.id_desa', $this->desaId);
        }

        // Filter by search
        if ($this->search) {
            $query->where('masyarakat.nama', 'LIKE', "%{$this->search}%");
        }

        return $query->orderBy('masyarakat.created_at', 'desc')->get();
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
            'Kecamatan',
            'Desa/Kelurahan',
            'Barcode ID',
            'Saldo',
            'Tanggal Bergabung'
        ];
    }

    public function map($user): array
    {
        $this->no++;

        return [
            $this->no,
            $user->nama,
            $user->email,
            $user->jenis_pengguna,
            $user->no_telepon ?? '-',
            $user->jenis_kelamin ?? '-',
            $user->tanggal_lahir ? \Carbon\Carbon::parse($user->tanggal_lahir)->format('d-m-Y') : '-',
            $user->nama_kecamatan ?? '-',
            $user->nama_desa ?? '-',
            $user->barcode_id ?? '-',
            'Rp ' . number_format($user->saldo, 0, ',', '.'),
            \Carbon\Carbon::parse($user->created_at)->format('d-m-Y H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // ✅ PENTING: Set view ke A1 dan freeze header
        $sheet->setSelectedCell('A1');
        $sheet->freezePane('A2'); // Freeze baris header
        
        // ✅ Set lebar kolom default SEBELUM auto-size
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }

        $totalRows = $this->collection()->count();
        $lastRow = $totalRows + 2;

        // AUTO-SIZE semua kolom (setelah set width default)
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->calculateColumnWidths();

        // Style header
        $sheet->getStyle('A1:L1')->applyFromArray([
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
        $sheet->getStyle("A{$lastRow}:L{$lastRow}")->applyFromArray([
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
        $sheet->getStyle("A1:L{$lastRow}")->applyFromArray([
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