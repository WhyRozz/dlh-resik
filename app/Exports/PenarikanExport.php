<?php

namespace App\Exports;

use App\Models\Penarikan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class PenarikanExport implements WithMultipleSheets
{
    protected $filter;

    public function __construct($filter = null)
    {
        $this->filter = $filter;
    }

    public function sheets(): array
    {
        $sheets = [];
        $tipePengguna = $this->filter['tipe_pengguna'] ?? 'semua';

        // ✅ Ambil admin yang login
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        $isSubAdminDesa = $admin && $admin->role === 'sub_admin_desa' && $admin->id_desa;

        // Jika filter = 'semua' atau 'masyarakat', buat sheet Masyarakat
        if ($tipePengguna === 'semua' || $tipePengguna === 'masyarakat') {
            $sheets[] = new MasyarakatPenarikanSheet($this->filter);
        }

        // Jika filter = 'semua' atau 'pns', buat sheet PNS per dinas
        if ($tipePengguna === 'semua' || $tipePengguna === 'pns') {
            // Ambil daftar dinas yang memiliki penarikan
            $dinasQuery = DB::table('penarikan')
                ->leftJoin('pns', 'penarikan.id_pns', '=', 'pns.id_pns')
                ->leftJoin('dinas', 'pns.id_dinas', '=', 'dinas.id_dinas')
                ->whereNotNull('penarikan.id_pns')
                ->select('dinas.id_dinas', 'dinas.nama_dinas', DB::raw('COUNT(*) as total'))
                ->groupBy('dinas.id_dinas', 'dinas.nama_dinas');

            // ✅ AUTO-FILTER UNTUK SUB ADMIN DESA
            if ($isSubAdminDesa) {
                $dinasQuery->where('pns.id_desa', $admin->id_desa);
            }

            // Apply filter dari parameter
            if (isset($this->filter['bulan'])) {
                $dinasQuery->whereMonth('penarikan.tanggal_penarikan', $this->filter['bulan']);
            }
            if (isset($this->filter['tahun'])) {
                $dinasQuery->whereYear('penarikan.tanggal_penarikan', $this->filter['tahun']);
            }
            if (isset($this->filter['status'])) {
                $dinasQuery->where('penarikan.status', $this->filter['status']);
            }
            if (isset($this->filter['dinas_id'])) {
                $dinasQuery->where('pns.id_dinas', $this->filter['dinas_id']);
            }

            $pnsByDinas = $dinasQuery->get();

            foreach ($pnsByDinas as $dinas) {
                if ($dinas->nama_dinas) {
                    $sheets[] = new PnsPenarikanSheet($dinas->nama_dinas, $this->filter);
                }
            }
        }

        // Fallback: kalau tidak ada sheet, buat sheet kosong
        if (empty($sheets)) {
            $sheets[] = new MasyarakatPenarikanSheet($this->filter);
        }

        return $sheets;
    }
}

// ========================================
// Sheet untuk Masyarakat
// ========================================
class MasyarakatPenarikanSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $filter;
    protected $no = 0;

    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    public function collection()
    {
        $query = Penarikan::with(['masyarakat.desa.kecamatan'])
            ->whereNotNull('id_masyarakat')
            ->orderBy('tanggal_penarikan', 'desc');

        // ✅ AUTO-FILTER UNTUK SUB ADMIN DESA
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if ($admin && $admin->role === 'sub_admin_desa' && $admin->id_desa) {
            $query->whereHas('masyarakat', function ($q) use ($admin) {
                $q->where('id_desa', $admin->id_desa);
            });
        }

        // Apply filters dari parameter
        if (isset($this->filter['bulan'])) {
            $query->whereMonth('tanggal_penarikan', $this->filter['bulan']);
        }
        if (isset($this->filter['tahun'])) {
            $query->whereYear('tanggal_penarikan', $this->filter['tahun']);
        }
        if (isset($this->filter['status'])) {
            $query->where('status', $this->filter['status']);
        }
        if (isset($this->filter['kecamatan_id'])) {
            $query->whereHas('masyarakat.desa', function ($q) {
                $q->where('id_kecamatan', $this->filter['kecamatan_id']);
            });
        }
        if (isset($this->filter['desa_id'])) {
            $query->whereHas('masyarakat', function ($q) {
                $q->where('id_desa', $this->filter['desa_id']);
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Anggota',
            'Pekerjaan',
            'Kecamatan',
            'Desa/Kelurahan',
            'Nama Penerima',
            'Jenis Layanan',
            'Nomor E-Wallet / Bank',
            'Jumlah Uang',
            'Tanggal Penarikan',
            'Status',
            'Alasan Penolakan'
        ];
    }

    public function map($penarikan): array
    {
        $this->no++;

        $namaUser = $penarikan->masyarakat->nama ?? 'Unknown';
        $kecamatan = $penarikan->masyarakat->desa->kecamatan->nama_kecamatan ?? '-';
        $desa = $penarikan->masyarakat->desa->nama_desa ?? '-';
        $namaPenerima = $penarikan->nama_penerima ?? '-';

        // ✅ PAKSA JADI TEKS DENGAN TANDA PETIK TUNGGAL DI DEPAN
        $nomorRekening = "'" . ($penarikan->nomor_ewallet ?? '-');

        return [
            $this->no,
            $namaUser,
            'Masyarakat',
            $kecamatan,
            $desa,
            $namaPenerima,
            $penarikan->jenis_layanan === 'bank'
                ? ($penarikan->nama_bank ?? '-')
                : ($penarikan->jenis_ewallet ?? '-'),
            $nomorRekening, // ✅ TANPA ARRAY, HANYA STRING SAJA
            'Rp ' . number_format($penarikan->jumlah_uang, 0, ',', '.'),
            Carbon::parse($penarikan->tanggal_penarikan)->format('d-m-Y H:i'),
            ucfirst($penarikan->status),
            $penarikan->alasan_penolakan ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setSelectedCell('A1');
        $sheet->freezePane('A2');

        $totalRows = $this->collection()->count();
        $lastRow = $totalRows + 2;
        $totalAmount = $this->collection()->sum('jumlah_uang');

        // Set lebar kolom default
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }

        // AUTO-SIZE
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
        $sheet->setCellValue("A{$lastRow}", 'TOTAL TRANSAKSI:');
        $sheet->setCellValue("C{$lastRow}", $totalRows . ' data');
        $sheet->mergeCells("D{$lastRow}:H{$lastRow}");
        $sheet->setCellValue("D{$lastRow}", 'TOTAL NOMINAL:');
        $sheet->setCellValue("I{$lastRow}", 'Rp ' . number_format($totalAmount, 0, ',', '.'));

        // ✅ PAKSA KOLM H SEBAGAI TEKS (TANDA PETIK TUNGGAL SUDAH DIHILANGKAN OLEH EXCEL)
        $sheet->getStyle('H2:H' . $lastRow)
            ->getNumberFormat()
            ->setFormatCode('@');

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

// ========================================
// Sheet untuk PNS per Dinas
// ========================================
class PnsPenarikanSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $dinasName;
    protected $filter;
    protected $no = 0;

    public function __construct($dinasName, $filter)
    {
        $this->dinasName = $dinasName;
        $this->filter = $filter;
    }

    public function collection()
    {
        // ✅ TAMBAHKAN 'pns.desa.kecamatan'
        $query = Penarikan::with(['pns.dinas', 'pns.desa.kecamatan'])
            ->whereNotNull('id_pns')
            ->orderBy('tanggal_penarikan', 'desc');

        // ✅ AUTO-FILTER UNTUK SUB ADMIN DESA
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if ($admin && $admin->role === 'sub_admin_desa' && $admin->id_desa) {
            $query->whereHas('pns', function ($q) use ($admin) {
                $q->where('id_desa', $admin->id_desa);
            });
        }

        // ✅ Filter by dinas name (jika sheet PNS per dinas)
        if ($this->dinasName) {
            $query->whereHas('pns.dinas', function ($q) {
                $q->where('nama_dinas', $this->dinasName);
            });
        }

        // Apply filters dari parameter
        if (isset($this->filter['bulan'])) {
            $query->whereMonth('tanggal_penarikan', $this->filter['bulan']);
        }
        if (isset($this->filter['tahun'])) {
            $query->whereYear('tanggal_penarikan', $this->filter['tahun']);
        }
        if (isset($this->filter['status'])) {
            $query->where('status', $this->filter['status']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Anggota',
            'Pekerjaan',
            'Dinas/Instansi',
            'Kecamatan',
            'Desa/Kelurahan',
            'Nama Penerima',
            'Jenis Layanan',
            'Nomor E-Wallet / Bank',
            'Jumlah Uang',
            'Tanggal Penarikan',
            'Status',
            'Alasan Penolakan'
        ];
    }

    public function map($penarikan): array
    {
        $this->no++;

        $namaUser = $penarikan->pns->nama ?? 'Unknown';
        $dinas = $penarikan->pns->dinas->nama_dinas ?? 'ASN/PNS';

        // AMBIL DATA KECAMATAN DAN DESA
        $kecamatan = $penarikan->pns->desa->kecamatan->nama_kecamatan ?? '-';
        $desa = $penarikan->pns->desa->nama_desa ?? '-';
        $namaPenerima = $penarikan->nama_penerima ?? '-';

        // ✅ PAKSA JADI TEKS DENGAN TANDA PETIK TUNGGAL DI DEPAN
        $nomorRekening = "'" . ($penarikan->nomor_ewallet ?? '-');

        return [
            $this->no,
            $namaUser,
            'ASN/PNS',
            $dinas,
            $kecamatan,
            $desa,
            $namaPenerima,
            $penarikan->jenis_layanan === 'bank'
                ? ($penarikan->nama_bank ?? '-')
                : ($penarikan->jenis_ewallet ?? '-'),
            $nomorRekening, // ✅ TANPA ARRAY, HANYA STRING SAJA
            'Rp ' . number_format($penarikan->jumlah_uang, 0, ',', '.'),
            Carbon::parse($penarikan->tanggal_penarikan)->format('d-m-Y H:i'),
            ucfirst($penarikan->status),
            $penarikan->alasan_penolakan ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setSelectedCell('A1');
        $sheet->freezePane('A2');

        $totalRows = $this->collection()->count();
        $lastRow = $totalRows + 2;
        $totalAmount = $this->collection()->sum('jumlah_uang');

        // Set lebar kolom default (A sampai M = 13 kolom)
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }

        // AUTO-SIZE
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->calculateColumnWidths();

        // Style header (A1 sampai M1)
        $sheet->getStyle('A1:M1')->applyFromArray([
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

        // Style total row (A sampai M)
        $sheet->getStyle("A{$lastRow}:M{$lastRow}")->applyFromArray([
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

        // Add total row (Disesuaikan dengan 13 kolom)
        $sheet->mergeCells("A{$lastRow}:B{$lastRow}");
        $sheet->setCellValue("A{$lastRow}", 'TOTAL TRANSAKSI:');
        $sheet->setCellValue("C{$lastRow}", $totalRows . ' data');

        // Merge dari Dinas(D) sampai Nomor E-Wallet(I) = 6 kolom
        $sheet->mergeCells("D{$lastRow}:I{$lastRow}");
        $sheet->setCellValue("D{$lastRow}", 'TOTAL NOMINAL:');
        // Nilai total ada di kolom J (Jumlah Uang)
        $sheet->setCellValue("J{$lastRow}", 'Rp ' . number_format($totalAmount, 0, ',', '.'));

        // ✅ PAKSA KOLM I SEBAGAI TEKS (TANDA PETIK TUNGGAL SUDAH DIHILANGKAN OLEH EXCEL)
        $sheet->getStyle('I2:I' . $lastRow)
            ->getNumberFormat()
            ->setFormatCode('@');

        // Style all cells border (A1 sampai M)
        $sheet->getStyle("A1:M{$lastRow}")->applyFromArray([
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