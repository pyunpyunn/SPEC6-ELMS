<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function feed(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->take(5)
            ->get()
            ->map(function (SystemNotification $notification): array {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'created_at' => $notification->created_at?->diffForHumans(),
                    'unread' => $notification->read_at === null,
                    'read_url' => route('notifications.read', $notification),
                ];
            });

        return response()->json([
            'unread_count' => $request->user()->notifications()->unreadActionable()->count(),
            'notifications' => $notifications,
        ]);
    }

    public function read(SystemNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return redirect($notification->action_url ?: route('home'));
    }
}
