<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * عرض صفحة الإشعارات
     */
    public function index(Request $request)
    {
        $notifications = Auth::user()
            ->notifications()
            ->when($request->type, fn($q) => $q->where('type', 'LIKE', "%{$request->type}%"))
            ->when($request->status === 'unread', fn($q) => $q->whereNull('read_at'))
            ->when($request->status === 'read', fn($q) => $q->whereNotNull('read_at'))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('core::notifications.index', compact('notifications'));
    }

    /**
     * الحصول على آخر الإشعارات (للـ Bell)
     */
    public function latest(Request $request): JsonResponse
    {
        // التحقق من المصادقة
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthenticated',
                'notifications' => [],
                'unread_count' => 0,
            ], 401);
        }

        try {
            $limit = $request->input('limit', 10);
            // الحد الأقصى 50 تنبيه
            $limit = min($limit, 50);

            $notifications = Auth::user()
                ->notifications()
                ->latest()
                ->limit($limit)
                ->get()
                ->map(function ($notification) {
                    $avatar = $notification->data['avatar'] ?? null;
                    $userName = $notification->data['user_name'] ?? $notification->data['title'] ?? 'System';

                    if ($avatar && !str_starts_with($avatar, 'http')) {
                        $avatar = asset($avatar);
                    }

                    // Get initials (using mb_substr for UTF-8 safety)
                    $initials = '';
                    if ($userName) {
                        $nameParts = explode(' ', trim($userName));
                        $initials = (count($nameParts) >= 2)
                            ? mb_strtoupper(mb_substr($nameParts[0], 0, 1) . mb_substr($nameParts[count($nameParts) - 1], 0, 1))
                            : mb_strtoupper(mb_substr($userName, 0, 2));
                    }

                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'title' => $notification->data['title'] ?? 'System Notification',
                        'message' => $notification->data['message'] ?? '',
                        'priority' => $notification->data['priority'] ?? 'info',
                        'avatar' => $avatar,
                        'initials' => $initials,
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at->toIso8601String(),
                        'created_at_human' => $notification->created_at->diffForHumans(),
                        'action_url' => (!empty($notification->data['action_url']) && $notification->data['action_url'] !== '#')
                            ? $notification->data['action_url']
                            : '#',
                    ];
                });

            // ✅ Optimized counter check
            $user = Auth::user();
            $unreadCount = (int) $user->unread_notifications_count;

            // Fallback & Periodic Sync: If counter is 0 but records exist, or randomly 1 in 10 times
            if (($unreadCount === 0 && $user->unreadNotifications()->exists()) || rand(1, 10) === 1) {
                $unreadCount = $user->unreadNotifications()->count();
                $user->update(['unread_notifications_count' => $unreadCount]);
            }

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ]);

        } catch (\Exception $e) {
            Log::error('Notifications latest error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load notifications',
                'notifications' => [],
                'unread_count' => 0,
            ], 500);
        }
    }

    /**
     * تحديد إشعار كمقروء
     */
    public function markAsRead(string $id): JsonResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        if ($notification->read_at === null) {
            $notification->markAsRead();
            // Counter decremented automatically via Observer updated method
        }

        return response()->json([
            'success' => true,
            'unread_count' => Auth::user()->unread_notifications_count
        ]);
    }

    /**
     * تحديد جميع الإشعارات كمقروءة
     */
    public function markAllAsRead(): JsonResponse
    {
        $unreadNotifications = Auth::user()->unreadNotifications;
        $count = $unreadNotifications->count();

        if ($count > 0) {
            $unreadNotifications->markAsRead();

            // Manual update for bulk operation
            Auth::user()->update(['unread_notifications_count' => 0]);
        }

        return response()->json([
            'success' => true,
            'marked_count' => $count,
            'unread_count' => 0
        ]);
    }

    /**
     * حذف إشعار
     */
    public function destroy(string $id): JsonResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * حذف جميع الإشعارات المقروءة
     */
    public function clearRead(): JsonResponse
    {
        Auth::user()->notifications()->whereNotNull('read_at')->delete();

        return response()->json(['success' => true]);
    }
}