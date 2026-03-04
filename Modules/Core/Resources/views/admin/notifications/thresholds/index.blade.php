@extends('core::layouts.master')

@section('title', __('core::notifications.thresholds_management'))

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('core::notifications.thresholds_management') }}
                    ({{ __('core::notifications.thresholds_breadcrumb') }})</h4>
                <div class="page-title-right">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createThresholdModal">
                        <i class="bx bx-plus"></i> {{ __('core::notifications.add_threshold') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('core::notifications.event_type') }}</th>
                                    <th>{{ __('core::notifications.max_count') }}</th>
                                    <th>{{ __('core::notifications.time_window') }}</th>
                                    <th>{{ __('core::notifications.severity') }}</th>
                                    <th>{{ __('core::notifications.status') }}</th>
                                    <th>{{ __('core::notifications.description') }}</th>
                                    <th>{{ __('core::notifications.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($thresholds as $threshold)
                                    <tr id="threshold-{{ $threshold->id }}">
                                        <td class="fw-medium">{{ $threshold->event_type }}</td>
                                        <td>{{ $threshold->max_count }} {{ __('core::notifications.times') }}</td>
                                        <td>{{ $threshold->time_window }} {{ __('core::notifications.seconds') }}</td>
                                        <td>
                                            <span class="badge bg-{{ getSeverityColor($threshold->severity) }}">
                                                {{ ucfirst($threshold->severity) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                    id="switch-{{ $threshold->id }}"
                                                    {{ $threshold->enabled ? 'checked' : '' }}
                                                    onchange="toggleThreshold({{ $threshold->id }})">
                                            </div>
                                        </td>
                                        <td>{{ Str::limit($threshold->description, 30) }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-soft-info"
                                                onclick="editThreshold({{ $threshold->id }}, {{ json_encode($threshold) }})">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-soft-danger"
                                                onclick="deleteThreshold({{ $threshold->id }})">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            {{ __('core::notifications.no_thresholds') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $thresholds->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="thresholdModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="thresholdModalTitle">{{ __('core::notifications.add_threshold') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="thresholdForm" onsubmit="saveThreshold(event)">
                    <div class="modal-body">
                        <input type="hidden" id="threshold_id" name="id">

                        <div class="mb-3">
                            <label for="event_type" class="form-label">{{ __('core::notifications.event_type') }}</label>
                            <input type="text" class="form-control" id="event_type" name="event_type" required
                                placeholder="e.g., audit_critical">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="max_count" class="form-label">{{ __('core::notifications.max_count') }}</label>
                                <input type="number" class="form-control" id="max_count" name="max_count" required
                                    min="1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="time_window"
                                    class="form-label">{{ __('core::notifications.time_window_seconds') }}</label>
                                <input type="number" class="form-control" id="time_window" name="time_window" required
                                    min="60">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="severity" class="form-label">{{ __('core::notifications.severity') }}</label>
                            <select class="form-select" id="severity" name="severity">
                                <option value="info">Info</option>
                                <option value="warning">Warning</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('core::notifications.description') }}</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="enabled" name="enabled"
                                checked>
                            <label class="form-check-label"
                                for="enabled">{{ __('core::notifications.enabled') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">{{ __('core::notifications.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('core::notifications.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const thresholdModal = new bootstrap.Modal(document.getElementById('thresholdModal'));
            let isEditing = false;

            document.querySelector('[data-bs-target="#createThresholdModal"]').addEventListener('click', () => {
                isEditing = false;
                document.getElementById('thresholdForm').reset();
                document.getElementById('threshold_id').value = '';
                document.getElementById('event_type').readOnly = false;
                document.getElementById('thresholdModalTitle').textContent =
                    '{{ __('core::notifications.add_threshold') }}';
                thresholdModal.show();
            });

            function editThreshold(id, data) {
                isEditing = true;
                document.getElementById('threshold_id').value = id;
                document.getElementById('event_type').value = data.event_type;
                document.getElementById('event_type').readOnly = true;
                document.getElementById('max_count').value = data.max_count;
                document.getElementById('time_window').value = data.time_window;
                document.getElementById('severity').value = data.severity;
                document.getElementById('description').value = data.description;
                document.getElementById('enabled').checked = data.enabled;
                document.getElementById('thresholdModalTitle').textContent = '{{ __('core::notifications.edit_threshold') }}';
                thresholdModal.show();
            }

            function saveThreshold(event) {
                event.preventDefault();
                const formData = new FormData(event.target);
                const data = Object.fromEntries(formData.entries());
                data.enabled = formData.get('enabled') === 'on' ? 1 : 0;

                const url = isEditing ?
                    `/admin/notifications/thresholds/${data.id}` :
                    '/admin/notifications/thresholds';

                const method = isEditing ? 'PUT' : 'POST';

                fetch(url, {
                        method: method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            location.reload();
                        } else {
                            alert('{{ __('core::messages.error_occurred') }}: ' + (result.message || 'Unknown error'));
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            function deleteThreshold(id) {
                if (!confirm('{{ __('core::notifications.confirm_delete') }}')) return;

                fetch(`/admin/notifications/thresholds/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            document.getElementById(`threshold-${id}`).remove();
                        }
                    });
            }

            function toggleThreshold(id) {
                fetch(`/admin/notifications/thresholds/${id}/toggle`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            // Toast or notification could be added here
                        }
                    });
            }
        </script>
    @endpush

    @php
        function getSeverityColor($severity)
        {
            return match ($severity) {
                'critical' => 'danger',
                'warning' => 'warning',
                'info' => 'info',
                default => 'secondary',
            };
        }
    @endphp
@endsection
