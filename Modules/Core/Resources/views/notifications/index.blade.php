@extends('core::layouts.master')

@section('title', 'الإشعارات')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri-notification-3-line align-middle me-1"></i>
                        الإشعارات
                    </h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-soft-primary" onclick="markAllAsRead()">
                            <i class="ri-check-double-line align-middle me-1"></i> تحديد الكل كمقروء
                        </button>
                        <button type="button" class="btn btn-sm btn-soft-danger" onclick="clearReadNotifications()">
                            <i class="ri-delete-bin-line align-middle me-1"></i> حذف المقروءة
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('notifications.index') }}" class="d-flex gap-2">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">جميع الحالات</option>
                                    <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>غير مقروءة</option>
                                    <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>مقروءة</option>
                                </select>
                                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">جميع الأنواع</option>
                                    <option value="CriticalAuditAlert" {{ request('type') === 'CriticalAuditAlert' ? 'selected' : '' }}>تنبيهات حرجة</option>
                                    <option value="SystemHealthAlert" {{ request('type') === 'SystemHealthAlert' ? 'selected' : '' }}>صحة النظام</option>
                                    <option value="UserRegisteredAlert" {{ request('type') === 'UserRegisteredAlert' ? 'selected' : '' }}>مستخدمين جدد</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <!-- Notifications List -->
                    @if($notifications->isEmpty())
                        <div class="text-center py-5">
                            <i class="ri-notification-off-line text-muted mb-3" style="font-size: 48px;"></i>
                            <p class="text-muted">لا توجد إشعارات</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                                    @php
                                        $userName = $notification->data['user_name'] ?? $notification->data['title'] ?? 'System';
                                        $initials = '';
                                        if ($userName) {
                                            $nameParts = explode(' ', trim($userName));
                                            $initials = (count($nameParts) >= 2) 
                                                ? mb_strtoupper(mb_substr($nameParts[0], 0, 1) . mb_substr($nameParts[count($nameParts)-1], 0, 1))
                                                : mb_strtoupper(mb_substr($userName, 0, 2));
                                        }
                                        $avatar = $notification->data['avatar'] ?? null;
                                    @endphp
                                <div class="list-group-item {{ $notification->read_at ? '' : 'bg-light' }} py-3" id="notification-{{ $notification->id }}">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            @if($avatar)
                                                <img src="{{ asset($avatar) }}" class="rounded-circle avatar-sm" alt="" onerror="this.src='/assets/images/users/user-dummy-img.jpg'">
                                            @else
                                                <div class="avatar-sm">
                                                    <span class="avatar-title rounded-circle bg-primary-subtle text-primary fs-14">
                                                        {{ $initials }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="d-flex align-items-center mb-1">
                                                <h6 class="mb-0 me-2">{{ $notification->data['title'] ?? 'إشعار' }}</h6>
                                                <span class="badge bg-{{ getPriorityColor($notification->data['priority'] ?? 'info') }}-subtle text-{{ getPriorityColor($notification->data['priority'] ?? 'info') }} fs-11">
                                                    {{ strtoupper($notification->data['priority'] ?? 'info') }}
                                                </span>
                                            </div>
                                            <p class="text-muted mb-0">{{ $notification->data['message'] ?? '' }}</p>
                                            <small class="text-muted fs-11 mt-1 d-block">
                                                <i class="ri-time-line align-middle me-1"></i>
                                                {{ $notification->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        <div class="flex-shrink-0 ms-2">
                                            <div class="d-flex gap-1">
                                                @if(!$notification->read_at)
                                                    <button class="btn btn-sm btn-icon btn-ghost-primary" onclick="markAsRead('{{ $notification->id }}')" title="تحديد كمقروء">
                                                        <i class="ri-checkbox-circle-line fs-17"></i>
                                                    </button>
                                                @endif
                                                <button class="btn btn-sm btn-icon btn-ghost-danger" onclick="deleteNotification('{{ $notification->id }}')" title="حذف">
                                                    <i class="ri-delete-bin-line fs-17"></i>
                                                </button>
                                                @if(isset($notification->data['action_url']))
                                                    <a href="{{ $notification->data['action_url'] }}" class="btn btn-sm btn-icon btn-ghost-info" title="عرض التفاصيل">
                                                        <i class="ri-external-link-line fs-17"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function markAsRead(id) {
    fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`notification-${id}`).classList.remove('list-group-item-info');
            location.reload();
        }
    });
}

function markAllAsRead() {
    if (!confirm('هل تريد تحديد جميع الإشعارات كمقروءة؟')) return;
    
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function deleteNotification(id) {
    if (!confirm('هل تريد حذف هذا الإشعار؟')) return;
    
    fetch(`/notifications/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`notification-${id}`).remove();
        }
    });
}

function clearReadNotifications() {
    if (!confirm('هل تريد حذف جميع الإشعارات المقروءة؟')) return;
    
    fetch('/notifications/clear-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
</script>
@endpush

@php
function getPriorityColor($priority) {
    return match($priority) {
        'critical' => 'danger',
        'warning' => 'warning',
        'info' => 'info',
        default => 'secondary'
    };
}
@endphp
@endsection
