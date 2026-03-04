<div class="row">
    <div class="col-md-6 mb-3">
        <label class="fw-bold">{{ __('core::audit.actor') }}</label>
        <p>{{ $log->user ? $log->user->full_name : __('core::audit.system') }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="fw-bold">{{ __('core::audit.event') }}</label>
        <p>{{ strtoupper($log->event) }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="fw-bold">{{ __('core::audit.auditable_type') }}</label>
        <p>{{ $log->auditable_type }} (ID: {{ $log->auditable_id }})</p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="fw-bold">{{ __('core::audit.ip_address') }}</label>
        <p>{{ $log->ip_address }}</p>
    </div>
    <div class="col-12 mb-3">
        <label class="fw-bold">{{ __('core::audit.url') }}</label>
        <p class="text-break">{{ $log->url }}</p>
    </div>
    <div class="col-12 mb-3">
        <label class="fw-bold">{{ __('core::audit.user_agent') }}</label>
        <p class="small text-muted">{{ $log->user_agent }}</p>
    </div>
</div>

<hr>

<h6 class="mb-3">{{ __('core::audit.data_changes') }}</h6>
<div class="table-responsive">
    <table class="table table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>{{ __('core::audit.field') }}</th>
                <th>{{ __('core::audit.old_values') }}</th>
                <th>{{ __('core::audit.new_values') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $allKeys = array_unique(
                    array_merge(array_keys($log->old_values ?? []), array_keys($log->new_values ?? [])),
                );
            @endphp
            @foreach ($allKeys as $key)
                @php
                    $old = $log->old_values[$key] ?? null;
                    $new = $log->new_values[$key] ?? null;
                    $isChanged = json_encode($old) !== json_encode($new);

                    // Try to translate the key
                    $translatedKey = $key;
                    if (Lang::has("core::profile.{$key}")) {
                        $translatedKey = __("core::profile.{$key}");
                    } elseif (Lang::has("educational::messages.{$key}")) {
                        $translatedKey = __("educational::messages.{$key}");
                    } elseif (Lang::has("tickets::messages.{$key}")) {
                        $translatedKey = __("tickets::messages.{$key}");
                    } elseif (Lang::has("users::messages.{$key}")) {
                        $translatedKey = __("users::messages.{$key}");
                    } elseif (Lang::has("core::notifications.{$key}")) {
                        $translatedKey = __("core::notifications.{$key}");
                    }
                @endphp
                <tr class="{{ $isChanged ? 'table-warning' : '' }}">
                    <td><code>{{ $translatedKey }}</code></td>
                    <td class="text-danger">
                        @if (is_array($old))
                            <pre class="mb-0 small">{{ json_encode($old, JSON_PRETTY_PRINT) }}</pre>
                        @else
                            {{ $old }}
                        @endif
                    </td>
                    <td class="text-success">
                        @if (is_array($new))
                            <pre class="mb-0 small">{{ json_encode($new, JSON_PRETTY_PRINT) }}</pre>
                        @else
                            {{ $new }}
                        @endif
                    </td>
                </tr>
            @endforeach
            @if (empty($allKeys))
                <tr>
                    <td colspan="3" class="text-center text-muted italic">
                        {{ __('core::audit.no_detailed_changes') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
