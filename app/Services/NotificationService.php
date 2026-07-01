<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Support\Collection;

class NotificationService
{
    protected FirebaseService $firebase;

    public function __construct()
    {
        $this->firebase = new FirebaseService();
    }

    /**
     * Ambil semua admin yang akan menerima notifikasi
     * - Super Admin (id_desa = null)
     * - Sub Admin Desa sesuai wilayah
     */
    private function getAdminTokens(?int $idDesa = null): Collection
    {
        $query = Admin::whereNotNull('fcm_token');

        if ($idDesa !== null) {

            $query->where(function ($q) use ($idDesa) {

                $q->whereNull('id_desa')
                    ->orWhere('id_desa', $idDesa);
            });
        }

        return $query->get();
    }

    /**
     * Function umum untuk mengirim notifikasi ke semua admin
     */
    private function sendToAdmins(
        string $title,
        string $body,
        array $data,
        ?int $idDesa = null
    ): void {

        $admins = $this->getAdminTokens($idDesa);

        foreach ($admins as $admin) {

            $this->firebase->sendNotification(

                $admin->fcm_token,

                $title,

                $body,

                $data

            );
        }
    }

    /**
     * ==========================
     * LAPORAN SAMPAH
     * ==========================
     */
    public function sendReport(
        string $nama,
        string $desa,
        int $idDesa,
        int $idLaporan
    ): void {

        $title = "📢 Laporan Sampah Baru";

        $body = "{$nama} mengirim laporan sampah dari {$desa}.";

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
        int $idDesa,
        int $nominal,
        int $idPenarikan
    ): void {

        $title = "💰 Pengajuan Penarikan Baru";

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
        int $idDesa,
        int $idPenjemputan
    ): void {

        $title = "🚛 Permintaan Penjemputan";

        $body = "{$nama} meminta penjemputan sampah.";

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
        string $desa,
        int $idDesa,
        float $berat,
        int $idTransaksi
    ): void {

        $title = "♻️ Setoran Sampah Baru";

        $body = "{$nama} melakukan setor sampah {$berat} Kg.";

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

        $title = "Status Laporan";

        $body = $status == "selesai"
            ? "Laporan Anda telah selesai diproses."
            : "Laporan Anda sedang diproses.";

        $this->sendToUser($token, $title, $body, [
            "type" => "report_result"
        ]);
    }

    public function sendWithdrawalResult(
        ?string $token,
        string $status
    ): void {

        $title = "Status Penarikan";

        $body = $status == "disetujui"
            ? "Penarikan Anda telah disetujui."
            : "Penarikan Anda ditolak.";

        $this->sendToUser($token, $title, $body, [
            "type" => "withdrawal_result"
        ]);
    }

    public function sendDepositResult(
        ?string $token
    ): void {

        $this->sendToUser(
            $token,
            "Setoran Dikonfirmasi",
            "Setoran sampah Anda telah dikonfirmasi.",
            [
                "type" => "deposit_result"
            ]
        );
    }

    public function sendPickupResult(
        ?string $token,
        string $status
    ): void {

        $title = "Status Penjemputan";

        $body = $status == "disetujui"
            ? "Permintaan penjemputan disetujui."
            : "Permintaan penjemputan ditolak.";

        $this->sendToUser($token, $title, $body, [
            "type" => "pickup_result"
        ]);
    }
}
