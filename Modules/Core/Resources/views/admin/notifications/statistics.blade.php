@extends('core::layouts.master')

@section('title', 'إحصائيات الإشعارات')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">إحصائيات الإشعارات (Statistics)</h4>
            <div class="page-title-right">
                <form action="{{ route('admin.notifications.statistics') }}" method="GET" class="d-flex align-items-center">
                    <label class="form-label me-2 mb-0">الفترة:</label>
                    <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="7" {{ $period == 7 ? 'selected' : '' }}>آخر 7 أيام</option>
                        <option value="30" {{ $period == 30 ? 'selected' : '' }}>آخر 30 يوم</option>
                        <option value="90" {{ $period == 90 ? 'selected' : '' }}>آخر 3 أشهر</option>
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
                <h4 class="card-title mb-0 flex-grow-1">الإشعارات اليومية</h4>
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
                <h4 class="card-title mb-0 flex-grow-1">التوزيع حسب الأهمية</h4>
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
                <h4 class="card-title mb-0 flex-grow-1">التوزيع حسب النوع</h4>
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
        datasets: [
            {
                label: 'الإشعارات الكلية',
                data: {!! json_encode($dailyStats->pluck('total')) !!},
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'الغير مقروءة',
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
                    text: 'التاريخ'
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'العدد'
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
                'rgba(75, 192, 192, 0.8)'  // Other
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
            label: 'عدد الإشعارات',
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
