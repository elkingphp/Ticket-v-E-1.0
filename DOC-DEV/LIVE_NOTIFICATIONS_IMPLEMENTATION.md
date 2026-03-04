# 🔔 Live Notifications System - Implementation Report
**تقرير تفعيل نظام التنبيهات الحية**

---

## 📊 Current Status Analysis | تحليل الوضع الحالي

### ✅ What's Already Working | ما يعمل حالياً

1. **AJAX Polling System** (30 seconds interval)
   - Endpoint: `/notifications/latest`
   - Unread count tracking
   - Notification dropdown in navbar
   - Permission-based access

2. **Database Notifications**
   - Laravel's built-in notification system
   - User notifications relationship
   - Read/unread status tracking

3. **Laravel Reverb** (WebSocket Server)
   - Already installed and running on port 8080
   - Configuration ready in `config/broadcasting.php`

### ⚠️ What Needs Implementation | ما يحتاج تفعيل

1. **Broadcasting Configuration**
   - Enable BROADCAST_CONNECTION=reverb in .env
   - Configure Laravel Echo on frontend

2. **Real-time Event Broadcasting**
   - Create notification events
   - Broadcast to user channels

3. **Frontend WebSocket Integration**
   - Laravel Echo setup
   - Fallback to polling on connection failure

---

## 🚀 Implementation Plan | خطة التنفيذ

### Phase 1: Backend Configuration | الإعدادات الخلفية

#### Step 1: Update .env File
```bash
# Add/Update these lines in .env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=digilians-app
REVERB_APP_KEY=local-key-12345
REVERB_APP_SECRET=local-secret-67890
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

#### Step 2: Create Notification Event
**File:** `app/Events/NewNotification.php`

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Notifications\DatabaseNotification;

class NewNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;
    public $userId;

    public function __construct(DatabaseNotification $notification, int $userId)
    {
        $this->notification = $notification;
        $this->userId = $userId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.new';
    }

    public function broadcastWith(): array
    {
        $avatar = $this->notification->data['avatar'] ?? null;
        
        if ($avatar && !str_starts_with($avatar, 'http')) {
            $avatar = asset($avatar);
        }
        
        $avatar = $avatar ?: asset('assets/images/users/user-dummy-img.jpg');

        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'title' => $this->notification->data['title'] ?? 'System Notification',
            'message' => $this->notification->data['message'] ?? '',
            'priority' => $this->notification->data['priority'] ?? 'info',
            'avatar' => $avatar,
            'created_at' => $this->notification->created_at->toIso8601String(),
            'created_at_human' => $this->notification->created_at->diffForHumans(),
            'action_url' => $this->notification->data['action_url'] ?? '#',
        ];
    }
}
```

#### Step 3: Update NotificationController
**File:** `Modules/Core/Http/Controllers/NotificationController.php`

Add after line 73:

```php
use App\Events\NewNotification;

// In markAsRead method, broadcast update
public function markAsRead(string $id): JsonResponse
{
    $notification = Auth::user()->notifications()->findOrFail($id);
    $notification->markAsRead();

    // Broadcast notification read event
    broadcast(new \App\Events\NotificationRead($notification, Auth::id()))->toOthers();

    return response()->json([
        'success' => true,
        'message' => __('messages.notification_marked_read'),
    ]);
}
```

#### Step 4: Create Notification Observer
**File:** `app/Observers/NotificationObserver.php`

```php
<?php

namespace App\Observers;

use Illuminate\Notifications\DatabaseNotification;
use App\Events\NewNotification;

class NotificationObserver
{
    public function created(DatabaseNotification $notification): void
    {
        // Broadcast new notification to user
        if ($notification->notifiable_type === 'Modules\Users\Domain\Models\User') {
            broadcast(new NewNotification($notification, $notification->notifiable_id));
        }
    }
}
```

#### Step 5: Register Observer
**File:** `app/Providers/AppServiceProvider.php`

```php
use Illuminate\Notifications\DatabaseNotification;
use App\Observers\NotificationObserver;

public function boot(): void
{
    // ... existing code ...
    
    DatabaseNotification::observe(NotificationObserver::class);
}
```

---

### Phase 2: Frontend Integration | التكامل الأمامي

#### Step 1: Install Laravel Echo & Pusher JS
```bash
npm install --save laravel-echo pusher-js
```

#### Step 2: Configure Laravel Echo
**File:** `resources/js/bootstrap.js`

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'local-key-12345',
    wsHost: import.meta.env.VITE_REVERB_HOST || 'localhost',
    wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT || 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});
```

#### Step 3: Update Notifications Manager
**File:** `resources/js/notifications.js`

```javascript
class NotificationsManager {
    constructor() {
        this.notificationsList = document.getElementById('notifications-list');
        this.loader = document.getElementById('notification-loader');
        this.notificationBadge = document.getElementById('notification-badge');
        this.notificationCount = document.getElementById('notification-count');
        this.pollInterval = 30000; // Fallback polling
        this.isWebSocketConnected = false;
        
        this.init();
    }

    init() {
        // Try WebSocket first
        this.setupWebSocket();
        
        // Fallback to polling if WebSocket fails
        setTimeout(() => {
            if (!this.isWebSocketConnected) {
                console.warn('WebSocket not connected, falling back to polling');
                this.startPolling();
            }
        }, 5000);
        
        // Desktop notifications permission
        this.requestDesktopPermission();
    }

    setupWebSocket() {
        if (typeof window.Echo === 'undefined') {
            console.error('Laravel Echo not initialized');
            return;
        }

        const userId = document.querySelector('meta[name="user-id"]')?.content;
        if (!userId) {
            console.error('User ID not found');
            return;
        }

        // Listen for new notifications
        window.Echo.private(`user.${userId}`)
            .listen('.notification.new', (event) => {
                console.log('New notification received:', event);
                this.isWebSocketConnected = true;
                this.addNotification(event);
                this.updateUnreadCount(1);
                this.showDesktopNotification(event);
            })
            .error((error) => {
                console.error('WebSocket error:', error);
                this.isWebSocketConnected = false;
            });

        // Initial load
        this.loadNotifications();
    }

    startPolling() {
        this.loadNotifications();
        setInterval(() => this.loadNotifications(), this.pollInterval);
    }

    async loadNotifications() {
        try {
            if (this.loader && (!this.notificationsList.hasChildNodes() || this.notificationsList.innerHTML.trim() === '')) {
                this.loader.style.display = 'block';
            }

            const response = await fetch('/notifications/latest?limit=10', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to load notifications');

            const data = await response.json();
            this.updateNotifications(data.notifications, data.unread_count);
        } catch (error) {
            console.error('Error loading notifications:', error);
            this.showError();
        } finally {
            if (this.loader) {
                this.loader.style.display = 'none';
            }
        }
    }

    addNotification(notification) {
        // Add to top of list
        const notificationHtml = this.createNotificationHtml(notification);
        this.notificationsList.insertAdjacentHTML('afterbegin', notificationHtml);
        
        // Limit to 10 notifications
        const items = this.notificationsList.querySelectorAll('.notification-item');
        if (items.length > 10) {
            items[items.length - 1].remove();
        }
    }

    updateUnreadCount(increment = 0) {
        const currentCount = parseInt(this.notificationBadge.textContent) || 0;
        const newCount = currentCount + increment;
        
        this.notificationBadge.textContent = newCount;
        this.notificationCount.textContent = `${newCount} ${window.APP_LOCALE === 'ar' ? 'جديد' : 'new'}`;
        
        if (newCount > 0) {
            this.notificationBadge.style.display = 'inline-block';
        } else {
            this.notificationBadge.style.display = 'none';
        }
    }

    showDesktopNotification(notification) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(notification.title, {
                body: notification.message,
                icon: notification.avatar,
                tag: notification.id,
                requireInteraction: notification.priority === 'high'
            });
        }
    }

    requestDesktopPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    createNotificationHtml(notification) {
        const priorityClass = {
            'high': 'danger',
            'medium': 'warning',
            'low': 'info'
        }[notification.priority] || 'info';

        return `
            <div class="text-reset notification-item d-block dropdown-item position-relative" data-id="${notification.id}">
                <div class="d-flex">
                    <img src="${notification.avatar}" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                    <div class="flex-grow-1">
                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">${notification.title}</h6>
                        <div class="fs-13 text-muted">
                            <p class="mb-1">${notification.message}</p>
                        </div>
                        <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                            <span><i class="mdi mdi-clock-outline"></i> ${notification.created_at_human}</span>
                        </p>
                    </div>
                    <span class="badge bg-${priorityClass} rounded-pill ms-2">${notification.priority}</span>
                </div>
            </div>
        `;
    }

    updateNotifications(notifications, unreadCount) {
        this.notificationsList.innerHTML = '';
        
        if (notifications.length === 0) {
            this.notificationsList.innerHTML = `
                <div class="text-center py-4">
                    <i class="ri-notification-off-line fs-1 text-muted"></i>
                    <p class="text-muted">${window.APP_LOCALE === 'ar' ? 'لا توجد تنبيهات' : 'No notifications'}</p>
                </div>
            `;
        } else {
            notifications.forEach(notification => {
                this.notificationsList.insertAdjacentHTML('beforeend', this.createNotificationHtml(notification));
            });
        }

        this.updateUnreadCount(unreadCount - (parseInt(this.notificationBadge.textContent) || 0));
    }

    showError() {
        this.notificationsList.innerHTML = `
            <div class="text-center py-4 text-danger">
                <i class="ri-error-warning-line fs-1"></i>
                <p>${window.APP_LOCALE === 'ar' ? 'فشل تحميل التنبيهات' : 'Failed to load notifications'}</p>
            </div>
        `;
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('notifications-list')) {
        new NotificationsManager();
    }
});
```

#### Step 4: Add User ID Meta Tag
**File:** `Modules/Core/Resources/views/layouts/master.blade.php`

Add in `<head>` section:

```blade
<meta name="user-id" content="{{ auth()->id() }}">
```

---

### Phase 3: Testing & Verification | الاختبار والتحقق

#### Test 1: WebSocket Connection
```bash
# Terminal 1: Start Reverb
php artisan reverb:start

# Terminal 2: Start Queue Worker
php artisan queue:work

# Terminal 3: Test Broadcasting
php artisan tinker
>>> $user = \Modules\Users\Domain\Models\User::first();
>>> $user->notify(new \Illuminate\Notifications\Messages\BroadcastMessage([
...     'title' => 'Test Notification',
...     'message' => 'This is a test',
...     'priority' => 'high'
... ]));
```

#### Test 2: Desktop Notifications
1. Open browser console
2. Check for "Notification permission" prompt
3. Allow notifications
4. Trigger a notification
5. Verify desktop notification appears

#### Test 3: Fallback to Polling
1. Stop Reverb server
2. Refresh page
3. Verify polling starts after 5 seconds
4. Check console for "falling back to polling" message

---

## 🎨 Theme Customization Integration | تكامل تخصيص السمات

### Database Migration
```php
// Migration: add_theme_preferences_to_users_table
Schema::table('users', function (Blueprint $table) {
    $table->json('theme_preferences')->nullable()->after('theme_mode');
});
```

### Theme Structure
```json
{
    "primary_color": "#405189",
    "notification_sound": true,
    "desktop_notifications": true,
    "notification_position": "top-right"
}
```

---

## 📊 Profile Analytics Integration | تكامل تحليلات الملف الشخصي

### New Analytics Features
1. **Notification Statistics**
   - Total notifications received
   - Read vs Unread ratio
   - Notifications by priority chart

2. **Activity Timeline**
   - Notification history
   - Read timestamps
   - Action taken on notifications

---

## ✅ Implementation Checklist | قائمة التنفيذ

### Backend
- [ ] Update .env with Reverb configuration
- [ ] Create NewNotification event
- [ ] Create NotificationObserver
- [ ] Register observer in AppServiceProvider
- [ ] Update NotificationController with broadcasting

### Frontend
- [ ] Install Laravel Echo & Pusher JS
- [ ] Configure Echo in bootstrap.js
- [ ] Update NotificationsManager class
- [ ] Add user-id meta tag
- [ ] Compile assets (npm run build)

### Testing
- [ ] Test WebSocket connection
- [ ] Test notification broadcasting
- [ ] Test desktop notifications
- [ ] Test fallback to polling
- [ ] Test RTL/LTR compatibility
- [ ] Test permission-based notifications

---

## 🚀 Priority Recommendations | التوصيات حسب الأولوية

### 1. Live Notifications (HIGH PRIORITY) ✅
**Status:** Implementation plan ready  
**Time:** 2-3 days  
**Impact:** Immediate UX improvement, reduced server load

### 2. Theme Customization (MEDIUM PRIORITY)
**Status:** Database structure defined  
**Time:** 4-5 days  
**Impact:** Enhanced personalization

### 3. Profile Analytics (MEDIUM PRIORITY)
**Status:** Feature specifications ready  
**Time:** 3-4 days  
**Impact:** Better user insights

---

## 📝 Conclusion | الخلاصة

The Live Notifications system is **ready for implementation** with:
- ✅ Complete backend architecture
- ✅ Frontend WebSocket integration
- ✅ Fallback mechanism
- ✅ Desktop notifications support
- ✅ RBAC integration
- ✅ RTL/LTR compatibility

**Next Steps:**
1. Execute backend configuration
2. Install and configure frontend dependencies
3. Test thoroughly
4. Deploy to production

---

**Report Date:** 2026-02-14  
**Status:** ✅ READY FOR IMPLEMENTATION
