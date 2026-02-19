<?php

namespace Modules\Tickets\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class NotificationComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Total unread count for the main badge
            $unreadNotificationsCount = $user->unreadNotifications()->count();

            // Fetch latest 20 notifications to distribute across tabs
            $notifications = $user->notifications()->latest()->limit(20)->get();

            // Grouping logic
            $categories = [];

            // Augment notifications with resolved categories/modules
            $notifications->each(function ($n) {
                // 1. Resolve Category (system vs user)
                if (!isset($n->data['category'])) {
                    $actor = $n->data['actor_name'] ?? '';
                    if ($actor === 'System' || $actor === 'النظام' || $actor === __('tickets::messages.system')) {
                        $n->resolved_category = 'system';
                    } else {
                        $n->resolved_category = 'user';
                    }
                } else {
                    $n->resolved_category = $n->data['category'];
                }

                // 2. Resolve Module
                if (!isset($n->data['module'])) {
                    if (preg_match('/Modules\\\\([^\\\\]+)\\\\/', $n->type, $matches)) {
                        $n->resolved_module = $matches[1];
                    } else {
                        $n->resolved_module = 'General';
                    }
                } else {
                    $n->resolved_module = $n->data['module'];
                }
            });

            // 1. System Category (Always exist as per request)
            $systemItems = $notifications->filter(fn($n) => $n->resolved_category === 'system');
            $categories['system'] = [
                'name' => app()->getLocale() == 'ar' ? 'النظام' : 'System',
                'id' => 'system-noti-tab',
                'items' => $systemItems,
                'unread' => $user->unreadNotifications()->where(function ($q) {
                    $q->where('data->category', 'system')
                        ->orWhere('data->actor_name', 'System')
                        ->orWhere('data->actor_name', 'النظام');
                })->count()
            ];

            // 2. Group User notifications by Module
            $userNotifications = $notifications->filter(fn($n) => $n->resolved_category !== 'system');
            $groupedByModule = $userNotifications->groupBy('resolved_module');

            foreach ($groupedByModule as $moduleName => $items) {
                $displayName = $moduleName;
                if ($moduleName === 'Tickets') {
                    $displayName = app()->getLocale() == 'ar' ? 'التذاكر' : 'Tickets';
                } elseif ($moduleName === 'General') {
                    $displayName = app()->getLocale() == 'ar' ? 'عام' : 'General';
                }

                $categories[strtolower($moduleName)] = [
                    'name' => $displayName,
                    'id' => strtolower($moduleName) . '-noti-tab',
                    'items' => $items,
                    'unread' => $user->unreadNotifications()->where(function ($q) use ($moduleName) {
                        if ($moduleName === 'General') {
                            $q->where(function ($sq) {
                                $sq->whereNull('data->module')
                                    ->where('type', 'not like', 'Modules\\\\%');
                            })->where('data->category', '!=', 'system');
                        } else {
                            $q->where('data->module', $moduleName)
                                ->orWhere('type', 'like', "Modules\\\\{$moduleName}\\\\%");
                        }
                    })->count()
                ];
            }

            $view->with([
                'unreadNotificationsCount' => $unreadNotificationsCount,
                'allNotifications' => $notifications,
                'notificationCategories' => $categories
            ]);
        }
    }
}
