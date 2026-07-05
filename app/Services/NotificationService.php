<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected FirebaseService $firebase;

    public function __construct()
    {
        $this->firebase = new FirebaseService();
    }

    /**
     * ✅ MULTI-DEVICE: Ambil semua FCM token admin
     * - Super Admin (id_desa = null) SELALU dapat
     * - Sub Admin Desa hanya dapat jika id_desa sesuai
     */
    private function getAdminTokens(?int $idDesa = null): Collection
    {
        $query = DB::table('admin_fcm_tokens')
            ->join('admin', 'admin_fcm_tokens.id_admin', '=', 'admin.id_admin')
            ->select('admin_fcm_tokens.*');

        if ($idDesa !== null) {
            // ✅ Filter: Super Admin + Sub Admin desa yang sesuai
            $query->where(function ($q) use ($idDesa) {
                $q->whereNull('admin.id_desa')        // Super Admin
                    ->orWhere('admin.id_desa', $idDesa); // Sub Admin desa ini
            });
        }
        // ✅ Jika $idDesa = null, TIDAK ADA FILTER → semua admin dapat
        // (Super Admin + semua Sub Admin)

        return $query->get();
    }

    /**
     * Function umum untuk mengirim notifikasi ke semua admin
     */
    /**
     * ✅ MULTI-DEVICE: Kirim notifikasi ke semua device admin
     */
    private function sendToAdmins(
        string $title,
        string $body,
        array $data,
        ?int $idDesa = null
    ): void {

        $tokens = $this->getAdminTokens($idDesa);

        foreach ($tokens as $tokenRow) {
            try {
                $this->firebase->sendNotification(
                    $tokenRow->fcm_token,
                    $title,
                    $body,
                    $data
                );
            } catch (\Exception $e) {
                // ✅ Hapus token yang invalid/expired
                if (
                    strpos($e->getMessage(), 'NOT_REGISTERED') !== false ||
                    strpos($e->getMessage(), 'InvalidRegistration') !== false
                ) {
                    DB::table('admin_fcm_tokens')
                        ->where('id', $tokenRow->id)
                        ->delete();
                }
                Log::error('FCM Multi-Device Error: ' . $e->getMessage());
            }
        }
    }

    /**
     * ==========================
     * LAPORAN SAMPAH
     * ==========================
     */
    public function sendReport(
        string $nama,
        string $lokasi,
        ?int $idDesa = null,
        int $idLaporan
    ): void {

        $title = "📢 Laporan Sampah Ilegal";

        $body = "{$nama} melaporkan adanya lokasi sampah ilegal di {$lokasi}.";

        $this->sendToAdmins(

            $title,

            $body,

            [
                "type" => "report",
                "url"  => "/admin/laporan",
                "id"   => (string) $idLaporan
            ],

            $idDesa

        );
    }

    /**
     * ==========================
     * PENARIKAN
     * ==========================
     */
    public function sendWithdrawal(
        string $nama,
        string $desa,
        ?int $idDesa = null,
        int $nominal,
        int $idPenarikan
    ): void {

        $title = "💸 Pengajuan Penarikan Baru";

        $body = "{$nama} mengajukan penarikan saldo sebesar Rp " .
            number_format($nominal, 0, ',', '.') . ".";

        $this->sendToAdmins(

            $title,

            $body,

            [
                "type" => "withdrawal",
                "url"  => "/admin/bank-sampah/penarikan",
                "id"   => (string) $idPenarikan
            ],

            $idDesa

        );
    }

    public function sendPickup(
        string $nama,
        string $desa,
        string $kecamatan,
        ?int $idDesa = null,
        int $idPenjemputan
    ): void {

        $title = "🚛 Pengajuan Penjemputan";

        $body = "{$nama} dari Bank Sampah Kecamatan {$kecamatan}, Desa {$desa} mengajukan penjemputan sampah.";

        $this->sendToAdmins(

            $title,

            $body,

            [
                "type" => "pickup",
                "url"  => "/admin/bank-sampah/penjemputan",
                "id"   => (string)$idPenjemputan
            ],

            $idDesa

        );
    }

    public function sendDeposit(
        string $nama,
        ?int $idDesa = null,
        float $berat,
        float $berat_asli,
        float $total_rupiah,
        int $idTransaksi
    ): void {

        $title = "♻️ Setoran Sampah Baru";

        $body = "Setoran {$berat} {$berat_asli} Kg dari {$nama} berhasil. Saldo bertambah Rp {$total_rupiah}!";

        $this->sendToAdmins(

            $title,

            $body,

            [
                "type" => "deposit",
                "url"  => "/admin/bank-sampah/setor-sampah",
                "id"   => (string)$idTransaksi
            ],

            $idDesa

        );
    }

    public function sendToUser(
        ?string $token,
        string $title,
        string $body,
        array $data = []
    ): void {

        if (!$token) {
            return;
        }

        $this->firebase->sendNotification(
            $token,
            $title,
            $body,
            $data
        );
    }


    // Mobile
    public function sendReportResult(
        ?string $token,
        string $status
    ): void {

        $title = "📢 Status Laporan";

        if ($status == "Diterima") {

            $body = "Laporan sampah Anda telah selesai diproses. Silakan buka aplikasi untuk melihat hasil penanganan laporan. Terima kasih atas partisipasi Anda dalam menjaga kebersihan lingkungan di Kota Nganjuk.";
        } elseif ($status == "Diproses") {

            $body = "Laporan sampah Anda sedang ditangani oleh petugas. Kami akan memberikan informasi setelah penanganan selesai.";
        } else {

            $body = "Maaf, laporan sampah Anda ditolak. Admin telah memberikan keterangan terkait laporan Anda. Silakan buka aplikasi untuk melihat detail balasan.";
        }

        $this->sendToUser(
            $token,
            $title,
            $body,
            [
                "type" => "report_result"
            ]
        );
    }


    public function sendWithdrawalResult(
        ?string $token,
        string $status,
        float $jumlah_uang
    ): void {

        $title = "💸 Status Penarikan";

        if ($status == "berhasil") {

            $body = "Pengajuan penarikan saldo Anda telah disetujui. Pencairan dana sebesar Rp "
                . number_format($jumlah_uang, 0, ',', '.')
                . " akan segera dilakukan.";
        } elseif ($status == "ditolak") {

            $body = "Maaf, pengajuan penarikan saldo Anda ditolak. Silakan hubungi admin untuk informasi lebih lanjut.";
        } else {

            return;
        }

        $this->sendToUser(
            $token,
            $title,
            $body,
            [
                "type" => "withdrawal_result"
            ]
        );
    }

    public function sendDepositResult(
        ?string $token,
        float $total_rupiah
    ): void {

        $this->sendToUser(
            $token,
            "♻️ Status Setoran",
            "Setoran sampah Anda telah disetujui. Saldo sebesar Rp "
                . number_format($total_rupiah, 0, ',', '.')
                . " berhasil ditambahkan ke akun Anda.",
            [
                "type" => "deposit_result"
            ]
        );
    }

    public function sendDepositRejected(
        ?string $token
    ): void {

        $this->sendToUser(
            $token,
            "♻️ Status Setoran",
            "Maaf, setoran sampah Anda ditolak. Silakan hubungi petugas untuk informasi lebih lanjut.",
            [
                "type" => "deposit_rejected"
            ]
        );
    }

    public function sendPickupResult(
        ?string $token,
        string $status
    ): void {

        $title = "🚛 Status Penjemputan";

        if ($status == "disetujui") {

            $body = "Pengajuan penjemputan sampah Anda telah disetujui oleh admin. Penjemputan akan segera dilakukan.";
        } elseif ($status == "ditolak") {

            $body = "Maaf, pengajuan penjemputan sampah Anda ditolak. Silakan hubungi admin untuk informasi lebih lanjut.";
        } else {

            return;
        }

        $this->sendToUser(
            $token,
            $title,
            $body,
            [
                "type" => "pickup_result"
            ]
        );
    }
}
