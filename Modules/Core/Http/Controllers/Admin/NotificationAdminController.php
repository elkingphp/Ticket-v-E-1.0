<?php

namespace Modules\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Domain\Models\NotificationThreshold;
use Modules\Core\Domain\Models\NotificationPreference;
use Modules\Core\Application\Services\NotificationMonitor;
use Modules\Core\Application\Services\AlertService;
use Modules\Core\Application\Services\FallbackLogger;

class NotificationAdminController extends Controller
{
    public function __construct(
        protected NotificationMonitor $monitor,
        protected AlertService $alertService,
        protected FallbackLogger $fallbackLogger
        )
    {
    }

    /**
     * Dashboard الإشعارات
     */
    public function dashboard()
    {
        // إحصائيات عامة
        $stats = [
            'total_notifications' => DB::table('notifications')->count(),
            'unread_notifications' => DB::table('notifications')->whereNull('read_at')->count(),
            'archived_notifications' => DB::table('notifications_archive')->count(),
            'active_thresholds' => NotificationThreshold::where('enabled', true)->count(),
            'total_users_with_preferences' => NotificationPreference::distinct('user_id')->count(),
        ];

        // صحة القنوات
        $channelsHealth = $this->monitor->checkChannelHealth();

        // إحصائيات الإشعارات حسب النوع
        $notificationsByType = DB::table('notifications')
            ->select(DB::raw("type, COUNT(*) as count"))
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // آخر الإشعارات الفاشلة
        $failedStats = $this->fallbackLogger->getFailedNotificationsStats();

        // إحصائيات الإرسال
        $sendingStats = $this->alertService->getStats();

        return view('core::admin.notifications.dashboard', compact(
            'stats',
            'channelsHealth',
            'notificationsByType',
            'failedStats',
            'sendingStats'
        ));
    }

    /**
     * إدارة Thresholds
     */
    public function thresholds()
    {
        $thresholds = NotificationThreshold::orderBy('event_type')->paginate(20);

        return view('core::admin.notifications.thresholds.index', compact('thresholds'));
    }

    /**
     * تحديث Threshold
     */
    public function updateThreshold(Request $request, NotificationThreshold $threshold)
    {
        $validated = $request->validate([
            'max_count' => 'required|integer|min:1',
            'time_window' => 'required|integer|min:60',
            'severity' => 'required|in:info,warning,critical',
            'enabled' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $threshold->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Threshold updated successfully',
            'threshold' => $threshold
        ]);
    }

    /**
     * إنشاء Threshold جديد
     */
    public function createThreshold(Request $request)
    {
        $validated = $request->validate([
            'event_type' => 'required|string|unique:notification_thresholds,event_type',
            'max_count' => 'required|integer|min:1',
            'time_window' => 'required|integer|min:60',
            'severity' => 'required|in:info,warning,critical',
            'enabled' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $threshold = NotificationThreshold::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Threshold created successfully',
            'threshold' => $threshold
        ]);
    }

    /**
     * حذف Threshold
     */
    public function deleteThreshold(NotificationThreshold $threshold)
    {
        $threshold->delete();

        return response()->json([
            'success' => true,
            'message' => 'Threshold deleted successfully'
        ]);
    }

    /**
     * تبديل حالة Threshold
     */
    public function toggleThreshold(NotificationThreshold $threshold)
    {
        $threshold->update(['enabled' => !$threshold->enabled]);

        return response()->json([
            'success' => true,
            'enabled' => $threshold->enabled,
            'message' => $threshold->enabled ? 'Threshold enabled' : 'Threshold disabled'
        ]);
    }

    /**
     * إحصائيات مفصلة
     */
    public function statistics(Request $request)
    {
        $period = $request->input('period', 7); // آخر 7 أيام افتراضياً

        // إحصائيات يومية
        $dailyStats = DB::table('notifications')
            ->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN read_at IS NULL THEN 1 ELSE 0 END) as unread')
        )
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // إحصائيات حسب النوع
        $typeStats = DB::table('notifications')
            ->select('type', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('type')
            ->get();

        // إحصائيات حسب الأولوية
        $driver = DB::connection()->getDriverName();
        $priorityExpr = $driver === 'pgsql' ? "data->>'priority'" : "JSON_EXTRACT(data, '$.priority')";

        $priorityStats = DB::table('notifications')
            ->select(
            DB::raw("$priorityExpr as priority"),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('priority')
            ->get();

        return view('core::admin.notifications.statistics', compact(
            'dailyStats',
            'typeStats',
            'priorityStats',
            'period'
        ));
    }

    /**
     * تنظيف الإشعارات
     */
    public function cleanup(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:archive,delete_read,delete_old',
            'days' => 'required_if:action,delete_old|integer|min:1',
        ]);

        $result = match ($validated['action']) {
                'archive' => $this->archiveNotifications($validated['days'] ?? 30),
                'delete_read' => $this->deleteReadNotifications(),
                'delete_old' => $this->deleteOldNotifications($validated['days']),
            };

        return response()->json($result);
    }

    /**
     * أرشفة الإشعارات
     */
    protected function archiveNotifications(int $days): array
    {
        $cutoffDate = now()->subDays($days);

        $notifications = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('created_at', '<', $cutoffDate)
            ->get();

        $archived = 0;
        foreach ($notifications as $notification) {
            DB::table('notifications_archive')->insert([
                'id' => $notification->id,
                'type' => $notification->type,
                'notifiable_type' => $notification->notifiable_type,
                'notifiable_id' => $notification->notifiable_id,
                'data' => $notification->data,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
                'archived_at' => now(),
            ]);
            $archived++;
        }

        $deleted = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        return [
            'success' => true,
            'message' => "Archived {$archived} notifications",
            'archived' => $archived,
            'deleted' => $deleted
        ];
    }

    /**
     * حذف الإشعارات المقروءة
     */
    protected function deleteReadNotifications(): array
    {
        $deleted = DB::table('notifications')
            ->whereNotNull('read_at')
            ->delete();

        return [
            'success' => true,
            'message' => "Deleted {$deleted} read notifications",
            'deleted' => $deleted
        ];
    }

    /**
     * حذف الإشعارات القديمة
     */
    protected function deleteOldNotifications(int $days): array
    {
        $cutoffDate = now()->subDays($days);

        $deleted = DB::table('notifications')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        return [
            'success' => true,
            'message' => "Deleted {$deleted} old notifications",
            'deleted' => $deleted
        ];
    }

    /**
     * اختبار إرسال إشعار
     */
    public function testNotification(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:audit_critical,system_health,user_registered,threshold_exceeded',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = \Modules\Users\Domain\Models\User::find($validated['user_id']);

        $testData = [
            'title' => 'Test Notification',
            'message' => 'This is a test notification sent from the admin panel',
            'priority' => 'info',
            'action_url' => route('notifications.index'),
        ];

        try {
            $this->alertService->sendAlert($validated['type'], $testData, $user);

            return response()->json([
                'success' => true,
                'message' => 'Test notification sent successfully'
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification: ' . $e->getMessage()
            ], 500);
        }
    }
}