<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // ✅ GET semua notifikasi user
    public function index(Request $request)
    {
        $userId = $request->query('user_id');
        $tipe = $request->query('tipe');

        if (!$userId || !$tipe) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter tidak lengkap'
            ], 422);
        }

        $notifications = Notification::where('user_id', $userId)
            ->where('tipe_user', $tipe)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->title,
                    'body' => $notif->body,
                    'type' => $notif->type,
                    'data' => $notif->data,
                    'is_read' => $notif->is_read,
                    'created_at' => $notif->created_at->format('d-m-Y H:i'),
                    'timestamp' => $notif->created_at->timestamp,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    // ✅ GET jumlah notifikasi belum dibaca
    public function unreadCount(Request $request)
    {
        $userId = $request->query('user_id');
        $tipe = $request->query('tipe');

        if (!$userId || !$tipe) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter tidak lengkap'
            ], 422);
        }

        $count = Notification::where('user_id', $userId)
            ->where('tipe_user', $tipe)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    // ✅ Mark as read (single notification)
    public function markAsRead($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
        }

        $notification->is_read = true;
        $notification->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi ditandai telah dibaca'
        ]);
    }

    // ✅ Mark all as read
    public function markAllAsRead(Request $request)
    {
        $userId = $request->query('user_id');
        $tipe = $request->query('tipe');

        if (!$userId || !$tipe) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter tidak lengkap'
            ], 422);
        }

        Notification::where('user_id', $userId)
            ->where('tipe_user', $tipe)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Semua notifikasi ditandai telah dibaca'
        ]);
    }
}