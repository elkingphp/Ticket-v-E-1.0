@extends('core::layouts.master')

@section('title', __('core::notifications.statistics_title'))

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('core::notifications.statistics_title') }}</h4>
                <div class="page-title-right">
                    <form action="{{ route('admin.notifications.statistics') }}" method="GET"
                        class="d-flex align-items-center">
                        <label class="form-label me-2 mb-0">{{ __('core::notifications.period') }}</label>
                        <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="7" {{ $period == 7 ? 'selected' : '' }}>
                                {{ __('core::notifications.last_7_days') }}</option>
                            <option value="30" {{ $period == 30 ? 'selected' : '' }}>
                                {{ __('core::notifications.last_30_days') }}</option>
                            <option value="90" {{ $period == 90 ? 'selected' : '' }}>
                                {{ __('core::notifications.last_30_days') }}</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Daily Notifications Chart -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header border-0 align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">{{ __('core::notifications.daily_notifications') }}</h4>
                </div>
                <div class="card-body">
                    <div class="w-100">
                        <canvas id="dailyStatsChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Priority Distribution Chart -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">{{ __('core::notifications.distribution_by_severity') }}</h4>
                </div>
                <div class="card-body">
                    <canvas id="priorityStatsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Type Distribution Chart -->
        <div class="col-12">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">{{ __('core::notifications.distribution_by_type') }}</h4>
                </div>
                <div class="card-body">
                    <canvas id="typeStatsChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Daily Stats Chart
            const dailyCtx = document.getElementById('dailyStatsChart');
            new Chart(dailyCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($dailyStats->pluck('date')) !!},
                    datasets: [{
                            label: '{{ __('core::notifications.total_notifications') }}',
                            data: {!! json_encode($dailyStats->pluck('total')) !!},
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: '{{ __('core::notifications.unread') }}',
                            data: {!! json_encode($dailyStats->pluck('unread')) !!},
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: '{{ __('core::notifications.date') }}'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: '{{ __('core::notifications.count') }}'
                            }
                        }
                    }
                }
            });

            // Priority Stats Chart
            const priorityCtx = document.getElementById('priorityStatsChart');
            new Chart(priorityCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($priorityStats->pluck('priority')) !!},
                    datasets: [{
                        data: {!! json_encode($priorityStats->pluck('count')) !!},
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)', // Critical
                            'rgba(255, 205, 86, 0.8)', // Warning
                            'rgba(54, 162, 235, 0.8)', // Info
                            'rgba(75, 192, 192, 0.8)' // Other
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });

            // Type Stats Chart
            const typeCtx = document.getElementById('typeStatsChart');
            new Chart(typeCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($typeStats->pluck('type')) !!},
                    datasets: [{
                        label: '{{ __('core::notifications.count') }}',
                        data: {!! json_encode($typeStats->pluck('count')) !!},
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
    @endpush
@endsection
