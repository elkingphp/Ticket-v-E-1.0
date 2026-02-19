@foreach($activities as $activity)
    <div class="acitivity-item d-flex mb-4">
        <div class="flex-shrink-0">
            <div class="avatar-xs acitivity-avatar">
                <div class="avatar-title rounded-circle bg-{{ $activity['color'] }}-subtle text-{{ $activity['color'] }}">
                    <i class="{{ $activity['icon'] }}"></i>
                </div>
            </div>
        </div>
        <div class="flex-grow-1 ms-3">
            <h6 class="mb-1 lh-base">{{ $activity['title'] }}</h6>
            <div class="mb-2">
                @if(!empty($activity['raw_fields']))
                    <span class="text-muted fs-13">{{ __('core::profile.changed_fields_label') }}:</span>
                    @foreach($activity['raw_fields'] as $field)
                        <span class="badge bg-light text-primary border border-primary-subtle fw-medium">{{ __('core::profile.' . $field) }}</span>
                    @endforeach
                @else
                    <p class="text-muted mb-0 fs-13">{!! $activity['description'] !!}</p>
                @endif

            </div>
            <small class="mb-0 text-muted">
                <i class="ri-history-line align-bottom"></i> {{ $activity['time'] }} 
                <span class="mx-1">|</span>
                <i class="ri-calendar-line align-bottom"></i> {{ $activity['formatted_time'] }}
                <span class="mx-1">|</span>
                <i class="ri-global-line align-bottom"></i> {{ $activity['ip_address'] }}
            </small>
        </div>
    </div>
@endforeach
