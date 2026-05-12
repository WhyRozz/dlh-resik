<?php

namespace App\Events;

use App\Models\Penarikan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewWithdrawalRequest implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $penarikan;

    public function __construct(Penarikan $penarikan)
    {
        $this->penarikan = $penarikan;
    }

    public function broadcastOn()
    {
        // ✅ Channel harus sama dengan yang di listen di JS
        return new Channel('admin-notifications');
    }

    public function broadcastAs()
    {
        // ✅ Event name harus sama (tanpa titik di awal)
        return 'new-withdrawal';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->penarikan->id_penarikan,
            'nama' => $this->penarikan->masyarakat->nama ?? $this->penarikan->pns->nama ?? 'Unknown',
            'jumlah' => $this->penarikan->jumlah_uang,
            // ✅ Gunakan tanggal_penarikan
            'waktu' => $this->penarikan->tanggal_penarikan
                ? $this->penarikan->tanggal_penarikan->diffForHumans()
                : 'Baru saja',
        ];
    }
}
