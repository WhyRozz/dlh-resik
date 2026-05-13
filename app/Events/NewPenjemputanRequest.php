<?php

namespace App\Events;

use App\Models\Penjemputan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewPenjemputanRequest implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $penjemputan;

    public function __construct(Penjemputan $penjemputan)
    {
        $this->penjemputan = $penjemputan;
    }

    public function broadcastOn()
    {
        return new Channel('admin-notifications');
    }

    public function broadcastAs()
    {
        return 'new-penjemputan';
    }

    public function broadcastWith()
    {
        return [
            'id'         => $this->penjemputan->id,
            'nama_admin' => $this->penjemputan->nama_admin,
            'berat'      => $this->penjemputan->berat,
            'waktu'      => $this->penjemputan->created_at->diffForHumans(),
        ];
    }
}