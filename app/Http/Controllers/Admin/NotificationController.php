<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penarikan;
use App\Models\Laporan;
use App\Models\Penjemputan;
use App\Models\Petugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    private function getLoggedInAdmin()
    {
        return Auth::guard('admin')->user();
    }

    private function isSubAdminDesa($admin)
    {
        return $admin && $admin->role === 'sub_admin_desa' && $admin->id_desa;
    }

    private function filterByDesa($query, $admin)
    {
        if ($this->isSubAdminDesa($admin)) {
            $idDesa = $admin->id_desa;
            $query->where(function ($q) use ($idDesa) {
                $q->whereHas('masyarakat.desa', function ($sub) use ($idDesa) {
                    $sub->where('id_desa', $idDesa);
                })->orWhereHas('pns.desa', function ($sub) use ($idDesa) {
                    $sub->where('id_desa', $idDesa);
                });
            });
        }
        return $query;
    }

    /**
     * ✅ HELPER BARU: Filter penjemputan berdasarkan id_petugas yang level-nya bank_sampah_{id_desa}
     */
    private function filterPenjemputanByDesa($query, $admin)
    {
        if ($this->isSubAdminDesa($admin)) {
            $idDesa = $admin->id_desa;
            $query->where('id_petugas', function ($q) use ($idDesa) {
                $q->select('id_petugas')
                    ->from('petugas')
                    ->where('level', 'bank_sampah_' . $idDesa);
            });
        }
        return $query;
    }

    /**
     * Get counts untuk badge notifikasi
     */
    public function getCounts()
    {
        $admin = $this->getLoggedInAdmin();

        // ✅ Query Penarikan
        $penarikanQuery = Penarikan::where('status', 'diproses');
        $penarikanQuery = $this->filterByDesa($penarikanQuery, $admin);
        $penarikanCount = $penarikanQuery->count();

        // ✅ Query Laporan
        $laporanQuery = Laporan::where('status', 'Diproses');
        if ($this->isSubAdminDesa($admin)) {
            $idDesa = $admin->id_desa;
            $laporanQuery->where(function ($q) use ($idDesa) {
                $q->whereHas('masyarakat.desa', function ($sub) use ($idDesa) {
                    $sub->where('id_desa', $idDesa);
                })->orWhereHas('pns.desa', function ($sub) use ($idDesa) {
                    $sub->where('id_desa', $idDesa);
                });
            });
        }
        $laporanCount = $laporanQuery->count();

        // ✅ Query Penjemputan - DIPERBAIKI!
        $penjemputanQuery = Penjemputan::where('status', 'diproses');
        $penjemputanQuery = $this->filterPenjemputanByDesa($penjemputanQuery, $admin);
        $penjemputanCount = $penjemputanQuery->count();

        return response()->json([
            'penarikan'   => $penarikanCount,
            'laporan'     => $laporanCount,
            'penjemputan' => $penjemputanCount,
        ]);
    }

    /**
     * Recent penarikan untuk modal notifikasi
     */
    public function recentPenarikan()
    {
        $admin = $this->getLoggedInAdmin();

        $query = Penarikan::with(['masyarakat', 'pns'])
            ->where('status', 'diproses')
            ->latest('tanggal_penarikan')
            ->take(5);

        $query = $this->filterByDesa($query, $admin);

        return $query->get()->map(function ($p) {
            return [
                'nama'   => $p->masyarakat->nama ?? $p->pns->nama ?? 'User',
                'jumlah' => $p->jumlah_uang,
                'waktu'  => $p->tanggal_penarikan->diffForHumans(),
            ];
        });
    }

    /**
     * Recent laporan untuk modal notifikasi
     */
    public function recentLaporan()
    {
        $admin = $this->getLoggedInAdmin();

        $query = Laporan::where('status', 'Diproses')
            ->latest()
            ->take(5);

        if ($this->isSubAdminDesa($admin)) {
            $idDesa = $admin->id_desa;
            $query->where(function ($q) use ($idDesa) {
                $q->whereHas('masyarakat.desa', function ($sub) use ($idDesa) {
                    $sub->where('id_desa', $idDesa);
                })->orWhereHas('pns.desa', function ($sub) use ($idDesa) {
                    $sub->where('id_desa', $idDesa);
                });
            });
        }

        return $query->get()->map(function ($l) {
            return [
                'lokasi' => $l->lokasi ?? 'Lokasi tidak diketahui',
                'jenis'  => $l->keterangan ? substr($l->keterangan, 0, 50) . '...' : '-',
                'waktu'  => $l->created_at->diffForHumans(),
                'nama'   => $l->nama ?? 'Unknown',
            ];
        });
    }

    /**
     * Recent penjemputan untuk modal notifikasi - DIPERBAIKI!
     */
    public function recentPenjemputan()
    {
        $admin = $this->getLoggedInAdmin();

        $query = Penjemputan::with('petugas')
            ->where('status', 'diproses')
            ->latest('waktu')
            ->take(5);

        // ✅ DIPERBAIKI: Filter berdasarkan id_petugas yang level-nya bank_sampah_{id_desa}
        $query = $this->filterPenjemputanByDesa($query, $admin);

        return $query->get()->map(function ($p) {
            return [
                'nama_admin' => $p->petugas->nama_lengkap ?? $p->nama_admin ?? 'Unknown',
                'berat'      => $p->berat . ' Kg',
                'waktu'      => $p->waktu->diffForHumans(),
                'lokasi'     => $p->lokasi ?? '-',
            ];
        });
    }
}