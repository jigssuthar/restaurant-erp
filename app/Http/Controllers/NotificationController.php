<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $user->notifications()
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn (DatabaseNotification $notification) => [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'body' => $notification->data['body'] ?? '',
                    'url' => $notification->data['url'] ?? route('admin.dashboard'),
                    'read' => $notification->read_at !== null,
                    'created_at' => $notification->created_at?->diffForHumans(),
                ]),
        ]);
    }

    public function markRead(Request $request, string $notification)
    {
        $userNotification = $request->user()->notifications()->findOrFail($notification);
        $userNotification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
