@extends('core::layouts.master')

@section('title', __('dashboard.dashboard'))

@section('content')
<!-- Critical Events Alert -->
<div id="criticalAlert" class="alert alert-danger alert-dismissible fade d-none" role="alert">
    <i class="ri-alert-line me-2"></i>
    <strong id="criticalCount">0</strong> {{ __('dashboard.critical_events') }}
    <a href="{{ route('audit.index') }}" class="alert-link">{{ __('dashboard.view_details') }}</a>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<!-- Stats Cards -->
<div class="row">
    <div class="col-xl-12">
        <div class="card crm-widget">
            <div class="card-body p-0">
                <div class="row row-cols-md-5 row-cols-1">
                    <!-- Total Users -->
                    <div class="col col-lg border-end">
                        <div class="py-4 px-3">
                            <div class="skeleton-box d-none">
                                <div class="skeleton-line mb-2" style="width: 60%;"></div>
                                <div class="skeleton-line" style="width: 40%;"></div>
                            </div>
                            <div class="stat-content">
                                <h5 class="text-muted text-uppercase fs-13">{{ __('dashboard.total_users') }} <i class="ri-user-line text-primary fs-18 float-end align-middle"></i></h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h4 class="mb-0"><span class="counter-value" data-target="0" id="totalUsers">0</span></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Completion (Phase 2) -->
                    @can('integrity_widget.view')
                    <div class="col col-lg border-end">
                        <div class="py-4 px-3">
                            <div class="skeleton-box d-none">
                                <div class="skeleton-line mb-2" style="width: 60%;"></div>
                                <div class="skeleton-line" style="width: 40%;"></div>
                            </div>
                            <div class="stat-content">
                                <h5 class="text-muted text-uppercase fs-13">
                                    {{ __('dashboard.profile_integrity') }} 
                                    <i class="ri-information-line text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('dashboard.profile_completion_tip') }}"></i>
                                    <i class="ri-shield-check-line text-warning fs-18 float-end align-middle"></i>
                                </h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h4 class="mb-0"><span id="profileCompletion">0</span>%</h4>
                                        <div class="progress progress-sm mt-2">
                                            <div id="profileProgress" class="progress-bar bg-warning" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endcan

                    <!-- Active Roles -->
                    <div class="col col-lg border-end">
                        <div class="py-4 px-3">
                            <div class="skeleton-box d-none">
                                <div class="skeleton-line mb-2" style="width: 60%;"></div>
                                <div class="skeleton-line" style="width: 40%;"></div>
                            </div>
                            <div class="stat-content">
                                <h5 class="text-muted text-uppercase fs-13">{{ __('dashboard.active_roles') }} <i class="ri-shield-user-line text-success fs-18 float-end align-middle"></i></h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h4 class="mb-0"><span class="counter-value" data-target="0" id="totalRoles">0</span></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Audit Logs (7 days) -->
                    <div class="col col-lg border-end">
                        <div class="py-4 px-3">
                            <div class="skeleton-box d-none">
                                <div class="skeleton-line mb-2" style="width: 60%;"></div>
                                <div class="skeleton-line" style="width: 40%;"></div>
                            </div>
                            <div class="stat-content">
                                <h5 class="text-muted text-uppercase fs-13">{{ __('dashboard.logs_7_days') }} <i class="ri-history-line text-info fs-18 float-end align-middle"></i></h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h4 class="mb-0"><span class="counter-value" data-target="0" id="totalLogs">0</span></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Health -->
                    <div class="col col-lg">
                        <div class="py-4 px-3">
                            <div class="skeleton-box d-none">
                                <div class="skeleton-line mb-2" style="width: 60%;"></div>
                                <div class="skeleton-line" style="width: 40%;"></div>
                            </div>
                            <div class="stat-content">
                                <h5 class="text-muted text-uppercase fs-13">{{ __('dashboard.system_health') }} <i class="ri-heart-pulse-line text-danger fs-18 float-end align-middle"></i></h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h4 class="mb-0"><span class="text-success" id="systemStatus">{{ __('dashboard.healthy') }}</span></h4>
                                        <p class="text-muted mb-0 small" id="dbSize">DB: 0 MB</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
@can('analytics.view')
<div class="row">
    <!-- Activity Trend Chart -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-0 align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('dashboard.activity_trend') }} ({{ __('dashboard.last_7_days') }})</h4>
            </div>
            <div class="card-body">
                <div id="activityChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- User Growth Chart (Phase 2) -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-0 align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('dashboard.user_growth') }} ({{ __('dashboard.last_6_months') }})</h4>
            </div>
            <div class="card-body">
                <div id="growthChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Category Distribution Chart -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header border-0 align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('dashboard.activity_by_module') }}</h4>
            </div>
            <div class="card-body">
                <div id="distributionChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>
@endcan
@endsection

@push('styles')
<style>
.skeleton-box {
    animation: pulse 1.5s ease-in-out infinite;
}
.skeleton-line {
    height: 20px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    border-radius: 4px;
}
@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    // Show skeleton loading
    document.querySelectorAll('.skeleton-box').forEach(el => el.classList.remove('d-none'));
    document.querySelectorAll('.stat-content').forEach(el => el.style.display = 'none');

    // Fetch dashboard metrics
    fetch('/api/dashboard/metrics', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        // Hide skeleton, show content
        document.querySelectorAll('.skeleton-box').forEach(el => el.classList.add('d-none'));
        document.querySelectorAll('.stat-content').forEach(el => el.style.display = 'block');

        // Update User Stats
        if (data.users && data.users.status === 'success') {
            animateCounter('totalUsers', data.users.data.total_users);
            animateCounter('totalRoles', data.users.data.total_roles);
        }

        // Update Advanced Analytics (Phase 2)
        @can('integrity_widget.view')
        if (data.analytics && data.analytics.status === 'success') {
            const completion = data.analytics.data.profile_completion;
            document.getElementById('profileCompletion').textContent = completion;
            document.getElementById('profileProgress').style.width = completion + '%';
            document.getElementById('profileProgress').setAttribute('aria-valuenow', completion);
            
            @can('analytics.view')
            renderGrowthChart(data.analytics.data.growth_trends);
            @endcan
        }
        @endcan

        // Update Audit Stats
        if (data.audit && data.audit.status === 'success') {
            animateCounter('totalLogs', data.audit.data.total_logs_last_7d);
            
            // Critical Events Alert
            const criticalCount = data.audit.data.critical_events_24h;
            if (criticalCount > 0) {
                document.getElementById('criticalCount').textContent = criticalCount;
                document.getElementById('criticalAlert').classList.add('show');
                document.getElementById('criticalAlert').classList.remove('d-none');
            }

            @can('analytics.view')
            // Analytics Charts
            renderActivityChart(data.audit.data.trend);
            renderDistributionChart(data.audit.data.distribution);
            @endcan
        }

        // Update System Health
        if (data.system && data.system.status === 'success') {
            document.getElementById('systemStatus').textContent = data.system.data.status.charAt(0).toUpperCase() + data.system.data.status.slice(1);
            document.getElementById('dbSize').textContent = `DB: ${data.system.data.database_size_mb} MB`;
        }
    })
    .catch(error => {
        console.error('Error loading dashboard metrics:', error);
        document.querySelectorAll('.skeleton-box').forEach(el => el.classList.add('d-none'));
        document.querySelectorAll('.stat-content').forEach(el => el.style.display = 'block');
    });

    function animateCounter(id, target) {
        const element = document.getElementById(id);
        if (!element) return;
        element.setAttribute('data-target', target);
        let current = 0;
        const increment = target / 20;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target;
                clearInterval(timer);
            } else {
                element.textContent = Math.ceil(current);
            }
        }, 50);
    }

    function renderActivityChart(trendData) {
        const options = {
            chart: { type: 'area', height: 300, toolbar: { show: false } },
            series: [{ name: 'Activities', data: trendData.series }],
            xaxis: { categories: trendData.labels },
            colors: ['#405189'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 }
        };
        new ApexCharts(document.querySelector("#activityChart"), options).render();
    }

    function renderGrowthChart(growthData) {
        const options = {
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            series: [{ name: 'New Users', data: growthData.series }],
            xaxis: { categories: growthData.labels },
            colors: ['#0ab39c'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
            dataLabels: { enabled: false },
        };
        new ApexCharts(document.querySelector("#growthChart"), options).render();
    }

    function renderDistributionChart(distData) {
        const options = {
            chart: { type: 'donut', height: 300 },
            series: distData.series,
            labels: distData.labels,
            colors: ['#405189', '#0ab39c', '#f06548', '#f7b84b', '#299cdb'],
            legend: { position: 'bottom' }
        };
        new ApexCharts(document.querySelector("#distributionChart"), options).render();
    }
});
</script>
@endpush