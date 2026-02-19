/**
 * Live Notifications Manager
 * Handles real-time notifications via WebSocket with fallback to AJAX polling
 * Supports RTL/LTR, Desktop Notifications, and RBAC integration
 */
class NotificationsManager {
    constructor() {
        this.notificationsList = document.getElementById('notifications-list');
        this.loader = document.getElementById('notification-loader');
        this.notificationBadge = document.getElementById('notification-badge');
        this.markAllReadBtn = document.getElementById('mark-all-read');
        this.pollInterval = 30000; // 30 seconds fallback
        this.isWebSocketConnected = false;
        this.userId = null;

        // Cache properties
        this.lastLoaded = null;
        this.cacheTimeout = 30000; // 30 seconds
        this.cachedData = null;

        this.init();
    }

    init() {
        // Get user ID from meta tag
        this.userId = document.querySelector('meta[name="user-id"]')?.content;

        if (!this.userId) {
            console.error('❌ User ID not found - notifications disabled');
            return;
        }

        // Try WebSocket first
        this.setupWebSocket();

        // Fallback to polling if WebSocket fails after 5 seconds
        setTimeout(() => {
            if (!this.isWebSocketConnected) {
                console.warn('⚠️ WebSocket not connected, falling back to AJAX polling');
                this.startPolling();
            }
        }, 5000);

        // Request desktop notification permission
        this.requestDesktopPermission();

        // Load notifications when dropdown opens
        this.setupDropdownListener();

        // Mark all as read listener
        this.setupMarkAllReadListener();

        // Clear cache on logout
        this.setupLogoutListener();

        // Notification item click listener (Event Delegation)
        this.setupNotificationClickListeners();
    }

    setupDropdownListener() {
        const dropdownButton = document.getElementById('page-header-notifications-dropdown');
        if (dropdownButton) {
            dropdownButton.addEventListener('show.bs.dropdown', () => {
                console.log('📂 Dropdown opened');

                // Check cache first
                if (this.isCacheValid()) {
                    console.log('✅ Using cached data');
                    this.updateNotifications(this.cachedData.notifications, this.cachedData.unread_count);
                } else {
                    console.log('🔄 Cache expired or missing - loading fresh data');
                    this.loadNotifications();
                }
            });
        }
    }

    isCacheValid() {
        if (!this.lastLoaded || !this.cachedData) return false;
        return (Date.now() - this.lastLoaded) < this.cacheTimeout;
    }

    invalidateCache() {
        this.lastLoaded = null;
        this.cachedData = null;
        console.log('🗑️ Cache invalidated');
    }

    setupLogoutListener() {
        // Clear cache when user logs out to prevent data leaking between sessions
        const logoutBtn = document.querySelector('a[href*="logout"]');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => this.invalidateCache());
        }
    }

    setupMarkAllReadListener() {
        if (this.markAllReadBtn) {
            this.markAllReadBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.markAllAsRead();
            });
        }
    }

    setupNotificationClickListeners() {
        if (!this.notificationsList) return;

        this.notificationsList.addEventListener('click', async (e) => {
            const item = e.target.closest('.notification-item');
            if (!item) return;

            const notificationId = item.getAttribute('data-id');
            const data = this.cachedData?.notifications?.find(n => n.id === notificationId);

            if (!data) {
                console.warn('⚠️ Notification data not found in cache');
                return;
            }

            e.preventDefault();

            // Mark as read if unread
            if (item.classList.contains('bg-light')) {
                try {
                    await this.markAsRead(notificationId, item);
                } catch (error) {
                    console.error('❌ Failed to mark as read:', error);
                }
            }

            // Show Modal
            this.showNotificationModal(data);
        });
    }

    showNotificationModal(data) {
        const modal = document.getElementById('notificationDetailModal');
        if (!modal) return;

        // Fill Modal Data
        document.getElementById('modal-notification-title').textContent = data.title;
        document.getElementById('modal-notification-message').textContent = data.message;
        document.getElementById('modal-notification-time').textContent = data.created_at_human;

        const avatarContainer = document.getElementById('modal-notification-avatar-container');
        if (data.avatar) {
            avatarContainer.innerHTML = `<img src="${data.avatar}" class="rounded-circle avatar-sm" alt="">`;
        } else {
            avatarContainer.innerHTML = `
                <div class="avatar-sm">
                    <span class="avatar-title rounded-circle bg-primary-subtle text-primary fs-14 uppercase">
                        ${data.initials || '??'}
                    </span>
                </div>`;
        }

        const actionBtn = document.getElementById('modal-notification-action-btn');
        if (data.action_url && data.action_url !== '#') {
            actionBtn.href = data.action_url;
            actionBtn.classList.remove('d-none');
        } else {
            actionBtn.classList.add('d-none');
        }

        // Show using Bootstrap
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }

    async markAsRead(id, element) {
        try {
            const response = await fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                console.log(`✅ Notification ${id} marked as read`);

                // Update Badge
                if (data.unread_count !== undefined) {
                    this.setUnreadCount(data.unread_count);
                } else {
                    this.updateUnreadCount(-1);
                }

                // Update UI Element
                if (element) {
                    element.classList.remove('bg-light');
                }

                // Invalidate cache
                this.invalidateCache();
                return true;
            }
        } catch (error) {
            console.error('❌ Error marking notification as read:', error);
            throw error;
        }
        return false;
    }

    async markAllAsRead() {
        try {
            const response = await fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                console.log('✅ All notifications marked as read');
                this.invalidateCache();
                this.setUnreadCount(0);

                // Update UI items
                const items = this.notificationsList.querySelectorAll('.notification-item');
                items.forEach(item => {
                    item.classList.remove('bg-light');
                });

                this.showToast(
                    window.APP_LOCALE === 'ar' ? 'تم تحديد الكل كمقروء' : 'All marked as read',
                    '#405189'
                );
            }
        } catch (error) {
            console.error('❌ Error marking all as read:', error);
        }
    }

    setupWebSocket() {
        if (typeof window.Echo === 'undefined') {
            console.error('❌ Laravel Echo not initialized');
            return;
        }

        console.log(`🔌 Connecting to WebSocket channel: user.${this.userId}`);

        // Listen for new notifications on private user channel
        window.Echo.private(`user.${this.userId}`)
            .listen('.notification.new', (event) => {
                console.log('✅ New notification received via WebSocket:', event);
                this.isWebSocketConnected = true;
                this.handleNewNotification(event);
            })
            .error((error) => {
                console.error('❌ WebSocket error:', error);
                this.isWebSocketConnected = false;
            });

        // Initial load of notifications
        this.loadNotifications();
    }

    startPolling() {
        console.log('🔄 Starting AJAX polling (every 30 seconds)');
        this.loadNotifications();
        setInterval(() => this.loadNotifications(), this.pollInterval);
    }

    async loadNotifications() {
        try {
            // Show loader
            if (this.loader) {
                this.loader.style.display = 'block';
            }

            const response = await fetch('/notifications/latest?limit=10', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // Handle authentication errors
            if (response.status === 401) {
                console.warn('⚠️ User not authenticated');
                window.location.href = '/login';
                return;
            }

            // Handle permission errors
            if (response.status === 403) {
                console.error('❌ Permission denied');
                this.showError('You do not have permission to view notifications');
                return;
            }

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            // Check success flag
            if (data.success === false) {
                throw new Error(data.error || 'Failed to load notifications');
            }

            // Cache the data
            this.cachedData = {
                notifications: data.notifications,
                unread_count: data.unread_count
            };
            this.lastLoaded = Date.now();

            console.log('✅ Notifications loaded and cached');
            this.updateNotifications(data.notifications, data.unread_count);

        } catch (error) {
            console.error('❌ Error loading notifications:', error);
            this.showError(error.message);
        } finally {
            if (this.loader) {
                this.loader.style.display = 'none';
            }
        }
    }

    handleNewNotification(notification) {
        // Invalidate cache since we have new data
        this.invalidateCache();

        // Add to top of list
        this.addNotification(notification);

        // Update unread count
        this.updateUnreadCount(1);

        // Show Toast (Velzon Integration)
        this.showToast(
            `${notification.title}: ${notification.message}`,
            notification.priority === 'high' ? '#f06548' : '#0ab39c'
        );

        // Show desktop notification
        this.showDesktopNotification(notification);

        // Play sound (optional)
        this.playNotificationSound();
    }

    showToast(message, color) {
        if (typeof Toastify !== 'undefined') {
            Toastify({
                text: message,
                duration: 5000,
                close: true,
                gravity: "top",
                position: "right",
                stopOnFocus: true,
                style: {
                    background: color || "#405189",
                }
            }).showToast();
        }
    }

    addNotification(notification) {
        const notificationHtml = this.createNotificationHtml(notification);
        this.notificationsList.insertAdjacentHTML('afterbegin', notificationHtml);

        // Limit to 10 notifications in dropdown
        const items = this.notificationsList.querySelectorAll('.notification-item');
        if (items.length > 10) {
            items[items.length - 1].remove();
        }

        // Add fade-in animation
        const newItem = this.notificationsList.querySelector('.notification-item');
        if (newItem) {
            newItem.style.animation = 'fadeIn 0.3s ease-in';
        }
    }

    updateUnreadCount(increment = 0) {
        const currentCount = parseInt(this.notificationBadge.textContent) || 0;
        const newCount = Math.max(0, currentCount + increment);
        this.setUnreadCount(newCount);
    }

    showDesktopNotification(notification) {
        if ('Notification' in window && Notification.permission === 'granted') {
            const desktopNotif = new Notification(notification.title, {
                body: notification.message,
                icon: notification.avatar,
                tag: notification.id,
                requireInteraction: notification.priority === 'high',
                badge: '/assets/images/logo-sm.png',
                vibrate: [200, 100, 200]
            });

            // Handle click
            desktopNotif.onclick = () => {
                window.focus();
                if (notification.action_url && notification.action_url !== '#') {
                    window.location.href = notification.action_url;
                }
                desktopNotif.close();
            };

            // Auto-close after 5 seconds for non-high priority
            if (notification.priority !== 'high') {
                setTimeout(() => desktopNotif.close(), 5000);
            }
        }
    }

    requestDesktopPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                console.log('🔔 Notification permission:', permission);
            });
        }
    }

    playNotificationSound() {
        // Optional: Play notification sound
        try {
            const audio = new Audio('/assets/sounds/notification.mp3');
            audio.volume = 0.3;
            audio.play().catch(e => {
                // Silently fail if sound not available or autoplay blocked
                console.log('🔇 Sound play blocked or unavailable');
            });
        } catch (e) {
            // Sound file not found - ignore
        }
    }

    createNotificationHtml(notification) {
        const priorityColors = {
            'critical': '#f3d9d9', // red background for initials
            'high': '#f06548',     // danger
            'medium': '#f7b84b',   // warning
            'low': '#299cdb'       // info
        };
        const color = priorityColors[notification.priority] || '#405189';

        const isRTL = window.APP_LOCALE === 'ar';
        const avatarMarginClass = isRTL ? 'ms-3' : 'me-3';
        const isUnread = !notification.read_at;
        const bgClass = isUnread ? 'bg-light' : '';
        const borderSide = isRTL ? 'border-right' : 'border-left';

        // Avatar logic: Image or Initials
        let avatarHtml = '';
        if (notification.avatar) {
            avatarHtml = `<img src="${notification.avatar}" class="${avatarMarginClass} rounded-circle avatar-xs" alt="user-pic" onerror="this.src='/assets/images/users/user-dummy-img.jpg'">`;
        } else {
            avatarHtml = `
                <div class="avatar-xs ${avatarMarginClass}">
                    <span class="avatar-title rounded-circle bg-primary-subtle text-primary fs-12 uppercase">
                        ${notification.initials || '??'}
                    </span>
                </div>`;
        }

        return `
            <div class="text-reset notification-item d-block dropdown-item position-relative ${bgClass}" 
                 data-id="${notification.id}"
                 style="animation: fadeIn 0.3s ease; ${borderSide}: 3px solid ${color};">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        ${avatarHtml}
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <a href="${notification.action_url || 'javascript:void(0);'}" class="stretched-link">
                            <h6 class="mt-0 mb-1 fs-13 fw-semibold text-truncate">${this.escapeHtml(notification.title)}</h6>
                        </a>
                        <div class="fs-12 text-muted">
                            <p class="mb-1 text-truncate">${this.escapeHtml(notification.message)}</p>
                        </div>
                        <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                            <span><i class="mdi mdi-clock-outline"></i> ${notification.created_at_human}</span>
                        </p>
                    </div>
                </div>
            </div>
        `;
    }

    updateNotifications(notifications, unreadCount) {
        this.notificationsList.innerHTML = '';

        if (notifications.length === 0) {
            this.notificationsList.innerHTML = `
                <div class="empty-notification-elem">
                    <div class="w-25 w-sm-50 pt-3 mx-auto">
                        <img src="/assets/images/svg/bell.svg" class="img-fluid" alt="user-pic">
                    </div>
                    <div class="text-center pb-5 mt-2">
                        <h6 class="fs-18 fw-semibold lh-base">${window.APP_LOCALE === 'ar' ? 'لا توجد تنبيهات جديدة' : 'Hey! You have no any notifications'}</h6>
                    </div>
                </div>
            `;
        } else {
            notifications.forEach(notification => {
                this.notificationsList.insertAdjacentHTML('beforeend', this.createNotificationHtml(notification));
            });
        }

        // Set unread count (not increment)
        this.setUnreadCount(unreadCount);
    }

    setUnreadCount(count) {
        if (!this.notificationBadge) return;

        const oldValue = parseInt(this.notificationBadge.textContent) || 0;
        this.notificationBadge.textContent = count;

        // Animate if count increased
        if (count > oldValue) {
            this.notificationBadge.classList.add('pulse-animation');
            setTimeout(() => this.notificationBadge.classList.remove('pulse-animation'), 1000);
        }

        if (count > 0) {
            this.notificationBadge.style.display = 'inline-block';
        } else {
            this.notificationBadge.style.display = 'none';
        }
    }

    showError(message) {
        const errorText = message || (window.APP_LOCALE === 'ar' ? 'فشل تحميل التنبيهات' : 'Failed to load notifications');
        const retryText = window.APP_LOCALE === 'ar' ? 'إعادة المحاولة' : 'Retry';

        this.notificationsList.innerHTML = `
            <div class="text-center py-4 text-danger">
                <i class="ri-error-warning-line fs-1"></i>
                <p class="mt-2">${this.escapeHtml(errorText)}</p>
                <button class="btn btn-sm btn-primary mt-2" onclick="window.notificationsManager?.loadNotifications()">
                    <i class="ri-refresh-line"></i> ${retryText}
                </button>
            </div>
        `;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('notifications-list')) {
        window.notificationsManager = new NotificationsManager();
        console.log('✅ NotificationsManager initialized');
    }
});

// Add fade-in animation CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    .pulse-animation {
        animation: pulse 0.5s ease-in-out;
    }
    .notification-item {
        transition: all 0.2s ease;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .notification-item:hover {
        background-color: rgba(64, 81, 137, 0.05) !important;
    }
`;
document.head.appendChild(style);

export default NotificationsManager;
