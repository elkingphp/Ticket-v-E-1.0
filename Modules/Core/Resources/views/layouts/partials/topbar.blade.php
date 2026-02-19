<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="/" class="logo logo-dark">
                        <span class="logo-sm">
                            @if(get_setting('logo_sm'))
                                <img src="{{ asset(get_setting('logo_sm')) }}" alt="" height="22">
                            @elseif(get_setting('logo_dark'))
                                <img src="{{ asset(get_setting('logo_dark')) }}" alt="" height="22">
                            @else
                                <span class="fs-22 fw-bold text-primary">D</span>
                            @endif
                        </span>
                        <span class="logo-lg">
                            @if(get_setting('logo_dark'))
                                <img src="{{ asset(get_setting('logo_dark')) }}" alt="" height="22">
                            @else
                                <span class="fs-22 fw-bold text-primary">{{ get_setting('site_name', 'DIGILIANS') }}</span>
                            @endif
                        </span>
                    </a>
                    <a href="/" class="logo logo-light">
                        <span class="logo-sm">
                            @if(get_setting('logo_sm'))
                                <img src="{{ asset(get_setting('logo_sm')) }}" alt="" height="22">
                            @elseif(get_setting('logo_light'))
                                <img src="{{ asset(get_setting('logo_light')) }}" alt="" height="22">
                            @else
                                <span class="fs-22 fw-bold text-white">D</span>
                            @endif
                        </span>
                        <span class="logo-lg">
                            @if(get_setting('logo_light'))
                                <img src="{{ asset(get_setting('logo_light')) }}" alt="" height="22">
                            @else
                                <span class="fs-22 fw-bold text-white">{{ get_setting('site_name', 'DIGILIANS') }}</span>
                            @endif
                        </span>
                    </a>

                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger"
                    id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>

            <div class="d-flex align-items-center">

                <!-- Language Selection -->
                <div class="dropdown ms-1 topbar-head-dropdown header-item">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        @if(app()->getLocale() == 'ar')
                            <img src="{{ asset('assets/images/flags/sa.svg') }}" alt="Arabic" height="20" class="rounded">
                        @else
                            <img src="{{ asset('assets/images/flags/us.svg') }}" alt="English" height="20" class="rounded">
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="{{ route('lang.switch', 'en') }}" class="dropdown-item notify-item language py-2 @if(app()->getLocale() == 'en') active @endif" data-lang="en" title="English">
                            <img src="{{ asset('assets/images/flags/us.svg') }}" alt="user-image" class="me-2 rounded" height="18">
                            <span class="align-middle">English</span>
                        </a>
                        <a href="{{ route('lang.switch', 'ar') }}" class="dropdown-item notify-item language py-2 @if(app()->getLocale() == 'ar') active @endif" data-lang="ar" title="Arabic">
                            <img src="{{ asset('assets/images/flags/sa.svg') }}" alt="user-image" class="me-2 rounded" height="18">
                            <span class="align-middle">العربية</span>
                        </a>
                    </div>
                </div>

                <!-- Notification Bell -->
                <div class="dropdown ms-1 topbar-head-dropdown header-item" id="notificationDropdown">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" 
                            id="page-header-notifications-dropdown" data-bs-toggle="dropdown" 
                            data-bs-auto-close="outside"
                            aria-haspopup="true" aria-expanded="false">
                        <i class='bx bx-bell fs-22'></i>
                        @if($unreadNotificationsCount > 0)
                        <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger" 
                              id="notification-badge">
                            {{ $unreadNotificationsCount }}
                        </span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" 
                         aria-labelledby="page-header-notifications-dropdown">
                        <div class="dropdown-head bg-primary bg-pattern rounded-top">
                            <div class="p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0 fs-16 fw-semibold text-white">{{ __('sidebar.notifications') }}</h6>
                                    </div>
                                    <div class="col-auto">
                                        @if($unreadNotificationsCount > 0)
                                        <form action="{{ route('notifications.markAllRead') }}" method="POST" id="mark-all-read-form">
                                            @csrf
                                            <a href="javascript:void(0);" onclick="document.getElementById('mark-all-read-form').submit();" class="badge bg-light-subtle text-body fs-13">
                                                {{ app()->getLocale() == 'ar' ? 'تحديد الكل كمقروء' : 'Mark all read' }}
                                            </a>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-2 pt-2 border-bottom border-bottom-dashed">
                            <ul class="nav nav-tabs dropdown-tabs nav-tabs-custom flex-nowrap overflow-auto" role="tablist" style="scrollbar-width: none; -ms-overflow-style: none;">
                                <li class="nav-item flex-shrink-0 waves-effect waves-light">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#all-noti-tab" role="tab">
                                        {{ app()->getLocale() == 'ar' ? 'الكل' : 'All' }}
                                        <span class="badge bg-soft-secondary text-secondary ms-1 fs-10" id="badge-all">{{ $unreadNotificationsCount }}</span>
                                    </a>
                                </li>
                                @foreach($notificationCategories as $key => $category)
                                <li class="nav-item flex-shrink-0 waves-effect waves-light">
                                    <a class="nav-link" data-bs-toggle="tab" href="#{{ $category['id'] }}" role="tab" data-module="{{ $key }}">
                                        {{ $category['name'] }}
                                        <span class="badge bg-soft-primary text-primary ms-1 fs-10" id="badge-{{ $category['id'] }}" style="{{ $category['unread'] == 0 ? 'display: none;' : '' }}">
                                            {{ $category['unread'] }}
                                        </span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="tab-content position-relative" id="notificationItemsTabContent" style="overflow-x: hidden;">
                            <div class="tab-pane fade show active py-2" id="all-noti-tab" role="tabpanel">
                                <div data-simplebar style="max-height: 300px; overflow-x: hidden;" class="pe-2">
                                    @forelse($allNotifications as $notification)
                                        @include('core::layouts.partials.notification_item', ['notification' => $notification])
                                    @empty
                                        @include('core::layouts.partials.notification_empty_state')
                                    @endforelse
                                </div>
                            </div>

                            @foreach($notificationCategories as $key => $category)
                            <div class="tab-pane fade py-2" id="{{ $category['id'] }}" role="tabpanel">
                                <div data-simplebar style="max-height: 300px; overflow-x: hidden;" class="pe-2">
                                    @forelse($category['items'] as $notification)
                                        @include('core::layouts.partials.notification_item', ['notification' => $notification])
                                    @empty
                                        @include('core::layouts.partials.notification_empty_state')
                                    @endforelse
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @if($unreadNotificationsCount > 0 || $allNotifications->isNotEmpty())
                        <div class="my-2 py-2 text-center view-all-button border-top border-top-dashed">
                            <a href="{{ route('notifications.index') }}" class="btn btn-soft-primary btn-sm px-4">
                                {{ app()->getLocale() == 'ar' ? 'عرض جميع الإشعارات' : 'View All Notifications' }} 
                                <i class="ri-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}-line align-middle ms-1"></i>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user"
                                src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->full_name) . '&background=405189&color=fff' }}"
                                alt="Header Avatar"
                                onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->full_name) }}&background=405189&color=fff'">
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{
                                    auth()->user()?->full_name ?? 'Guest' }}</span>
                                <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">{{ auth()->user()?->roles->first()?->name ?? 'User' }}</span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <h6 class="dropdown-header">{{ __('messages.welcome') }}, {{ auth()->user()?->first_name }}!</h6>
                        <a class="dropdown-item" href="{{ route('profile.index') }}"><i
                                class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span
                                class="align-middle">{{ __('sidebar.profile') }}</span></a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span
                                    class="align-middle" data-key="t-logout">{{ __('sidebar.logout') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Notification Details Modal -->
<div class="modal fade" id="notificationDetailModal" tabindex="-1" aria-labelledby="notificationDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header p-3 bg-primary-subtle">
                <h5 class="modal-title" id="notificationDetailModalLabel">التفاصيل</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="flex-shrink-0" id="modal-notification-avatar-container">
                        <!-- Avatar will be injected here -->
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="fs-15 mb-1" id="modal-notification-title"></h5>
                        <p class="text-muted mb-0" id="modal-notification-time"></p>
                    </div>
                </div>
                <div class="notification-content-wrapper">
                    <p class="text-muted fs-14" id="modal-notification-message"></p>
                </div>
            </div>
            <div class="modal-footer border-top border-top-dashed">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إغلاق</button>
                <a href="#" id="modal-notification-action-btn" class="btn btn-primary d-none">عرض التفاصيل</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationContainer = document.getElementById('notificationDropdown');
    
    if (notificationContainer) {
        notificationContainer.addEventListener('click', function(e) {
            const link = e.target.closest('.notification-title-link');
            if (!link) return;
            
            e.preventDefault();
            
            // Get data from attributes
            const id = link.getAttribute('data-id');
            const title = link.getAttribute('data-title');
            const message = link.getAttribute('data-message');
            const time = link.getAttribute('data-time');
            const url = link.getAttribute('data-url');
            
            // Populate Modal
            document.getElementById('modal-notification-title').innerText = title;
            document.getElementById('modal-notification-message').innerText = message;
            document.getElementById('modal-notification-time').innerText = time;
            
            const actionBtn = document.getElementById('modal-notification-action-btn');
            if (url && url !== '#') {
                actionBtn.href = url;
                actionBtn.classList.remove('d-none');
            } else {
                actionBtn.classList.add('d-none');
            }
            
            // Mark as read via AJAX if unread
            const item = link.closest('.notification-item');
            if (item && item.classList.contains('active')) {
                fetch(`/notifications/${id}/read`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).then(response => response.json()).then(data => {
                    if (data.success) {
                        // 1. Update general item appearance
                        item.classList.remove('active', 'bg-light-subtle');
                        const dot = item.querySelector('.badge-dot');
                        if (dot) dot.remove();
                        
                        // 2. Update Main Bell Badge
                        const mainBadge = document.getElementById('notification-badge');
                        if (mainBadge) {
                            mainBadge.innerText = data.unread_count;
                            if (data.unread_count <= 0) mainBadge.style.display = 'none';
                        }
                        
                        // 3. Update All Tab Badge
                        const allBadge = document.getElementById('badge-all');
                        if (allBadge) {
                            allBadge.innerText = data.unread_count;
                        }
                        
                        // 4. Update Sector Specific Badge
                        const category = link.getAttribute('data-category');
                        const module = link.getAttribute('data-module');
                        let targetTabId = '';
                        
                        if (category === 'system') {
                            targetTabId = 'system-noti-tab';
                        } else if (module) {
                            targetTabId = module.toLowerCase() + '-noti-tab';
                        } else {
                            targetTabId = 'general-noti-tab';
                        }
                        
                        const sectorBadge = document.getElementById('badge-' + targetTabId);
                        if (sectorBadge) {
                            let count = parseInt(sectorBadge.innerText) || 0;
                            count = Math.max(0, count - 1);
                            sectorBadge.innerText = count;
                            if (count <= 0) sectorBadge.style.display = 'none';
                        }
                    }
                }).catch(error => console.error('Error marking as read:', error));
            }
            
            // Show Modal
            const modal = new bootstrap.Modal(document.getElementById('notificationDetailModal'));
            modal.show();
        });
    }
});
</script>

<style>
/* Hide scrollbar for tabs but allow scrolling */
.dropdown-tabs::-webkit-scrollbar {
    display: none;
}
.dropdown-tabs {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
/* Ensure no horizontal scroll in tab panes */
.tab-pane [data-simplebar] {
    overflow-x: hidden !important;
}
.tab-pane .simplebar-content {
    overflow-x: hidden !important;
}
</style>