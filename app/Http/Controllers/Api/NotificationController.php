<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $notifications = AppNotification::where('owner_id', $request->user()->workspaceOwnerId())
            ->with('user:id,name')
            ->latest()
            ->limit(30)
            ->get()
            ->map->toPublicArray();

        return response()->json(['ok' => true, 'notifications' => $notifications]);
    }

    public function read(Request $request, AppNotification $notification)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($notification->owner_id === $request->user()->workspaceOwnerId(), 403);
        $notification->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
