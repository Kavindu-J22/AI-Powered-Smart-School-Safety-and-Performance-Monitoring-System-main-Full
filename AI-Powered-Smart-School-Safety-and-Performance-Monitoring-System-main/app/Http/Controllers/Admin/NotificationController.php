<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $unreadOnly = $request->get('unread_only', false);

        $query = Notification::orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->unread();
        }

        $notifications = $query->limit($limit)->get();

        // Transform notifications for frontend
        $notificationsData = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'entity_type' => $notification->entity_type,
                'entity_id' => $notification->entity_id,
                'user_name' => $notification->user_name,
                'is_read' => $notification->is_read,
                'time_ago' => $notification->time_ago,
                'icon' => $notification->icon,
                'color' => $notification->color,
                'created_at' => $notification->created_at->toISOString(),
            ];
        });

        $unreadCount = Notification::unread()->count();

        return response()->json([
            'notifications' => $notificationsData,
            'unread_count' => $unreadCount,
            'total_count' => Notification::count(),
        ]);
    }

    public function markAsRead(Request $request)
    {
        $notificationIds = $request->input('notification_ids', []);

        if (empty($notificationIds)) {
            return response()->json(['message' => 'No notification IDs provided'], 400);
        }

        $updated = Notification::whereIn('id', $notificationIds)
            ->update(['is_read' => true]);

        return response()->json([
            'message' => 'Notifications marked as read',
            'updated_count' => $updated,
        ]);
    }

    public function markAllAsRead()
    {
        $updated = Notification::unread()->update(['is_read' => true]);

        return response()->json([
            'message' => 'All notifications marked as read',
            'updated_count' => $updated,
        ]);
    }

    public function getUnreadCount()
    {
        $unreadCount = Notification::unread()->count();

        return response()->json([
            'unread_count' => $unreadCount,
        ]);
    }

    public function destroy($id)
    {
        $notification = Notification::find($id);

        if (! $notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted']);
    }
}
