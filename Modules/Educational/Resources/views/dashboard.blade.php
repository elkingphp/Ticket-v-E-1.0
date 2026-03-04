@extends('core::layouts.master')
@section('title', __('educational::messages.educational_dashboard'))

@push('styles')
    <style>
        .card-animate {
            transition: all 0.3s ease-in-out;
        }

        .card-animate:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        }

        .kpi-title {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #878a99;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .kpi-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #495057;
        }

        .filter-card {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
    </style>
@endpush

@section('content')

    <!-- Header & Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card filter-card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="row align-items-center mb-4">
                        <div class="col">
                            <h4 class="mb-1 fw-bold text-dark"><i class="ri-dashboard-2-line text-primary me-2"></i>
                                {{ __('educational::dashboard.title') }}</h4>
                            <p class="text-muted mb-0">{{ __('educational::dashboard.subtitle') }}</p>
                        </div>
                    </div>

                    <form action="{{ route('educational.dashboard') }}" method="GET">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label text-muted fw-semibold fs-13"><i
                                        class="ri-book-read-line me-1"></i>
                                    {{ __('educational::messages.program') }}</label>
                                <select name="program_id" class="form-select bg-light border-0" data-choices>
                                    <option value="">{{ __('educational::messages.all_programs') }}</option>
                                    @foreach ($programs as $p)
                                        <option value="{{ $p->id }}" {{ $programId == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted fw-semibold fs-13"><i
                                        class="ri-calendar-event-line me-1"></i>
                                    {{ __('educational::messages.from') }}</label>
                                <input type="date" name="date_from" class="form-control bg-light border-0"
                                    value="{{ $dateFrom->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted fw-semibold fs-13"><i
                                        class="ri-calendar-check-line me-1"></i>
                                    {{ __('educational::messages.to') }}</label>
                                <input type="date" name="date_to" class="form-control bg-light border-0"
                                    value="{{ $dateTo->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100 shadow-sm fw-bold">
                                    <i class="ri-filter-3-line align-bottom me-1"></i>
                                    {{ __('educational::messages.filter') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Primary KPIs -->
    <div class="row">
        <div class="col-xl-3 col-sm-6">
            <div class="card card-animate border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="kpi-title">{{ __('educational::dashboard.training_programs') }}</p>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="badge bg-success-subtle text-success fs-12"><i
                                    class="ri-checkbox-circle-line me-1"></i>{{ $stats['active_programs'] }}
                                {{ __('educational::dashboard.active') }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-3">
                        <div>
                            <h4 class="kpi-value mb-1">{{ $stats['total_programs'] }}</h4>
                            <a href="{{ route('educational.programs.index') }}"
                                class="text-decoration-underline text-muted fs-13">{{ __('educational::dashboard.view_details') }}</a>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-primary rounded fs-3">
                                <i class="ri-stack-line text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card card-animate border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="kpi-title">{{ __('educational::dashboard.trainees') }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-3">
                        <div>
                            <h4 class="kpi-value mb-1">{{ $stats['total_students'] }}</h4>
                            <a href="{{ route('educational.students.index') }}"
                                class="text-decoration-underline text-muted fs-13">{{ __('educational::dashboard.manage_students') }}</a>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-success rounded fs-3">
                                <i class="ri-team-line text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card card-animate border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="kpi-title">{{ __('educational::dashboard.instructors') }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-3">
                        <div>
                            <h4 class="kpi-value mb-1">{{ $stats['total_instructors'] }}</h4>
                            <a href="{{ route('educational.instructors.index') }}"
                                class="text-decoration-underline text-muted fs-13">{{ __('educational::dashboard.view_details') }}</a>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-warning rounded fs-3">
                                <i class="ri-user-star-line text-warning"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card card-animate border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="kpi-title">{{ __('educational::dashboard.companies_tracks') }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-3">
                        <div>
                            <h4 class="kpi-value mb-1">{{ $stats['total_companies'] }} <span
                                    class="fs-13 text-muted fw-normal">{{ __('educational::dashboard.companies') }}</span>
                                / {{ $stats['total_tracks'] }} <span
                                    class="fs-13 text-muted fw-normal">{{ __('educational::dashboard.tracks') }}</span>
                            </h4>
                            <a href="{{ route('educational.companies.index') }}"
                                class="text-decoration-underline text-muted fs-13">{{ __('educational::dashboard.view_companies') }}</a>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-info rounded fs-3">
                                <i class="ri-briefcase-4-line text-info"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Operational KPIs -->
    <div class="row">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm border-start border-3 border-info">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <div class="avatar-title bg-info-subtle text-info rounded-circle fs-4">
                                <i class="ri-pie-chart-2-line"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 fs-12 fw-semibold">
                                {{ __('educational::dashboard.occupancy_rate_today') }}</p>
                            <h5 class="mb-0 fw-bold">{{ $stats['occupancy_rate'] }}%</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm border-start border-3 border-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <div class="avatar-title bg-success-subtle text-success rounded-circle fs-4">
                                <i class="ri-user-follow-line"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 fs-12 fw-semibold">
                                {{ __('educational::dashboard.attendance_rate') }}</p>
                            <h5 class="mb-0 fw-bold">{{ $stats['attendance_rate'] }}%</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm border-start border-3 border-primary">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-4">
                                <i class="ri-computer-line"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 fs-12 fw-semibold">{{ __('educational::dashboard.lectures_today') }}
                            </p>
                            <h5 class="mb-0 fw-bold">{{ $stats['lectures_today'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm border-start border-3 border-warning">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <div class="avatar-title bg-warning-subtle text-warning rounded-circle fs-4">
                                <i class="ri-calendar-todo-line"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 fs-12 fw-semibold">
                                {{ __('educational::dashboard.upcoming_lectures') }}</p>
                            <h5 class="mb-0 fw-bold">{{ $stats['upcoming_lectures'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center d-flex border-0 bg-transparent pb-0 pt-4">
                    <h4 class="card-title mb-0 flex-grow-1 fw-bold fs-16"><i
                            class="ri-history-line text-primary me-1"></i>
                        {{ __('educational::dashboard.recent_lectures') }}</h4>
                    <div class="flex-shrink-0">
                        <a href="{{ route('educational.lectures.index') }}"
                            class="btn btn-soft-primary btn-sm px-3 fw-medium">{{ __('educational::dashboard.view_all') }}</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive table-card mt-3">
                        <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                            <thead class="text-muted table-light">
                                <tr>
                                    <th class="fw-semibold">{{ __('educational::dashboard.lecture_program') }}</th>
                                    <th class="fw-semibold">{{ __('educational::dashboard.room') }}</th>
                                    <th class="fw-semibold">{{ __('educational::dashboard.time') }}</th>
                                    <th class="fw-semibold">{{ __('educational::dashboard.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLectures as $lecture)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs flex-shrink-0 me-2">
                                                    <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                        <i class="ri-book-open-line"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h6 class="mb-1">
                                                        {{ $lecture->subject ?? ($lecture->sessionType->name ?? __('educational::messages.generic_lecture')) }}
                                                    </h6>
                                                    <p class="text-muted mb-0 fs-12">{{ $lecture->program->name ?? '-' }}
                                                        ({{ $lecture->group->name ?? '-' }})
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark shadow-sm">
                                                <i class="ri-building-line text-primary align-middle me-1"></i>
                                                {{ $lecture->room->name ?? __('educational::messages.not_specified') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-muted fs-13">
                                                <i class="ri-calendar-2-line me-1 text-primary"></i>
                                                {{ $lecture->starts_at->translatedFormat('d M Y') }}<br>
                                                <i class="ri-time-line me-1 text-primary"></i>
                                                {{ $lecture->starts_at->format('H:i') }} -
                                                {{ $lecture->ends_at->format('H:i') }}
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $colors = [
                                                    'scheduled' => 'info',
                                                    'running' => 'primary',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger',
                                                    'rescheduled' => 'warning',
                                                ];
                                                $color = $colors[$lecture->status] ?? 'secondary';
                                            @endphp
                                            <span
                                                class="badge bg-{{ $color }}-subtle text-{{ $color }} px-2 py-1 fs-12">
                                                {{ __('educational::dashboard.' . $lecture->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="ri-inbox-line fs-24 mb-2 d-block"></i>
                                                {{ __('educational::dashboard.no_lectures') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 bg-transparent pb-0 pt-4">
                    <h4 class="card-title mb-0 flex-grow-1 fw-bold fs-16"><i
                            class="ri-bar-chart-grouprd-line text-primary me-1"></i>
                        {{ __('educational::dashboard.distribution_by_type') }}
                        ({{ __('educational::dashboard.period') }})</h4>
                </div>
                <div class="card-body">
                    <div id="instructor_load_chart" data-colors='["--vz-primary"]' class="apex-charts" dir="ltr">
                    </div>
                    <div class="mt-3">
                        <p class="text-muted small mb-0"><i class="ri-information-line me-1"></i>
                            {{ __('educational::dashboard.chart_top5_hint') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 bg-transparent pb-0 pt-4">
                    <h4 class="card-title mb-0 fw-bold fs-16"><i class="ri-pie-chart-2-line text-primary me-1"></i>
                        {{ __('educational::dashboard.status') }}</h4>
                </div>
                <div class="card-body border-bottom">
                    <div id="status_overview_chart"
                        data-colors='["--vz-info", "--vz-primary", "--vz-success", "--vz-danger", "--vz-warning"]'
                        class="apex-charts" dir="ltr"></div>
                </div>
                <div class="card-body">
                    <h6 class="text-uppercase fw-semibold text-muted fs-12 mb-3">
                        {{ __('educational::dashboard.status_details') }}</h6>
                    @php
                        $totalStatus = array_sum($statusBreakdown);
                    @endphp
                    <div class="d-flex flex-column gap-3">
                        @foreach ($statusBreakdown as $status => $count)
                            @php
                                $colors = [
                                    'scheduled' => 'info',
                                    'running' => 'primary',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    'rescheduled' => 'warning',
                                ];
                                $color = $colors[$status] ?? 'secondary';
                                $percent = $totalStatus > 0 ? round(($count / $totalStatus) * 100) : 0;
                            @endphp
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs flex-shrink-0">
                                    <span class="avatar-title bg-{{ $color }}-subtle rounded-circle">
                                        <i class="ri-checkbox-blank-circle-fill text-{{ $color }} fs-10"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 fs-13">{{ __('educational::dashboard.' . $status) }}</h6>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-{{ $color }}" role="progressbar"
                                            style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}"
                                            aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 ms-3">
                                    <h6 class="mb-0 fs-13">{{ $count }} ({{ $percent }}%)</h6>
                                </div>
                            </div>
                        @endforeach
                        @if (empty($statusBreakdown))
                            <div class="text-center text-muted small">{{ __('educational::dashboard.no_lectures') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        // 1. Instructor Load Chart (Bar)
        var instLabels = {!! json_encode(
            $topInstructors->map(
                fn($i) => $i->instructorProfile->user->full_name ??
                    ($i->instructorProfile->user->name ?? 'ID: ' . $i->instructor_profile_id),
            ),
        ) !!};
        var instCounts = {!! json_encode($topInstructors->pluck('lecture_count')) !!};

        if (instLabels.length > 0) {
            var optionsBar = {
                series: [{
                    name: '{{ __('educational::messages.lectures') }}',
                    data: instCounts
                }],
                chart: {
                    type: 'bar',
                    height: 280,
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'inherit'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true,
                        distributed: true,
                        dataLabels: {
                            position: 'bottom'
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    textAnchor: 'start',
                    style: {
                        colors: ['#fff']
                    },
                    offsetX: 0,
                    dropShadow: {
                        enabled: true
                    }
                },
                stroke: {
                    width: 1,
                    colors: ['#fff']
                },
                xaxis: {
                    categories: instLabels
                },
                yaxis: {
                    labels: {
                        show: true
                    }
                },
                colors: ['#405189', '#0ab39c', '#f06548', '#f7b84b', '#299cdb']
            };
            new ApexCharts(document.querySelector("#instructor_load_chart"), optionsBar).render();
        } else {
            document.querySelector("#instructor_load_chart").innerHTML =
                '<div class="text-center text-muted py-4"><i class="ri-bar-chart-fill fs-1 d-block mb-2"></i> {{ __('educational::dashboard.no_lectures') }}</div>';
        }

        // 2. Status Overview Chart (Pie)
        var statusLabelsArray = {!! json_encode(array_keys($statusBreakdown)) !!};
        var statusValues = {!! json_encode(array_values($statusBreakdown)) !!};

        // Map labels to have better casing
        var statusLabels = statusLabelsArray.map(function(label) {
            return label.charAt(0).toUpperCase() + label.slice(1);
        });

        // Map colors based on status keys
        var colorMap = {
            'scheduled': '#299cdb',
            'running': '#405189',
            'completed': '#0ab39c',
            'cancelled': '#f06548',
            'rescheduled': '#f7b84b'
        };

        var chartColors = statusLabelsArray.map(function(status) {
            return colorMap[status] || '#878a99';
        });

        if (statusValues.length > 0) {
            var optionsPie = {
                series: statusValues,
                chart: {
                    type: 'donut',
                    height: 280,
                    fontFamily: 'inherit'
                },
                labels: statusLabels,
                legend: {
                    show: true,
                    position: 'bottom'
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return Math.round(val) + "%"
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                name: {
                                    show: true
                                },
                                value: {
                                    show: true
                                }
                            }
                        }
                    }
                },
                colors: chartColors
            };
            new ApexCharts(document.querySelector("#status_overview_chart"), optionsPie).render();
        } else {
            document.querySelector("#status_overview_chart").parentElement.innerHTML =
                '<div class="text-center text-muted py-4"><i class="ri-pie-chart-2-fill fs-1 d-block mb-2"></i> {{ __('educational::dashboard.no_lectures') }}</div>';
        }
    </script>
@endpush
