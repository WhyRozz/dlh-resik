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

        // Get all PNS grouped by dinas
        $pnsByDinas = DB::table('pns')
            ->leftJoin('dinas', 'pns.id_dinas', '=', 'dinas.id_dinas')
            ->select('dinas.nama_dinas', DB::raw('COUNT(*) as total'))
            ->groupBy('dinas.id_dinas', 'dinas.nama_dinas')
            ->get();

        // Create sheet for each dinas
        foreach ($pnsByDinas as $dinas) {
            if ($dinas->nama_dinas) {
                $sheets[] = new DinasSheet($dinas->nama_dinas, $this->filter);
            }
        }

        // Create sheet for Masyarakat
        $sheets[] = new MasyarakatSheet($this->filter);

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
                'dinas.nama_dinas',
                'pns.created_at'
            )
            ->where('dinas.nama_dinas', $this->dinasName)
            ->orderBy('pns.created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Email',
            'Jenis Pengguna',
            'No Telepon',
            'Jenis Kelamin',
            'Dinas/Instansi',
            'Tanggal Bergabung'
        ];
    }

    public function map($user): array
    {
        return [
            $user->nama,
            $user->email,
            $user->jenis_pengguna,
            $user->no_telepon ?? '-',
            $user->jenis_kelamin ?? '-',
            $user->nama_dinas ?? '-',
            \Carbon\Carbon::parse($user->created_at)->format('d-m-Y H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $totalRows = $this->collection()->count();

        // Add total row at the end
        $lastRow = $totalRows + 2; // +2 for heading and row 1

        $sheet->setCellValue("A{$lastRow}", 'TOTAL PENGguna:');
        $sheet->setCellValue("B{$lastRow}", $totalRows);
        $sheet->mergeCells("A{$lastRow}:B{$lastRow}");

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2E8B57']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']]
            ],
            $lastRow => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFF00']
                ]
            ]
        ];
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
                DB::raw('NULL as no_telepon'),
                DB::raw('NULL as jenis_kelamin'),
                DB::raw("'Masyarakat Umum' as nama_dinas"),
                'masyarakat.created_at'
            )
            ->orderBy('masyarakat.created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Email',
            'Jenis Pengguna',
            'No Telepon',
            'Jenis Kelamin',
            'Dinas/Instansi',
            'Tanggal Bergabung'
        ];
    }

    public function map($user): array
    {
        return [
            $user->nama,
            $user->email,
            $user->jenis_pengguna,
            $user->no_telepon ?? '-',
            $user->jenis_kelamin ?? '-',
            $user->nama_dinas,
            \Carbon\Carbon::parse($user->created_at)->format('d-m-Y H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $totalRows = $this->collection()->count();
        $lastRow = $totalRows + 2;

        $sheet->setCellValue("A{$lastRow}", 'TOTAL PENGGUNA:');
        $sheet->setCellValue("B{$lastRow}", $totalRows);
        $sheet->mergeCells("A{$lastRow}:B{$lastRow}");

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2E8B57']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']]
            ],
            $lastRow => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFF00']
                ]
            ]
        ];
    }

    public function title(): string
    {
        return 'Masyarakat';
    }
}
