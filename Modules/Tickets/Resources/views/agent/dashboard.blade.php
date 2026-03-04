@extends('core::layouts.master')

@section('title', 'لوحة تحكم التذاكر')

@section('css')
    <style>
        :root {
            --vz-card-bg-custom: #fff;
        }

        .crm-widget .card-body {
            padding: 1.25rem;
        }

        .avatar-title {
            border-radius: 0.6rem;
            transform: rotate(0deg);
            transition: all 0.3s ease;
        }

        .card-animate:hover .avatar-title {
            transform: rotate(15deg);
        }

        .progress-sm {
            height: 7px;
            border-radius: 10px;
        }

        .ff-secondary {
            font-family: 'Cairo', sans-serif !important;
        }

        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .chart-label-list {
            list-style: none;
            padding: 0;
        }

        .chart-label-list li {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .chart-label-list li i {
            margin-inline-end: 10px;
            font-size: 10px;
        }

        .leaderboard-card {
            transition: all 0.3s ease;
        }

        .leaderboard-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .range-pill {
            cursor: pointer;
            transition: all 0.2s;
        }

        .range-pill:hover {
            background-color: #405189 !important;
            color: white !important;
        }

        .range-pill.active {
            background-color: #405189 !important;
            color: white !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Dashboard Header -->
        <div class="row mt-3">
            <div class="col-12">
                <div
                    class="page-title-box d-sm-flex align-items-center justify-content-between bg-white p-3 rounded-4 shadow-sm mb-4">
                    <div>
                        <h4 class="mb-sm-0 fw-bold text-primary"><i class="ri-dashboard-fill me-1"></i> لوحة تحكم التذاكر
                            التحليلية</h4>
                        <p class="text-muted mb-0 fs-12">نظرة عامة وشاملة على مؤشرات الأداء</p>
                    </div>
                    <div class="page-title-right d-flex gap-2 align-items-center">
                        <span class="text-muted fs-11 fw-bold text-uppercase me-1">نطاق اللوحة:</span>
                        <div class="btn-group btn-group-sm">
                            <a href="?range=7"
                                class="btn btn-outline-primary {{ request('range', 30) == 7 ? 'active' : '' }}">أسبوع</a>
                            <a href="?range=30"
                                class="btn btn-outline-primary {{ request('range', 30) == 30 ? 'active' : '' }}">شعر</a>
                            <a href="?range=90"
                                class="btn btn-outline-primary {{ request('range', 30) == 90 ? 'active' : '' }}">3 أشهر</a>
                            <a href="?range=all"
                                class="btn btn-outline-primary {{ request('range') == 'all' ? 'active' : '' }}">الكل</a>
                        </div>
                        <a href="{{ route('agent.tickets.index') }}"
                            class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm ms-2">
                            <i class="ri-customer-service-2-line align-middle me-1"></i> العودة للمكتب
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body">
                        <p class="text-uppercase fw-bold fs-11 text-muted mb-0">إجمالي التذاكر</p>
                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <div>
                                <h2 class="fs-28 fw-bold ff-secondary mb-1 text-info">{{ number_format($stats['total']) }}
                                </h2><span class="text-primary fs-12 fw-medium">التذاكر في النظام</span>
                            </div>
                            <div class="avatar-lg">
                                <div class="avatar-title bg-soft-info text-info fs-2"><i class="ri-ticket-2-fill"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body">
                        <p class="text-uppercase fw-bold fs-11 text-muted mb-0">قيد المعالجة</p>
                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <div>
                                <h2 class="fs-28 fw-bold ff-secondary mb-1 text-warning">{{ number_format($stats['open']) }}
                                </h2><span class="text-warning fs-12 fw-medium">بانتظار الإجراء الفني</span>
                            </div>
                            <div class="avatar-lg">
                                <div class="avatar-title bg-soft-warning text-warning fs-2"><i class="ri-loader-4-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body">
                        <p class="text-uppercase fw-bold fs-11 text-muted mb-0">تجاوز الـ SLA</p>
                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <div>
                                <h2 class="fs-28 fw-bold ff-secondary mb-1 text-danger">
                                    {{ number_format($stats['overdue']) }}</h2><span
                                    class="text-danger fs-12 fw-medium">متجاوزة للموعد</span>
                            </div>
                            <div class="avatar-lg">
                                <div class="avatar-title bg-soft-danger text-danger fs-3"><i
                                        class="ri-error-warning-fill"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body">
                        <p class="text-uppercase fw-bold fs-11 text-muted mb-0">تم إغلاقها</p>
                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <div>
                                <h2 class="fs-28 fw-bold ff-secondary mb-1 text-success">
                                    {{ number_format($stats['closed']) }}</h2><span class="text-success fs-12 fw-medium">تم
                                    حلها بنجاح</span>
                            </div>
                            <div class="avatar-lg">
                                <div class="avatar-title bg-soft-success text-success fs-2"><i
                                        class="ri-checkbox-circle-fill"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header border-0 bg-transparent p-4 d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0 fw-bold">حجم الورود اليومي (آخر {{ $trendData['current_range'] }} يوم)
                        </h5>
                        <div class="dropdown">
                            <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">تحكم بالمدة</button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="?range={{ request('range', 30) }}&trend_range=7">آخر 7
                                        أيام</a></li>
                                <li><a class="dropdown-item" href="?range={{ request('range', 30) }}&trend_range=15">آخر 15
                                        يوم</a></li>
                                <li><a class="dropdown-item" href="?range={{ request('range', 30) }}&trend_range=30">آخر 30
                                        يوم</a></li>
                                <li><a class="dropdown-item" href="?range={{ request('range', 30) }}&trend_range=60">آخر
                                        شهرين</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div id="volume_chart_advanced" style="height: 380px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header border-0 bg-transparent p-4">
                        <h5 class="card-title mb-0 fw-bold">تحليل الحالات</h5>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div id="status_donut_advanced" style="height: 300px;"></div>
                        <div class="mt-4 px-2">
                            <ul class="chart-label-list mb-0">
                                @php $statusPaletteUI = ['#0ab39c', '#f7b84b', '#299cdb', '#405189', '#727cf5', '#f19072']; @endphp
                                @foreach ($statusDistribution as $sd)
                                    <li><i class="ri-checkbox-blank-circle-fill"
                                            style="color: {{ $statusPaletteUI[$loop->index % count($statusPaletteUI)] }}"></i><span
                                            class="flex-grow-1">{{ $sd->name }}</span><span
                                            class="fw-bold">{{ $sd->count }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Insights Grid -->
        <div class="row mt-4">
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header border-0 bg-transparent p-4">
                        <h5 class="card-title mb-0 fw-bold">أداء مراحل العمل</h5>
                    </div>
                    <div class="card-body p-4 pt-0">
                        @foreach ($stageStats as $stage)
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="fs-13 fw-bold mb-0 text-dark">{{ $stage['name'] }}</h6>
                                    <span class="badge bg-soft-info text-info">{{ $stage['count'] }}</span>
                                </div>
                                <div class="progress progress-sm rounded-pill bg-light">
                                    <div class="progress-bar rounded-pill bg-primary"
                                        style="width: {{ $stage['percentage'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header border-0 bg-transparent p-4">
                        <h5 class="card-title mb-0 fw-bold">أكثر 5 مسببات للشكاوى</h5>
                    </div>
                    <div class="card-body p-4 pt-0">
                        @foreach ($categoryDistribution as $cd)
                            <div
                                class="d-flex align-items-center p-2 rounded-3 bg-light bg-opacity-50 mb-2 border border-dashed leaderboard-card">
                                <div class="avatar-xs flex-shrink-0">
                                    <div class="avatar-title bg-soft-primary text-primary rounded-circle fs-12">
                                        {{ $loop->iteration }}</div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fs-13 fw-bold mb-0">{{ $cd->name }}</h6>
                                </div>
                                <div class="flex-shrink-0">@php $perc = ($stats['total'] > 0) ? round(($cd->count / $stats['total']) * 100, 1) : 0; @endphp <span
                                        class="badge bg-soft-secondary text-secondary">{{ $perc }}%</span></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header border-0 bg-transparent p-4 text-center pb-0">
                        <h5 class="card-title mb-0 fw-bold">توزيع الأولويات</h5>
                    </div>
                    <div class="card-body p-0 text-center">
                        <div id="priority_radar_chart" style="height: 280px;"></div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header border-0 bg-transparent p-4">
                        <h5 class="card-title mb-0 fw-bold">المجموعات الأكثر شكاوى (طلاب)</h5>
                    </div>
                    <div class="card-body p-4 pt-0">
                        @foreach ($eduGroupStats as $egrp)
                            <div
                                class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-soft-warning mb-2 border-start border-4 border-warning leaderboard-card">
                                <h6 class="fs-13 fw-bold mb-0 text-dark">{{ $egrp->name }}</h6>
                                <span class="badge bg-warning text-white rounded-pill">{{ $egrp->count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header border-0 bg-transparent p-4">
                        <h5 class="card-title mb-0 fw-bold">البرامج الأكثر شكاوى</h5>
                    </div>
                    <div class="card-body p-4 pt-0">
                        @foreach ($programStats as $prog)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <h6 class="fs-13 mb-1 fw-bold text-dark">{{ $prog->name }}</h6>
                                    <div class="progress progress-sm rounded-pill bg-light">
                                        @php $progPerc = ($stats['total'] > 0) ? ($prog->count / $stats['total']) * 100 : 0; @endphp
                                        <div class="progress-bar rounded-pill bg-success"
                                            style="width: {{ $progPerc }}%"></div>
                                    </div>
                                </div>
                                <div class="ms-3 text-end">
                                    <h6 class="mb-0 fw-bold text-success">{{ $prog->count }}</h6>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header border-0 bg-transparent p-4">
                        <h5 class="card-title mb-0 fw-bold">المجموعات الأكثر ضغطاً (دعم فني)</h5>
                    </div>
                    <div class="card-body p-4 pt-0">
                        @foreach ($supportGroupStats as $sgrp)
                            <div
                                class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-soft-primary mb-2 border-start border-4 border-primary leaderboard-card">
                                <h6 class="fs-13 fw-bold mb-0 text-primary">{{ $sgrp->name }}</h6>
                                <span class="badge bg-primary rounded-pill">{{ $sgrp->count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Transactions -->
        <div class="row mt-4 mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header border-0 bg-transparent p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title mb-0 fw-bold">أحدث التذاكر المضافة</h5>
                            <p class="text-muted mb-0 fs-12 mt-1">قائمة التفاصل السريعة لآخر تذاكر تم تسجيلها ومتابعتها</p>
                        </div>
                        <a href="{{ route('agent.tickets.index') }}"
                            class="btn btn-soft-primary btn-sm rounded-pill fw-bold px-3">
                            عرض الكل <i class="ri-arrow-left-line align-middle ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light bg-opacity-50 text-muted">
                                    <tr class="fs-11 text-uppercase">
                                        <th class="ps-4 border-0">رقم التذكرة والموضوع</th>
                                        <th class="border-0">بواسطة</th>
                                        <th class="border-0">التصنيف</th>
                                        <th class="border-0">تاريخ الإضافة</th>
                                        <th class="pe-4 text-end border-0">إجراء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentTickets as $ticket)
                                        <tr class="fs-13 border-bottom border-light">
                                            <td class="ps-4 py-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-2">
                                                        <i class="ri-checkbox-blank-circle-fill text-primary"
                                                            style="font-size: 8px;"></i>
                                                    </div>
                                                    <div>
                                                        <a href="{{ route('agent.tickets.show', $ticket->uuid) }}"
                                                            class="text-dark fw-bold mb-0">#{{ $ticket->ticket_number }}</a>
                                                        <p class="text-muted mb-0 fs-12 text-truncate"
                                                            style="max-width: 250px;">{{ $ticket->subject }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">
                                                    {{ $ticket->user->full_name ?? 'غير معروف' }}</div>
                                                <div class="text-muted fs-11">{{ $ticket->user->email ?? '' }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-soft-secondary text-secondary rounded-pill px-2">
                                                    {{ $ticket->category->name ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="text-muted fs-12">
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-time-line me-1 text-primary"></i>
                                                    {{ $ticket->created_at->diffForHumans() }}
                                                </div>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <a href="{{ route('agent.tickets.show', $ticket->uuid) }}"
                                                    class="btn btn-sm btn-icon btn-soft-primary rounded-pill shadow-none">
                                                    <i class="ri-eye-line fs-14"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="text-center">
                                                    <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                                        colors="primary:#405189,secondary:#0ab39c"
                                                        style="width:75px;height:75px"></lord-icon>
                                                    <h6 class="mt-2 text-muted">لا توجد بيانات متاحة حالياً</h6>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const trendData = @json($trendData);
            if (document.querySelector("#volume_chart_advanced")) {
                new ApexCharts(document.querySelector("#volume_chart_advanced"), {
                    series: [{
                        name: 'عدد الورود',
                        data: trendData.values
                    }],
                    chart: {
                        type: 'area',
                        height: 380,
                        toolbar: {
                            show: false
                        }
                    },
                    colors: ['#0ab39c'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.6,
                            opacityTo: 0.1
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 4
                    },
                    dataLabels: {
                        enabled: true,
                        style: {
                            fontSize: '10px'
                        }
                    },
                    xaxis: {
                        categories: trendData.labels
                    }
                }).render();
            }

            const statusCounts = @json($statusDistribution->pluck('count'));
            const statusLabels = @json($statusDistribution->pluck('name'));
            if (document.querySelector("#status_donut_advanced")) {
                new ApexCharts(document.querySelector("#status_donut_advanced"), {
                    series: statusCounts,
                    chart: {
                        type: 'donut',
                        height: 300
                    },
                    labels: statusLabels,
                    colors: ['#0ab39c', '#f7b84b', '#299cdb', '#405189', '#727cf5', '#f19072'],
                    legend: {
                        show: false
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '80%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'الإجمالي',
                                        formatter: () => {{ $stats['total'] }}
                                    }
                                }
                            }
                        }
                    }
                }).render();
            }

            const priorityCounts = @json($priorityDistribution->pluck('count'));
            const priorityLabels = @json($priorityDistribution->pluck('name'));
            if (document.querySelector("#priority_radar_chart")) {
                new ApexCharts(document.querySelector("#priority_radar_chart"), {
                    series: priorityCounts,
                    chart: {
                        type: 'polarArea',
                        height: 280,
                        toolbar: {
                            show: false
                        }
                    },
                    labels: priorityLabels,
                    colors: ['#f06548', '#ff7f41', '#f7b84b', '#0ab39c', '#299cdb'],
                    fill: {
                        opacity: 0.8
                    },
                    legend: {
                        show: false
                    },
                    yaxis: {
                        show: false
                    }
                }).render();
            }
        });
    </script>
@endpush
