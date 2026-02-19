<div class="row">
    <div class="col-md-6 mb-3">
        <label class="fw-bold">{{ __('Actor') }}</label>
        <p>{{ $log->user ? $log->user->full_name : __('System') }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="fw-bold">{{ __('Event') }}</label>
        <p>{{ strtoupper($log->event) }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="fw-bold">{{ __('Resource') }}</label>
        <p>{{ $log->auditable_type }} (ID: {{ $log->auditable_id }})</p>
    </div>
    <div class="col-md-6 mb-3">
        <label class="fw-bold">{{ __('IP Address') }}</label>
        <p>{{ $log->ip_address }}</p>
    </div>
    <div class="col-12 mb-3">
        <label class="fw-bold">{{ __('URL') }}</label>
        <p class="text-break">{{ $log->url }}</p>
    </div>
    <div class="col-12 mb-3">
        <label class="fw-bold">{{ __('User Agent') }}</label>
        <p class="small text-muted">{{ $log->user_agent }}</p>
    </div>
</div>

<hr>

<h6 class="mb-3">{{ __('Data Changes') }}</h6>
<div class="table-responsive">
    <table class="table table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>{{ __('Field') }}</th>
                <th>{{ __('Old Value') }}</th>
                <th>{{ __('New Value') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $allKeys = array_unique(array_merge(
                    array_keys($log->old_values ?? []),
                    array_keys($log->new_values ?? [])
                ));
            @endphp
            @foreach($allKeys as $key)
                @php
                    $old = $log->old_values[$key] ?? null;
                    $new = $log->new_values[$key] ?? null;
                    $isChanged = json_encode($old) !== json_encode($new);
                @endphp
                <tr class="{{ $isChanged ? 'table-warning' : '' }}">
                    <td><code>{{ $key }}</code></td>
                    <td class="text-danger">
                        @if(is_array($old))
                            <pre class="mb-0 small">{{ json_encode($old, JSON_PRETTY_PRINT) }}</pre>
                        @else
                            {{ $old }}
                        @endif
                    </td>
                    <td class="text-success">
                        @if(is_array($new))
                            <pre class="mb-0 small">{{ json_encode($new, JSON_PRETTY_PRINT) }}</pre>
                        @else
                            {{ $new }}
                        @endif
                    </td>
                </tr>
            @endforeach
            @if(empty($allKeys))
                <tr>
                    <td colspan="3" class="text-center text-muted italic">{{ __('No detailed data changes available') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
