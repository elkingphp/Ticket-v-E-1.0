@extends('core::layouts.master')

@section('title', __('core::messages.notification_dashboard'))

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('core::messages.notification_dashboard') }}</h4>
                <div class="page-title-right">
                    <button class="btn btn-primary"
                        onclick="location.href='{{ route('admin.notifications.thresholds.index') }}'">
                        <i class="bx bx-cog"></i> {{ __('core::messages.manage_thresholds') }}
                    </button>
                    <button class="btn btn-info" onclick="location.href='{{ route('admin.notifications.statistics') }}'">
                        <i class="bx bx-bar-chart"></i> {{ __('core::messages.statistics') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted mb-0">
                                {{ __('core::messages.total_notifications') }}</p>
                        </div>
                        <div class="flex-shrink-0">
                            <h5 class="text-success fs-14 mb-0">
                                <i class="ri-arrow-right-up-line fs-13 align-middle"></i>
                            </h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                {{ number_format($stats['total_notifications']) }}
                            </h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle rounded fs-3">
                                <i class="bx bx-bell text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted mb-0">{{ __('core::messages.unread') }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                {{ number_format($stats['unread_notifications']) }}
                            </h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-warning-subtle rounded fs-3">
                                <i class="bx bx-envelope text-warning"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted mb-0">{{ __('core::messages.archived') }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                {{ number_format($stats['archived_notifications']) }}
                            </h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-info-subtle rounded fs-3">
                                <i class="bx bx-archive text-info"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted mb-0">
                                {{ __('core::messages.active_thresholds') }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                {{ $stats['active_thresholds'] }}
                            </h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle rounded fs-3">
                                <i class="bx bx-slider text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Channel Health -->
    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('core::messages.channel_health') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('core::messages.channel') }}</th>
                                    <th>{{ __('core::messages.status') }}</th>
                                    <th>{{ __('core::messages.response_time') }}</th>
                                    <th>{{ __('core::messages.message') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($channelsHealth as $channel => $health)
                                    <tr>
                                        <td>
                                            <i class="bx bx-{{ getChannelIcon($channel) }} me-2"></i>
                                            {{ ucfirst($channel) }}
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ getStatusColor($health['status']) }}">
                                                {{ ucfirst($health['status']) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($health['latency_ms'])
                                                <span
                                                    class="text-muted">{{ number_format($health['latency_ms'], 2) }}ms</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="text-muted">{{ $health['message'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('core::messages.failed_notifications') }}</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h2 class="mb-3">{{ $failedStats['total'] }}</h2>
                        <p class="text-muted">{{ __('core::messages.total_notifications') }}</p>
                    </div>

                    @if ($failedStats['total'] > 0)
                        <div class="mt-4">
                            <button class="btn btn-warning w-100" onclick="retryFailed()">
                                <i class="bx bx-refresh"></i> {{ __('core::messages.retry') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('core::messages.maintenance_tools') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="archiveOld()">
                            <i class="bx bx-archive"></i> {{ __('core::notifications.archive_old') }}
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteRead()">
                            <i class="bx bx-trash"></i> {{ __('core::notifications.delete_read') }}
                        </button>
                        <button class="btn btn-outline-info" onclick="showTestModal()">
                            <i class="bx bx-test-tube"></i> {{ __('core::notifications.test_notification') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications by Type -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('core::notifications.distribution_by_type') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="notificationsByTypeChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Chart for notifications by type
            const ctx = document.getElementById('notificationsByTypeChart');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($notificationsByType->pluck('type')->map(fn($t) => class_basename($t))) !!},
                    datasets: [{
                        label: '{{ __('core::messages.total_notifications') }}',
                        data: {!! json_encode($notificationsByType->pluck('count')) !!},
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            function retryFailed() {
                if (!confirm('{{ __('core::notifications.confirm_retry') }}')) return;

                fetch('{{ route('admin.notifications.cleanup') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'retry_failed'
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        alert(data.message);
                        location.reload();
                    });
            }

            function archiveOld() {
                const days = prompt('{{ __('core::notifications.archive_days_prompt') }}', '30');
                if (!days) return;

                fetch('{{ route('admin.notifications.cleanup') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'archive',
                            days: parseInt(days)
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        alert(data.message);
                        location.reload();
                    });
            }

            function deleteRead() {
                if (!confirm('{{ __('core::notifications.confirm_delete_read') }}')) return;

                fetch('{{ route('admin.notifications.cleanup') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'delete_read'
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        alert(data.message);
                        location.reload();
                    });
            }

            function showTestModal() {
                // Implement test notification modal
                alert('{{ __('core::notifications.test_feature_coming_soon') }}');
            }
        </script>
    @endpush

    @php
        function getChannelIcon($channel)
        {
            return match ($channel) {
                'database' => 'data',
                'mail' => 'envelope',
                'redis' => 'server',
                'reverb' => 'broadcast',
                default => 'circle',
            };
        }

        function getStatusColor($status)
        {
            return match ($status) {
                'healthy' => 'success',
                'degraded' => 'warning',
                'failed' => 'danger',
                default => 'secondary',
            };
        }
    @endphp
@endsection
