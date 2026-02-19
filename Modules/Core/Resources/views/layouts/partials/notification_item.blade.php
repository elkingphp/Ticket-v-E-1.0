<div class="text-reset notification-item d-block dropdown-item position-relative border-bottom border-bottom-dashed py-3 @if(!$notification->read_at) active bg-light-subtle @endif" data-id="{{ $notification->id }}">
    <div class="d-flex align-items-center overflow-hidden">
        <div class="avatar-xs me-3 flex-shrink-0">
            <span class="avatar-title bg-{{ $notification->data['color'] ?? 'primary' }}-subtle text-{{ $notification->data['color'] ?? 'primary' }} rounded-circle fs-16 shadow-sm">
                <i class="{{ $notification->data['icon'] ?? 'ri-notification-3-line' }}"></i>
            </span>
        </div>
        <div class="flex-grow-1 overflow-hidden">
            <a href="javascript:void(0);" 
               class="notification-title-link text-decoration-none"
               data-id="{{ $notification->id }}"
               data-title="{{ $notification->data['title'] ?? '' }}"
               data-message="{{ $notification->data['message'] ?? '' }}"
               data-time="{{ $notification->created_at->diffForHumans() }}"
               data-category="{{ $notification->resolved_category ?? '' }}"
               data-module="{{ $notification->resolved_module ?? '' }}"
               data-url="{{ $notification->data['url'] ?? '#' }}">
                <h6 class="mt-0 mb-1 fs-14 fw-semibold text-dark text-truncate">{{ $notification->data['title'] ?? '' }}</h6>
            </a>
            <div class="fs-13 text-muted">
                <p class="mb-1 text-truncate">{{ $notification->data['message'] ?? '' }}</p>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <p class="mb-0 fs-11 fw-medium text-muted">
                    <span><i class="ri-history-line me-1"></i> {{ $notification->created_at->diffForHumans() }}</span>
                </p>
                @if(!$notification->read_at)
                    <span class="badge badge-dot bg-primary"></span>
                @endif
            </div>
        </div>
    </div>
</div>
