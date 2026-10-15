<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $notifications = $user->notifications()->latest()->take(3)->get();

        $user->unreadNotifications->markAsRead();

        return response()->json([
            'unread_count' => 0,
            'notifications' => $notifications->map(fn($notification) => [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? '',
                'message' => $notification->data['message'] ?? '',
                'url' => $notification->data['url'] ?? null,
                'read' => $notification->read_at !== null,
                'created_at' => $notification->created_at->diffForHumans(),
            ]),
        ]);
    }
}
