@extends('core::layouts.master')
@section('title', 'نظرة عامة قيادية — المركز التعليمي')

@push('styles')
    <style>
        .kpi-title {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #878a99;
            font-weight: 600;
            margin-bottom: .3rem;
        }

        .card-animate {
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .card-animate:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, .08) !important;
        }

        .progress-sm {
            height: 5px;
            border-radius: 4px;
        }

        .progress-xs {
            height: 3px;
            border-radius: 4px;
        }

        .side-stat-item {
            border-radius: 8px;
            padding: 10px 14px;
            background: #f8f9fa;
            border: 1px solid rgba(0, 0, 0, .05);
        }

        .table-room-header {
            background: #f3f6f9;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        {{-- ══ Page Title ══════════════════════════════════════════════════════════ --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1 fw-bold">نظرة عامة قيادية</h4>
                        <p class="text-muted mb-0">لوحة إحصائيات متكاملة لدعم القرار وتحسين أداء التعليم</p>
                    </div>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('educational.dashboard') }}">المركز التعليمي</a>
                            </li>
                            <li class="breadcrumb-item active">نظرة عامة قيادية</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ Date Filter ════════════════════════════════════════════════════════ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form action="{{ route('educational.overview') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label text-muted fw-semibold fs-13 mb-1">
                            <i class="ri-calendar-event-line me-1"></i>من تاريخ
                        </label>
                        <input type="date" name="date_from" class="form-control bg-light border-0"
                            value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted fw-semibold fs-13 mb-1">
                            <i class="ri-calendar-check-line me-1"></i>إلى تاريخ
                        </label>
                        <input type="date" name="date_to" class="form-control bg-light border-0"
                            value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary fw-bold">
                                <i class="ri-bar-chart-2-line me-1"></i> عرض الإحصائيات
                            </button>
                            @foreach ([['اليوم', now()->format('Y-m-d'), now()->format('Y-m-d')], ['أسبوع', now()->subDays(6)->format('Y-m-d'), now()->format('Y-m-d')], ['شهر', now()->subDays(29)->format('Y-m-d'), now()->format('Y-m-d')]] as [$label, $f, $t])
                                <a href="{{ route('educational.overview', ['date_from' => $f, 'date_to' => $t]) }}"
                                    class="btn btn-soft-primary">{{ $label }}</a>
                            @endforeach
                            <span class="ms-auto text-muted small align-self-center">
                                <i
                                    class="ri-time-line me-1"></i>{{ \Carbon\Carbon::now()->translatedFormat('l، d M Y — H:i') }}
                            </span>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ══ KPI Bar (8 cards) ══════════════════════════════════════════════════ --}}
        <div class="row g-3 mb-4">
            @php
                $kpiRows = [
                    [
                        'الحضور',
                        $presentCount,
                        $presentRate,
                        'success',
                        'ri-user-follow-line',
                        'من إجمالي ' . number_format($totalEligible) . ' مؤهل',
                    ],
                    ['الغياب', $absentCount, $absentRate, 'danger', 'ri-user-unfollow-line', 'غائب عن المحاضرات'],
                    ['التأخير', $lateCount, $lateRate, 'warning', 'ri-time-line', 'سجّلوا حضوراً متأخراً'],
                    ['الأعذار', $excusedCount, $excusedRate, 'info', 'ri-file-text-line', 'غياب بعذر مقبول'],
                    ['إجمالي المحاضرات', $totalLectures, null, 'primary', 'ri-book-open-line', 'في الفترة المختارة'],
                    ['المتدربون المؤهلون', $totalEligible, null, 'success', 'ri-group-line', 'متدرب مؤهل'],
                    [
                        'تقييمات مُرسَلة',
                        $evalStat?->submissions ?? 0,
                        null,
                        'info',
                        'ri-star-line',
                        'إجمالي التقييمات المُرسَلة',
                    ],
                    [
                        'معدل التقييمات',
                        (float) ($evalStat?->completion_rate ?? 0),
                        null,
                        'warning',
                        'ri-percent-line',
                        'نسبة إتمام التقييمات',
                    ],
                ];
            @endphp
            @foreach ($kpiRows as $i => [$lbl, $val, $rate, $color, $icon, $sub])
                <div class="col-xl-3 col-sm-6">
                    @if ($rate !== null)
                        {{-- With progress bar --}}
                        <div class="card card-animate border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="kpi-title mb-0">{{ $lbl }}</p>
                                    <span
                                        class="badge bg-{{ $color }}-subtle text-{{ $color }} fs-11">{{ $rate }}%</span>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-3">
                                    <div>
                                        <h4 class="mb-1 fw-bold">{{ number_format($val) }}</h4>
                                        <p class="text-muted mb-0 fs-12">{{ $sub }}</p>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-{{ $color }}-subtle rounded fs-3">
                                            <i class="{{ $icon }} text-{{ $color }}"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-{{ $color }}"
                                            style="width:{{ min($rate, 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Border-start style --}}
                        <div class="card border-0 shadow-sm border-start border-3 border-{{ $color }}">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm flex-shrink-0 me-3">
                                        <div
                                            class="avatar-title bg-{{ $color }}-subtle text-{{ $color }} rounded-circle fs-4">
                                            <i class="{{ $icon }}"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-muted mb-1 fs-12 fw-semibold text-truncate">{{ $lbl }}</p>
                                        @if ($i === 7)
                                            <h5 class="mb-0 fw-bold">{{ number_format($val, 1) }}%</h5>
                                        @else
                                            <h5 class="mb-0 fw-bold">{{ number_format($val) }}</h5>
                                        @endif
                                        <p class="text-muted mb-0 fs-11">{{ $sub }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- ══════════════════════════════════════════════════════════════════════
         MAIN LAYOUT: col-4 (left sidebar) | col-8 (right main)
    ══════════════════════════════════════════════════════════════════════ --}}
        <div class="row g-3">

            {{-- ╔═══════════════════════════════════════════════════════════════╗
             ║  LEFT COLUMN  (col-4)                                        ║
             ╚═══════════════════════════════════════════════════════════════╝ --}}
            <div class="col-xl-4">

                {{-- ── 1. محاضرات اليوم ───────────────────────────────────── --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-transparent pb-0 pt-3">
                        <h5 class="card-title mb-0 fw-bold fs-14">
                            <i class="ri-calendar-todo-line text-warning me-1"></i>محاضرات اليوم
                        </h5>
                        <p class="text-muted mb-0 fs-12">{{ \Carbon\Carbon::parse($today)->translatedFormat('d F Y') }}</p>
                    </div>
                    <div class="card-body pb-2">
                        @php
                            $ts = $todayStatus;
                            $tsTotal = max((int) ($ts?->total ?? 0), 1);
                            $todayRows = [
                                ['مجدولة', (int) ($ts?->scheduled ?? 0), 'info'],
                                ['جارية الآن', (int) ($ts?->running ?? 0), 'danger'],
                                ['مكتملة', (int) ($ts?->completed ?? 0), 'success'],
                                ['ملغية', (int) ($ts?->cancelled ?? 0), 'secondary'],
                                ['مؤجلة', (int) ($ts?->rescheduled ?? 0), 'warning'],
                            ];
                        @endphp
                        <div class="d-flex flex-column gap-2">
                            @foreach ($todayRows as [$lbl, $cnt, $clr])
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-xs flex-shrink-0">
                                        <span class="avatar-title bg-{{ $clr }}-subtle rounded-circle">
                                            <i class="ri-checkbox-blank-circle-fill text-{{ $clr }} fs-10"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fs-13 fw-medium">{{ $lbl }}</span>
                                            <span
                                                class="fs-13 fw-bold text-{{ $clr }}">{{ $cnt }}</span>
                                        </div>
                                        <div class="progress progress-xs">
                                            <div class="progress-bar bg-{{ $clr }}"
                                                style="width:{{ round(($cnt / $tsTotal) * 100) }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Running Now --}}
                        @if (count($runningNow) > 0)
                            <hr class="my-2">
                            <p class="fs-12 fw-bold text-danger mb-2">
                                <span class="badge bg-danger me-1 rounded-pill px-2 py-1">●
                                    LIVE</span>{{ count($runningNow) }} تعمل الآن
                            </p>
                            <div class="d-flex flex-column gap-2">
                                @foreach (collect($runningNow)->take(3) as $lec)
                                    <div class="p-2 rounded" style="background:#fff5f5;border:1px solid #fde8e8">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="overflow-hidden me-2">
                                                <div class="fw-semibold fs-12 text-truncate">
                                                    {{ $lec->program?->name ?? '—' }}</div>
                                                <div class="text-muted fs-11">{{ $lec->group?->name }}</div>
                                                <div class="text-muted fs-11">
                                                    <i
                                                        class="ri-time-line me-1"></i>{{ $lec->starts_at->format('H:i') }}–{{ $lec->ends_at->format('H:i') }}
                                                </div>
                                            </div>
                                            <div class="text-end flex-shrink-0">
                                                <span class="badge bg-success-subtle text-success fs-11 d-block mb-1">
                                                    {{ $lec->attendances_count }}/{{ $lec->trainees_count }}
                                                </span>
                                                <span
                                                    class="badge bg-light text-muted fs-11">{{ $lec->room?->name ?? '—' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @if (count($runningNow) > 3)
                                    <p class="text-muted fs-12 text-center mb-0">و {{ count($runningNow) - 3 }} محاضرات
                                        أخرى...</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ── 2. محاضرات اليوم حسب نوع الجلسة ──────────────────── --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-transparent pb-0 pt-3">
                        <h5 class="card-title mb-0 fw-bold fs-14">
                            <i class="ri-pie-chart-2-line text-info me-1"></i>حسب نوع الجلسة
                        </h5>
                        <p class="text-muted mb-0 fs-12">توزيع محاضرات اليوم</p>
                    </div>
                    <div class="card-body">
                        <div id="sessionTypeChart" class="apex-charts" dir="ltr" style="min-height:200px"></div>
                        @if (!empty($lecturesBySessionType))
                            <div class="mt-2 d-flex flex-column gap-2">
                                @php $stColors2 = ['#405189','#0ab39c','#f06548','#f7b84b','#299cdb','#e67e22','#7c6bff','#27ae60']; @endphp
                                @foreach ($lecturesBySessionType as $idx => $row)
                                    @php $c = $stColors2[$idx % count($stColors2)]; @endphp
                                    <div class="d-flex align-items-center gap-2">
                                        <span
                                            style="width:8px;height:8px;border-radius:50%;background:{{ $c }};flex-shrink:0;display:inline-block"></span>
                                        <span class="fs-12 flex-grow-1 text-truncate">{{ $row->session_type }}</span>
                                        <span class="badge fs-11 fw-bold px-2"
                                            style="background:{{ $c }}20;color:{{ $c }}">{{ $row->lecture_count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted text-center py-2 fs-12">لا توجد بيانات</p>
                        @endif
                    </div>
                </div>

                {{-- ── 3. محاضرات اليوم حسب شركة التدريب ─────────────────── --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-transparent pb-0 pt-3">
                        <h5 class="card-title mb-0 fw-bold fs-14">
                            <i class="ri-building-2-line text-warning me-1"></i>حسب شركة التدريب
                        </h5>
                        <p class="text-muted mb-0 fs-12">توزيع محاضرات اليوم على الشركات</p>
                    </div>
                    <div class="card-body">
                        @forelse($lecturesByCompany as $row)
                            @php $maxC = collect($lecturesByCompany)->max('lecture_count') ?: 1; @endphp
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span
                                            class="fs-12 fw-semibold text-truncate">{{ $row->company_name ?? 'غير محدد' }}</span>
                                        <span class="fs-12 fw-bold ms-2 flex-shrink-0">{{ $row->lecture_count }}</span>
                                    </div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-warning"
                                            style="width:{{ round(($row->lecture_count / $maxC) * 100) }}%"></div>
                                    </div>
                                    <div class="d-flex gap-1 mt-1">
                                        <span class="badge bg-success-subtle text-success fs-10">{{ $row->present }}
                                            حضور</span>
                                        <span class="badge bg-danger-subtle text-danger fs-10">{{ $row->absent }}
                                            غياب</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-2 fs-12">لا توجد بيانات</p>
                        @endforelse
                    </div>
                </div>

                {{-- ── 4. أعلى مجموعات الغياب ─────────────────────────────── --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-transparent pb-0 pt-3">
                        <h5 class="card-title mb-0 fw-bold fs-14">
                            <i class="ri-alarm-warning-line text-danger me-1"></i>أعلى مجموعات الغياب
                        </h5>
                        <p class="text-muted mb-0 fs-12">في الفترة المختارة</p>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-borderless table-sm align-middle mb-0">
                                <thead class="text-muted table-light" style="font-size:11px">
                                    <tr>
                                        <th class="ps-3 fw-semibold">المجموعة</th>
                                        <th class="fw-semibold text-center">غياب</th>
                                        <th class="fw-semibold text-center">النسبة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topAbsentGroups as $idx => $row)
                                        @php
                                            $r = (float) $row->absence_rate;
                                            $rc = $r >= 50 ? 'danger' : ($r >= 25 ? 'warning' : 'secondary');
                                        @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-semibold fs-12">{{ $row->group_name }}</div>
                                                <div class="text-muted fs-11">{{ $row->program_name }}</div>
                                            </td>
                                            <td class="text-center fw-bold text-danger fs-12">{{ $row->absent_count }}
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-{{ $rc }}-subtle text-{{ $rc }} rounded-pill fs-11">{{ $r }}%</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-3 text-muted fs-12">
                                                <i class="ri-check-double-line text-success d-block fs-4 mb-1"></i>لا يوجد
                                                غياب 🎉
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ── 5. استخدام القاعات اليوم ───────────────────────────── --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-transparent pb-0 pt-3">
                        <h5 class="card-title mb-0 fw-bold fs-14">
                            <i class="ri-map-pin-2-line text-primary me-1"></i>استخدام القاعات اليوم
                        </h5>
                        <p class="text-muted mb-0 fs-12">أكثر القاعات نشاطاً</p>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-borderless table-sm align-middle mb-0">
                                <thead class="text-muted table-light" style="font-size:11px">
                                    <tr>
                                        <th class="ps-3 fw-semibold">القاعة</th>
                                        <th class="fw-semibold text-center">محاضرات</th>
                                        <th class="fw-semibold text-center">ساعات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roomUtilization as $idx => $row)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-semibold fs-12">{{ $row->room_name }}</div>
                                                <div class="text-muted fs-11">
                                                    {{ $row->building_name }}/{{ $row->floor_name }}</div>
                                            </td>
                                            <td class="text-center fw-bold fs-13">{{ $row->lectures_today }}</td>
                                            <td class="text-center text-muted fs-12">{{ $row->total_hours }}س</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-3 text-muted fs-12">لا توجد بيانات
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>{{-- /col-4 --}}

            {{-- ╔═══════════════════════════════════════════════════════════════╗
             ║  RIGHT COLUMN  (col-8)                                       ║
             ╚═══════════════════════════════════════════════════════════════╝ --}}
            <div class="col-xl-8">

                {{-- ── 1. مسار الحضور والغياب ──────────────────────────────── --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div
                        class="card-header border-0 bg-transparent pb-0 pt-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title mb-0 fw-bold fs-14">
                                <i class="ri-line-chart-line text-primary me-1"></i>مسار الحضور والغياب
                            </h5>
                            <p class="text-muted mb-0 fs-12">
                                من {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }}
                                إلى {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach ([['الحضور', 'success'], ['الغياب', 'danger'], ['التأخير', 'warning'], ['الأعذار', 'info']] as [$l, $c])
                                <span
                                    class="badge bg-{{ $c }}-subtle text-{{ $c }} fw-normal fs-11">
                                    <i class="ri-checkbox-blank-circle-fill me-1 fs-10"></i>{{ $l }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        <div id="trendChart" class="apex-charts" dir="ltr" style="min-height:300px"></div>
                    </div>
                </div>

                {{-- ── 2. إحصائيات التقييمات ───────────────────────────────── --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 bg-transparent pb-0 pt-3">
                        <h5 class="card-title mb-0 fw-bold fs-14">
                            <i class="ri-star-line text-success me-1"></i>إحصائيات التقييمات
                        </h5>
                        <p class="text-muted mb-0 fs-12">معدل إتمام التقييمات في الفترة المختارة</p>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center g-3">
                            <div class="col-sm-4 text-center">
                                <div id="evalDonut" class="apex-charts" dir="ltr" style="min-height:160px"></div>
                            </div>
                            <div class="col-sm-8">
                                <div class="row g-2 mb-3">
                                    @php
                                        $evalNumbers = [
                                            [$evalStat?->assignments ?? 0, 'تقييمات مُعيَّنة', 'primary'],
                                            [$evalStat?->submissions ?? 0, 'تقييمات مُرسَلة', 'success'],
                                            [
                                                ($evalStat?->assignments ?? 0) - ($evalStat?->submissions ?? 0),
                                                'تقييمات معلقة',
                                                'danger',
                                            ],
                                        ];
                                    @endphp
                                    @foreach ($evalNumbers as [$num, $lbl, $clr])
                                        <div class="col-4 text-center">
                                            <h5 class="fw-bold text-{{ $clr }} mb-1">{{ number_format($num) }}
                                            </h5>
                                            <p class="text-muted fs-12 mb-0">{{ $lbl }}</p>
                                        </div>
                                    @endforeach
                                </div>
                                @php $eRate = (float)($evalStat?->completion_rate ?? 0); @endphp
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted fs-13">معدل الإتمام</span>
                                    <span class="fw-bold text-success fs-13">{{ number_format($eRate, 1) }}%</span>
                                </div>
                                <div class="progress mb-2" style="height:8px">
                                    <div class="progress-bar bg-success" style="width:{{ min($eRate, 100) }}%"></div>
                                </div>
                                <div
                                    class="alert alert-soft-{{ $eRate >= 70 ? 'success' : ($eRate >= 30 ? 'warning' : 'danger') }} p-2 mb-0 fs-12">
                                    <i class="ri-information-line me-1"></i>
                                    @if ($eRate < 30)
                                        نسبة الإتمام منخفضة جداً. راجع إجراءات تفعيل التقييمات مع القائمين.
                                    @elseif($eRate < 70)
                                        هناك تقييمات معلقة. ذكّر المتدربين والمشرفين بإتمامها.
                                    @else
                                        نسبة إتمام ممتازة! استمر في متابعة الجودة.
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── 3. تفاصيل محاضرات اليوم (مُجمَّعة حسب القاعة) ────────── --}}
                <div class="card border-0 shadow-sm">
                    {{-- Card Header --}}
                    <div class="card-header border-0 bg-transparent pt-3 pb-2">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                            <div>
                                <h5 class="card-title mb-0 fw-bold fs-14">
                                    <i class="ri-table-line text-primary me-1"></i>تفاصيل محاضرات اليوم
                                </h5>
                                <p class="text-muted mb-0 fs-12">مُجمَّعة حسب القاعة — حضور / غياب / تأخير / أعذار /
                                    تقييمات</p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 fs-12"
                                    id="lectureCount">
                                    {{ count($todayLecturesDetail) }} محاضرة
                                </span>
                                {{-- Export Button --}}
                                <a id="exportBtn" href="{{ route('educational.overview.export') }}"
                                    class="btn btn-success btn-sm fw-semibold" target="_blank">
                                    <i class="ri-file-excel-2-line me-1"></i>تصدير Excel
                                </a>
                            </div>
                        </div>

                        {{-- ── Filter Bar ──────────────────────────────────────── --}}
                        <div class="row g-2 align-items-end" id="detailFilterBar">
                            <div class="col-sm-2">
                                <label class="form-label text-muted fw-semibold fs-11 mb-1">
                                    <i class="ri-calendar-line me-1"></i>التاريخ
                                </label>
                                <input type="date" class="form-control form-control-sm bg-light border-0"
                                    id="filterDate" value="{{ $today }}">
                            </div>
                            <div class="col-sm-2">
                                <label class="form-label text-muted fw-semibold fs-11 mb-1">
                                    <i class="ri-map-pin-2-line me-1"></i>القاعة
                                </label>
                                <select class="form-select form-select-sm bg-light border-0" id="filterRoom">
                                    <option value="">كل القاعات</option>
                                    @foreach ($filterRooms as $roomOpt)
                                        <option value="{{ $roomOpt }}">{{ $roomOpt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label text-muted fw-semibold fs-11 mb-1">
                                    <i class="ri-graduation-cap-line me-1"></i>البرنامج
                                </label>
                                <select class="form-select form-select-sm bg-light border-0" id="filterProgram">
                                    <option value="">كل البرامج</option>
                                    @foreach ($filterPrograms as $prog)
                                        <option value="{{ $prog }}">{{ $prog }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <label class="form-label text-muted fw-semibold fs-11 mb-1">
                                    <i class="ri-layout-grid-line me-1"></i>نوع الجلسة
                                </label>
                                <select class="form-select form-select-sm bg-light border-0" id="filterSessionType">
                                    <option value="">كل الأنواع</option>
                                    @foreach ($filterSessionTypes as $st)
                                        <option value="{{ $st }}">{{ $st }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <label class="form-label text-muted fw-semibold fs-11 mb-1">
                                    <i class="ri-checkbox-circle-line me-1"></i>الحالة
                                </label>
                                <select class="form-select form-select-sm bg-light border-0" id="filterStatus">
                                    <option value="">كل الحالات</option>
                                    @php $statusLabels = ['scheduled'=>'مجدول','running'=>'جارٍ','completed'=>'مكتمل','cancelled'=>'ملغى','rescheduled'=>'مؤجل']; @endphp
                                    @foreach ($filterStatuses as $st)
                                        <option value="{{ $st }}">{{ $statusLabels[$st] ?? $st }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-1">
                                <button type="button" class="btn btn-soft-secondary btn-sm w-100" id="resetFilters">
                                    <i class="ri-refresh-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0" id="lectureDetailBody">
                        @include('modules.educational.overview._lectures_list')
                    </div>
                </div>

            </div>{{-- /col-8 --}}
        </div>{{-- /row main --}}
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        // ── Colour palette ──────────────────────────────────────────────────────────
        var VZ = {
            primary: '#405189',
            success: '#0ab39c',
            danger: '#f06548',
            warning: '#f7b84b',
            info: '#299cdb'
        };
        var PALETTE = ['#405189', '#0ab39c', '#f06548', '#f7b84b', '#299cdb', '#e67e22', '#7c6bff', '#27ae60'];

        // ── 1. Trend Chart ──────────────────────────────────────────────────────────
        var tLabels = @json($trendLabels);
        var tPresent = @json($trendPresent);
        var tAbsent = @json($trendAbsent);
        var tLate = @json($trendLate);
        var tExcused = @json($trendExcused);

        if (tLabels.length > 0) {
            new ApexCharts(document.querySelector('#trendChart'), {
                series: [{
                        name: 'حضور',
                        data: tPresent
                    },
                    {
                        name: 'غياب',
                        data: tAbsent
                    },
                    {
                        name: 'تأخير',
                        data: tLate
                    },
                    {
                        name: 'أعذار',
                        data: tExcused
                    },
                ],
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'inherit'
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: .4,
                        opacityTo: .05
                    }
                },
                xaxis: {
                    categories: tLabels,
                    labels: {
                        rotate: -30,
                        style: {
                            fontSize: '11px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: v => Math.round(v)
                    }
                },
                colors: [VZ.success, VZ.danger, VZ.warning, VZ.info],
                legend: {
                    show: false
                },
                grid: {
                    borderColor: '#f1f1f1'
                },
                tooltip: {
                    y: {
                        formatter: v => v + ' متدرب'
                    }
                },
            }).render();
        } else {
            document.querySelector('#trendChart').innerHTML =
                '<div class="text-center text-muted py-5"><i class="ri-line-chart-line fs-1 d-block mb-2"></i>لا توجد بيانات في الفترة المختارة</div>';
        }

        // ── 2. Session Type Donut ───────────────────────────────────────────────────
        var stData = @json(collect($lecturesBySessionType)->map(fn($r) => ['name' => $r->session_type, 'count' => (int) $r->lecture_count]));
        if (stData.length > 0) {
            new ApexCharts(document.querySelector('#sessionTypeChart'), {
                series: stData.map(r => r.count),
                labels: stData.map(r => r.name),
                chart: {
                    type: 'donut',
                    height: 200,
                    fontFamily: 'inherit'
                },
                legend: {
                    show: false
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'إجمالي',
                                    formatter: () => stData.reduce((a, r) => a + r.count, 0)
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                colors: PALETTE,
            }).render();
        } else {
            document.querySelector('#sessionTypeChart').innerHTML =
                '<div class="text-center text-muted py-4 fs-12"><i class="ri-pie-chart-2-fill fs-2 d-block mb-1"></i>لا توجد بيانات</div>';
        }

        // ── 3. Eval Donut ───────────────────────────────────────────────────────────
        var eRate = {{ (float) ($evalStat?->completion_rate ?? 0) }};
        new ApexCharts(document.querySelector('#evalDonut'), {
            series: [eRate, Math.max(0, 100 - eRate)],
            labels: ['مُكتمَل', 'معلق'],
            chart: {
                type: 'donut',
                height: 160,
                fontFamily: 'inherit'
            },
            legend: {
                show: false
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'الإتمام',
                                formatter: () => eRate.toFixed(1) + '%'
                            }
                        }
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            colors: [VZ.success, '#e9ebec'],
            tooltip: {
                y: {
                    formatter: v => v.toFixed(1) + '%'
                }
            },
        }).render();

        // ── Lecture Detail Filter ──────────────────────────────────────────────────
        (function() {
            var baseExportUrl = '{{ route('educational.overview.export') }}';

            function getFilters() {
                return {
                    date: document.getElementById('filterDate').value,
                    room: document.getElementById('filterRoom').value,
                    program: document.getElementById('filterProgram').value,
                    session_type: document.getElementById('filterSessionType').value,
                    status: document.getElementById('filterStatus').value,
                };
            }

            function applyFilters() {
                var f = getFilters();
                var roomGroups = document.querySelectorAll('.room-group');
                var totalVisible = 0;

                roomGroups.forEach(function(group) {
                    var roomName = group.getAttribute('data-room') || '';
                    var rows = group.querySelectorAll('.lecture-row');
                    var visibleInRoom = 0;

                    rows.forEach(function(row) {
                        var match =
                            (!f.room || row.getAttribute('data-room') === f.room) &&
                            (!f.program || row.getAttribute('data-program') === f.program) &&
                            (!f.session_type || row.getAttribute('data-session-type') === f
                                .session_type) &&
                            (!f.status || row.getAttribute('data-status') === f.status);
                        row.style.display = match ? '' : 'none';
                        if (match) visibleInRoom++;
                    });

                    // Toggle whole room group
                    var showGroup = (!f.room || roomName === f.room) && visibleInRoom > 0;
                    group.style.display = showGroup ? '' : 'none';

                    // Update room count badge
                    var badge = group.querySelector('.room-count');
                    if (badge) badge.textContent = visibleInRoom + ' محاضرات';

                    totalVisible += visibleInRoom;
                });

                // Update header badge
                var countBadge = document.getElementById('lectureCount');
                if (countBadge) countBadge.textContent = totalVisible + ' محاضرة';

                // Update export URL with current filters
                var params = new URLSearchParams();
                if (f.date) params.set('filter_date', f.date);
                if (f.room) params.set('room', f.room);
                if (f.program) params.set('program', f.program);
                if (f.session_type) params.set('session_type', f.session_type);
                if (f.status) params.set('status', f.status);

                var exportBtn = document.getElementById('exportBtn');
                if (exportBtn) {
                    exportBtn.href = baseExportUrl + (params.toString() ? '?' + params.toString() : '');
                }
            }

            // Bind events
            ['filterRoom', 'filterProgram', 'filterSessionType', 'filterStatus'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) el.addEventListener('change', applyFilters);
            });

            var filterDateEl = document.getElementById('filterDate');
            if (filterDateEl) {
                filterDateEl.addEventListener('change', function() {
                    var dateVal = this.value;
                    var detailBody = document.getElementById('lectureDetailBody');
                    detailBody.style.opacity = '0.5';

                    fetch(window.location.pathname + '?filter_date=' + dateVal + '&fetch_lectures=1', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            detailBody.innerHTML = data.html;
                            detailBody.style.opacity = '1';

                            // Update select options
                            var fRooms = data.filters.rooms || [];
                            var fProgs = data.filters.programs || [];
                            var fSess = data.filters.sessionTypes || [];
                            var fStats = data.filters.statuses || [];

                            function updateSelect(id, options, defaultText, labelsMap) {
                                var el = document.getElementById(id);
                                if (!el) return;
                                var currentVal = el.value;
                                el.innerHTML = '<option value="">' + defaultText + '</option>';
                                options.forEach(function(opt) {
                                    var selected = (opt == currentVal) ? 'selected' : '';
                                    var text = (labelsMap && labelsMap[opt]) ? labelsMap[opt] : opt;
                                    el.innerHTML += '<option value="' + opt + '" ' + selected +
                                        '>' + text + '</option>';
                                });
                            }

                            var statLabels = {
                                'scheduled': 'مجدول',
                                'running': 'جارٍ',
                                'completed': 'مكتمل',
                                'cancelled': 'ملغى',
                                'rescheduled': 'مؤجل'
                            };

                            updateSelect('filterRoom', fRooms, 'كل القاعات');
                            updateSelect('filterProgram', fProgs, 'كل البرامج');
                            updateSelect('filterSessionType', fSess, 'كل الأنواع');
                            updateSelect('filterStatus', fStats, 'كل الحالات', statLabels);

                            applyFilters();
                        })
                        .catch(err => {
                            console.error('Error fetching lectures:', err);
                            detailBody.style.opacity = '1';
                        });
                });
            }

            // Reset button
            var resetBtn = document.getElementById('resetFilters');
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    ['filterRoom', 'filterProgram', 'filterSessionType', 'filterStatus'].forEach(function(id) {
                        var el = document.getElementById(id);
                        if (el) el.value = '';
                    });
                    applyFilters();
                });
            }

            // Apply initially to set export URL with initial date
            applyFilters();
        })();
    </script>
@endpush
