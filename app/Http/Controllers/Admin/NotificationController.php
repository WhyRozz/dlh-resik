<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penarikan;
use App\Models\Laporan; // Sesuaikan dengan model laporan kamu
use App\Models\Penjemputan;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getCounts()
    {
        return response()->json([
            'penarikan' => Penarikan::where('status', 'diproses')->count(),
            'laporan'   => Laporan::where('status', 'diproses')->count(), // Sesuaikan status
            'penjemputan' => Penjemputan::where('status', 'diproses')->count(),
        ]);
    }

    public function recentPenarikan()
    {
        return Penarikan::with(['masyarakat', 'pns'])
            ->where('status', 'diproses')
            ->latest('tanggal_penarikan')
            ->take(5)
            ->get()
            ->map(function ($p) {
                return [
                    'nama' => $p->masyarakat->nama ?? $p->pns->nama ?? 'User',
                    'jumlah' => $p->jumlah_uang,
                    'waktu' => $p->tanggal_penarikan->diffForHumans(),
                ];
            });
    }

    public function recentLaporan()
    {
        return Laporan::where('status', 'Diproses') // ✅ Gunakan 'Diproses'
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($l) {
                return [
                    'lokasi' => $l->lokasi ?? 'Lokasi tidak diketahui',
                    'jenis' => $l->keterangan ? substr($l->keterangan, 0, 50) . '...' : '-',
                    'waktu' => $l->created_at->diffForHumans(),
                    'nama' => $l->nama ?? 'Unknown',
                ];
            });
    }
    public function recentPenjemputan()
    {
        return Penjemputan::where('status', 'diproses')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($p) {
                return [
                    'nama_admin' => $p->nama_admin ?? 'Unknown',
                    'berat'      => $p->berat . ' Kg',
                    'waktu'      => $p->created_at->diffForHumans(),
                    'lokasi'     => $p->lokasi ?? '-',
                ];
            });
    }
}
