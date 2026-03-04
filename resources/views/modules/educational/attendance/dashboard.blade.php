@extends('core::layouts.master')
@section('title', __('educational::messages.attendance_dashboard'))

@section('content')
    @include('modules.educational.shared.alerts')

    <style>
        .dash-stat-card {
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .room-badge {
            background: rgba(var(--vz-primary-rgb), 0.1);
            color: var(--vz-primary);
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 8px;
        }

        .lecture-card {
            border-radius: 16px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .lecture-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.08) !important;
        }

        .status-dot {
            height: 8px;
            width: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .pulse-animation {
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        .progress-stacked {
            height: 8px;
            display: flex;
            overflow: hidden;
            border-radius: 4px;
            background-color: #f1f5f9;
        }

        .search-input-group {
            border-radius: 10px;
            overflow: hidden;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
    </style>

    <div class="container-fluid">
        {{-- ─── Header & Stats ────────────────────────────────────────────────── --}}
        <div class="row mb-4">
            <div class="col-xl-8 col-md-12">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-sm">
                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-24">
                            <i class="ri-dashboard-3-line"></i>
                        </div>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bold">تحكم الحضور والعمليات اليومية</h4>
                        <p class="text-muted mb-0">مراقبة القاعات، تسجيل حضور المتدربين، ومتابعة سير المحاضرات ليوم <span
                                class="text-primary fw-medium">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-12 mt-3 mt-xl-0">
                <div class="row g-2">
                    <div class="col-4">
                        <div class="card dash-stat-card border-0 bg-primary-subtle mb-0 shadow-none text-center p-2">
                            <small class="text-primary d-block mb-1">المحاضرات</small>
                            <h5 class="mb-0 fw-bold">{{ $summary['total'] }}</h5>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card dash-stat-card border-0 bg-success-subtle mb-0 shadow-none text-center p-2">
                            <small class="text-success d-block mb-1">إجمالي الحضور</small>
                            <h5 class="mb-0 fw-bold">{{ $summary['present'] }}</h5>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card dash-stat-card border-0 bg-danger-subtle mb-0 shadow-none text-center p-2">
                            <small class="text-danger d-block mb-1">إجمالي الغياب</small>
                            <h5 class="mb-0 fw-bold">{{ $summary['absent'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Search & Filters ─────────────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
            <div class="card-body p-3">
                <form action="{{ route('educational.attendance.dashboard') }}" method="GET"
                    class="row g-3 align-items-end" id="filterForm">

                    {{-- Search --}}
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small text-muted mb-1">
                            <i class="ri-search-line me-1"></i>البحث السريع
                        </label>
                        <div class="input-group search-input-group border-0">
                            <span class="input-group-text bg-transparent border-0 pe-0"><i
                                    class="ri-search-line text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-0 bg-transparent"
                                placeholder="اسم القاعة، المجموعة، البرنامج..." value="{{ $search }}">
                        </div>
                    </div>

                    {{-- Date --}}
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label small text-muted mb-1">
                            <i class="ri-calendar-line me-1"></i>التاريخ
                        </label>
                        <input type="date" name="date" class="form-control border-0 bg-light" id="dateFilter"
                            value="{{ $date }}">
                    </div>

                    {{-- Program --}}
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small text-muted mb-1">
                            <i class="ri-book-open-line me-1"></i>البرنامج التدريبي
                        </label>
                        <select name="program_id" class="form-select border-0 bg-light" data-choices>
                            <option value="">كل البرامج</option>
                            @foreach ($programs as $prog)
                                <option value="{{ $prog->id }}" {{ $programId == $prog->id ? 'selected' : '' }}>
                                    {{ $prog->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Building --}}
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label small text-muted mb-1">
                            <i class="ri-building-line me-1"></i>المبنى
                        </label>
                        <select name="building_id" id="buildingSelect" class="form-select border-0 bg-light"
                            onchange="onBuildingChange(this.value)">
                            <option value="">كل المباني</option>
                            @foreach ($buildings as $building)
                                <option value="{{ $building->id }}" data-floors="{{ $building->floors->toJson() }}"
                                    {{ $buildingId == $building->id ? 'selected' : '' }}>
                                    {{ $building->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Floor --}}
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label small text-muted mb-1">
                            <i class="ri-stack-line me-1"></i>الطابق
                        </label>
                        <select name="floor_id" id="floorSelect" class="form-select border-0 bg-light"
                            {{ !$buildingId ? 'disabled' : '' }}>
                            <option value="">كل الطوابق</option>
                            @foreach ($floors as $floor)
                                <option value="{{ $floor->id }}" {{ $floorId == $floor->id ? 'selected' : '' }}>
                                    {{ $floor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Attendance Status --}}
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label small text-muted mb-1">
                            <i class="ri-checkbox-circle-line me-1"></i>حالة الرصد
                        </label>
                        <select name="attendance_status" class="form-select border-0 bg-light">
                            <option value="">الكل</option>
                            <option value="completed" {{ $attendanceStatus === 'completed' ? 'selected' : '' }}>مكتمل
                                (100%)</option>
                            <option value="pending" {{ $attendanceStatus === 'pending' ? 'selected' : '' }}>معلق / غير
                                مكتمل</option>
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="col-lg-1 col-md-6">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100 shadow-sm" title="تطبيق الفلاتر">
                                <i class="ri-filter-fill align-bottom"></i>
                            </button>
                            <a href="{{ route('educational.attendance.dashboard') }}"
                                class="btn btn-soft-secondary flex-shrink-0" title="إعادة تعيين">
                                <i class="ri-refresh-line"></i>
                            </a>
                        </div>
                    </div>

                    {{-- Active filters badges --}}
                    @if ($buildingId || $floorId || $programId || $attendanceStatus || $search)
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <small class="text-muted fw-medium">الفلاتر النشطة:</small>
                                @if ($buildingId)
                                    @php $activeBuilding = $buildings->firstWhere('id', $buildingId) @endphp
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                                        <i class="ri-building-line me-1"></i>{{ $activeBuilding?->name }}
                                    </span>
                                @endif
                                @if ($floorId)
                                    @php $activeFloor = $floors->firstWhere('id', $floorId) @endphp
                                    <span class="badge bg-info-subtle text-info rounded-pill px-3 py-2">
                                        <i class="ri-stack-line me-1"></i>{{ $activeFloor?->name }}
                                    </span>
                                @endif
                                @if ($programId)
                                    @php $activeProg = $programs->firstWhere('id', $programId) @endphp
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                                        <i class="ri-book-open-line me-1"></i>{{ $activeProg?->name }}
                                    </span>
                                @endif
                                @if ($attendanceStatus)
                                    <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-2">
                                        <i class="ri-checkbox-circle-line me-1"></i>
                                        {{ $attendanceStatus === 'completed' ? 'مكتمل' : 'غير مكتمل' }}
                                    </span>
                                @endif
                                @if ($search)
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2">
                                        <i class="ri-search-line me-1"></i>{{ $search }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        {{-- ─── Lectures Grid ───────────────────────────────────────────────── --}}
        @forelse($rooms as $room)
            @php
                $roomLectures = $room->day_lectures;
                $locationLabel =
                    ($room->floor->building->name ?? '') .
                    ($room->floor->name ? ' - ' . $room->floor->name : '') .
                    ' (' .
                    $room->name .
                    ')';
            @endphp
            <div class="room-section mb-5">
                <div class="d-flex align-items-center gap-2 mb-3 px-2">
                    <span class="room-badge"><i class="ri-map-pin-2-fill me-1"></i> {{ $locationLabel }}</span>
                    <span class="badge bg-light text-muted fw-normal">{{ $roomLectures->count() }} محاضرات</span>
                    <div class="flex-grow-1 border-bottom border-dashed border-primary-subtle opacity-25 ms-2"></div>
                </div>

                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    @foreach ($roomLectures as $lecture)
                        @php
                            $traineesCount = $lecture->trainees_count ?? 0;
                            $attendancesCount = $lecture->attendances_count ?? 0;
                            $percentage = $traineesCount > 0 ? round(($attendancesCount / $traineesCount) * 100) : 0;

                            $isRunning = now()->between($lecture->starts_at, $lecture->ends_at);
                            $isFinished = now()->isAfter($lecture->ends_at);

                            $statusColors = [
                                'scheduled' => 'info',
                                'running' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                'rescheduled' => 'warning',
                            ];
                            $stateColor = $statusColors[$lecture->status] ?? 'secondary';
                        @endphp
                        <div class="col">
                            <div class="card lecture-card h-100 shadow-sm bg-white border-0">
                                <div class="card-header border-0 bg-transparent p-3 pb-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex align-items-center gap-2">
                                            <span
                                                class="badge bg-light text-dark fw-bold border">{{ $lecture->starts_at->format('H:i') }}</span>
                                            <span class="text-muted small">—</span>
                                            <span
                                                class="text-muted small fw-medium">{{ $lecture->ends_at->format('H:i') }}</span>
                                        </div>
                                        @if ($isRunning)
                                            <span class="badge bg-danger pulse-animation shadow-sm">جاري الآن <span
                                                    class="status-dot bg-white ms-1"></span></span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="mb-3">
                                        <h6 class="fw-bold mb-1 text-truncate"
                                            title="{{ $lecture->program->name ?? '' }}">
                                            {{ $lecture->program->name ?? 'برنامج غير محدد' }}</h6>
                                        <div class="d-flex align-items-center gap-1 text-primary small fw-medium">
                                            <i class="ri-group-line"></i>
                                            {{ $lecture->group->name ?? 'مجموعة غير محددة' }}
                                        </div>
                                    </div>

                                    <div class="p-2 bg-light rounded-3 mb-3 border border-light">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="text-muted small">حالة رصد الحضور والغياب</span>
                                            <span
                                                class="fw-bold small {{ $percentage == 100 ? 'text-success' : 'text-primary' }}">{{ $percentage }}%</span>
                                        </div>
                                        <div class="progress-stacked mb-1">
                                            @if ($traineesCount > 0)
                                                <div class="progress-bar bg-success" style="width: {{ $percentage }}%">
                                                </div>
                                            @else
                                                <div class="progress-bar bg-light" style="width: 100%"></div>
                                            @endif
                                        </div>
                                        <div class="d-flex justify-content-between fs-11 text-muted">
                                            <span>مرصود: {{ $attendancesCount }}</span>
                                            <span>إجمالي: {{ $traineesCount }}</span>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2 mb-0">
                                        <div class="avatar-xs flex-shrink-0">
                                            @if (
                                                $lecture->instructorProfile &&
                                                    $lecture->instructorProfile->user &&
                                                    $lecture->instructorProfile->user->profile_photo_url)
                                                <img src="{{ $lecture->instructorProfile->user->profile_photo_url }}"
                                                    class="rounded-circle img-fluid" alt="user">
                                            @else
                                                <div class="avatar-title bg-light text-muted rounded-circle fw-bold fs-10">
                                                    {{ substr($lecture->instructorProfile->user->full_name ?? '?', 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-truncate">
                                            <small class="text-muted d-block text-truncate">المحاضر</small>
                                            <small
                                                class="fw-medium text-dark">{{ $lecture->instructorProfile->user->full_name ?? 'غير محدد' }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer p-3 bg-light-subtle border-top-0 d-flex gap-2">
                                    <a href="{{ route('educational.attendance.show', $lecture->id) }}"
                                        class="btn {{ $percentage == 100 ? 'btn-soft-success' : 'btn-primary' }} w-100 fw-bold shadow-none rounded-3 px-0">
                                        @if ($percentage == 100)
                                            <i class="ri-check-double-line align-bottom"></i> تحديث الحضور
                                        @else
                                            <i class="ri-fingerprint-line align-bottom"></i> تسجيل الحضور
                                        @endif
                                    </a>
                                    <div class="dropdown">
                                        <button class="btn btn-soft-secondary dropdown-toggle arrow-none rounded-3 px-2"
                                            data-bs-toggle="dropdown">
                                            <i class="ri-more-2-fill"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3">
                                            <li><a class="dropdown-item"
                                                    href="{{ route('educational.attendance.show', $lecture->id) }}"><i
                                                        class="ri-fingerprint-line me-2 align-bottom text-muted"></i> تسجيل
                                                    الحضور</a></li>
                                            <li>
                                                <button type="button" class="dropdown-item"
                                                    onclick="openAbsenceReport({{ $lecture->id }}, '{{ addslashes($lecture->program->name ?? 'برنامج') }}', '{{ addslashes($lecture->group->name ?? 'مجموعة') }}')">
                                                    <i class="ri-file-list-line me-2 align-bottom text-muted"></i>
                                                    تقرير الغياب
                                                </button>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item text-danger"
                                                    onclick="openReportProblem(
                                                        {{ $lecture->id }},
                                                        '{{ addslashes($lecture->program->name ?? 'برنامج') }}',
                                                        '{{ addslashes($lecture->group->name ?? 'مجموعة') }}',
                                                        '{{ $lecture->starts_at->format('H:i') }} - {{ $lecture->ends_at->format('H:i') }}',
                                                        '{{ addslashes($lecture->instructorProfile->user->full_name ?? 'غير محدد') }}',
                                                        '{{ addslashes($lecture->room->name ?? '') }}'
                                                    )">
                                                    <i class="ri-error-warning-line me-2 align-bottom"></i>
                                                    تبليغ عن مشكلة
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <div class="avatar-lg mx-auto mb-4">
                        <div class="avatar-title bg-light text-primary rounded-circle fs-48">
                            <i class="ri-search-eye-line"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold">لم نتمكن من العثور على أية محاضرات</h4>
                    <p class="text-muted">جرب تغيير التاريخ أو معايير البحث والفلترة لعرض نتائج أخرى.</p>
                    <a href="{{ route('educational.attendance.dashboard') }}"
                        class="btn btn-primary px-4 rounded-pill mt-2">عرض محاضرات اليوم</a>
                </div>
            </div>
        @endforelse

        {{-- ─── Pagination ──────────────────────────────────────────────────── --}}
        <div class="mt-4 mb-5 d-flex justify-content-center">
            {{ $rooms->links() }}
        </div>
    </div>

@endsection

{{-- ════════════════════════════════════════════════════════════ --}}
{{--  MODAL: تبليغ عن مشكلة (فتح تذكرة مرتبطة بالمحاضرة)         --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@push('modals')
    <div class="modal fade" id="reportProblemModal" tabindex="-1" aria-labelledby="reportProblemModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                {{-- Header --}}
                <div class="modal-header bg-danger bg-gradient p-4">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div class="avatar-sm flex-shrink-0">
                            <div class="avatar-title bg-white bg-opacity-20 text-white rounded-circle fs-20">
                                <i class="ri-error-warning-fill"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="modal-title text-white fw-bold mb-0" id="reportProblemModalLabel">تبليغ عن مشكلة
                            </h5>
                            <p class="text-white text-opacity-75 mb-0 small" id="rp-lecture-subtitle">جاري التحميل...</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white ms-auto me-0" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                </div>

                {{-- Lecture Info Banner --}}
                <div class="bg-danger bg-opacity-10 border-bottom border-danger border-opacity-10 px-4 py-3">
                    <div class="row g-3 text-center">
                        <div class="col-6 col-md-3">
                            <div class="d-flex flex-column">
                                <small class="text-muted fw-bold text-uppercase mb-1"
                                    style="font-size:10px">البرنامج</small>
                                <span class="fw-bold text-dark small" id="rp-program">-</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="d-flex flex-column">
                                <small class="text-muted fw-bold text-uppercase mb-1"
                                    style="font-size:10px">المجموعة</small>
                                <span class="fw-bold text-dark small" id="rp-group">-</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="d-flex flex-column">
                                <small class="text-muted fw-bold text-uppercase mb-1" style="font-size:10px">الوقت</small>
                                <span class="fw-bold text-dark small" id="rp-time">-</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="d-flex flex-column">
                                <small class="text-muted fw-bold text-uppercase mb-1" style="font-size:10px">المحاضر /
                                    القاعة</small>
                                <span class="fw-bold text-dark small text-truncate" id="rp-instructor">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form --}}
                <form id="reportProblemForm" action="{{ route('tickets.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="lecture_id" id="rp-lecture-id">

                    <div class="modal-body p-4">
                        <div class="row g-3">
                            {{-- Stage --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">
                                    <i class="ri-node-tree me-1 text-danger"></i> المرحلة <span
                                        class="text-danger">*</span>
                                </label>
                                <select name="stage_id" id="rp-stage-select"
                                    class="form-select border-0 bg-light shadow-none" required
                                    onchange="rpPopulateCategories()">
                                    <option value="">اختر المرحلة...</option>
                                    @foreach (\Modules\Tickets\Domain\Models\TicketStage::with('categories.complaints.subComplaints')->get() as $stage)
                                        <option value="{{ $stage->id }}"
                                            data-categories="{{ $stage->categories->toJson() }}">
                                            {{ $stage->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Category --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">
                                    <i class="ri-stack-line me-1 text-danger"></i> التصنيف <span
                                        class="text-danger">*</span>
                                </label>
                                <select name="category_id" id="rp-category-select"
                                    class="form-select border-0 bg-light shadow-none" required disabled
                                    onchange="rpPopulateComplaints()">
                                    <option value="">---</option>
                                </select>
                            </div>

                            {{-- Complaint --}}
                            <div class="col-md-6" id="rp-complaint-group" style="display:none">
                                <label class="form-label fw-bold text-dark small">
                                    <i class="ri-error-warning-line me-1 text-danger"></i> نوع الشكوى
                                </label>
                                <select name="complaint_id" id="rp-complaint-select"
                                    class="form-select border-0 bg-light shadow-none">
                                    <option value="">---</option>
                                </select>
                            </div>

                            {{-- Priority --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">
                                    <i class="ri-scales-line me-1 text-danger"></i> الأولوية <span
                                        class="text-danger">*</span>
                                </label>
                                <select name="priority_id" class="form-select border-0 bg-light shadow-none" required>
                                    @foreach (\Modules\Tickets\Domain\Models\TicketPriority::all() as $priority)
                                        <option value="{{ $priority->id }}"
                                            {{ $priority->is_default ? 'selected' : '' }}>
                                            {{ $priority->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Subject --}}
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">
                                    <i class="ri-bookmark-3-line me-1 text-danger"></i> عنوان الشكوى
                                </label>
                                <input type="text" name="subject" id="rp-subject"
                                    class="form-control border-0 bg-light shadow-none"
                                    placeholder="عنوان مختصر للمشكلة (اختياري)">
                            </div>

                            {{-- Description --}}
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">
                                    <i class="ri-file-list-3-line me-1 text-danger"></i> تفاصيل المشكلة <span
                                        class="text-danger">*</span>
                                </label>
                                <textarea name="description" rows="5" class="form-control border-0 bg-light shadow-none" required
                                    placeholder="اشرح المشكلة بالتفصيل..."></textarea>
                            </div>

                            {{-- Attachments --}}
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">
                                    <i class="ri-attachment-line me-1 text-danger"></i> مرفقات (اختياري)
                                </label>
                                <input type="file" name="attachments[]" multiple
                                    class="form-control border-0 bg-light shadow-none">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 px-4 pb-4 pt-0 gap-2">
                        <button type="button" class="btn btn-light rounded-3 px-4"
                            data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-danger rounded-3 px-5 fw-bold">
                            <i class="ri-send-plane-fill me-2"></i> إرسال الشكوى
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{--  MODAL: تقرير الغياب                                        --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="absenceReportModal" tabindex="-1" aria-labelledby="absenceReportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                {{-- Header --}}
                <div class="modal-header bg-primary bg-gradient p-4">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div class="avatar-sm flex-shrink-0">
                            <div class="avatar-title bg-white bg-opacity-20 text-white rounded-circle fs-20">
                                <i class="ri-file-list-3-fill"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="modal-title text-white fw-bold mb-0" id="absenceReportModalLabel">تقرير الغياب</h5>
                            <p class="text-white text-opacity-75 mb-0 small" id="ar-lecture-subtitle">جاري التحميل...</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white ms-auto me-0" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body p-0">
                    {{-- Loading state --}}
                    <div id="ar-loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">جاري التحميل...</span>
                        </div>
                        <p class="text-muted mt-3">جاري جلب بيانات الغياب...</p>
                    </div>

                    {{-- Content --}}
                    <div id="ar-content" style="display:none">
                        {{-- Summary Stats --}}
                        <div class="px-4 pt-4 pb-3 bg-light border-bottom">
                            <div class="row g-3 text-center">
                                <div class="col-6 col-md-3">
                                    <div class="p-3 bg-white rounded-3 border shadow-sm">
                                        <h4 class="mb-0 fw-bold text-dark" id="ar-total">0</h4>
                                        <small class="text-muted fw-medium">إجمالي</small>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 bg-white rounded-3 border shadow-sm">
                                        <h4 class="mb-0 fw-bold text-success" id="ar-present">0</h4>
                                        <small class="text-muted fw-medium">حاضر</small>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 bg-white rounded-3 border shadow-sm">
                                        <h4 class="mb-0 fw-bold text-warning" id="ar-late">0</h4>
                                        <small class="text-muted fw-medium">متأخر / معذور</small>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 bg-white rounded-3 border border-danger shadow-sm">
                                        <h4 class="mb-0 fw-bold text-danger" id="ar-absent">0</h4>
                                        <small class="text-muted fw-medium">غائب</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Table --}}
                        <div class="p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="ri-user-unfollow-line me-2 text-danger"></i>
                                    قائمة الغائبين
                                </h6>
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-3"
                                    id="ar-absent-count-badge">0 غائب</span>
                            </div>

                            <div id="ar-empty"
                                class="text-center py-4 bg-success-subtle rounded-4 border border-success border-opacity-25"
                                style="display:none">
                                <i class="ri-checkbox-circle-fill text-success fs-36"></i>
                                <p class="text-success fw-bold mb-0 mt-2">لا يوجد غياب في هذه المحاضرة 🎉</p>
                            </div>

                            <div class="table-responsive rounded-3 border border-light" id="ar-table-wrapper">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="py-3 px-3 fw-bold text-muted small">#</th>
                                            <th class="py-3 px-3 fw-bold text-muted small">الاسم</th>
                                            <th class="py-3 px-3 fw-bold text-muted small">الرقم العسكري</th>
                                            <th class="py-3 px-3 fw-bold text-muted small text-center">الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ar-table-body">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Error state --}}
                    <div id="ar-error" class="text-center py-5 px-4" style="display:none">
                        <i class="ri-error-warning-line text-danger fs-36"></i>
                        <p class="text-danger fw-bold mt-3">فشل تحميل البيانات. يرجى المحاولة مرة أخرى.</p>
                    </div>
                </div>

                <div class="modal-footer border-top bg-light px-4 py-3">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">إغلاق</button>
                    <a href="#" id="ar-mark-link" class="btn btn-primary rounded-3 px-4 fw-bold">
                        <i class="ri-fingerprint-line me-2"></i> تسجيل الحضور
                    </a>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        // ─── Report Problem Modal ────────────────────────────────────────────────
        function openReportProblem(lectureId, program, group, time, instructor, room) {
            document.getElementById('rp-lecture-id').value = lectureId;
            document.getElementById('rp-lecture-subtitle').textContent = program + ' — ' + group;
            document.getElementById('rp-program').textContent = program;
            document.getElementById('rp-group').textContent = group;
            document.getElementById('rp-time').textContent = time;
            document.getElementById('rp-instructor').textContent = instructor + (room ? ' / ' + room : '');

            // Pre-fill subject
            document.getElementById('rp-subject').value = 'مشكلة في محاضرة: ' + program + ' — ' + group + ' (' + time + ')';

            // Reset dropdowns
            const stageSelect = document.getElementById('rp-stage-select');
            const categorySelect = document.getElementById('rp-category-select');
            stageSelect.value = '';
            categorySelect.innerHTML = '<option value="">---</option>';
            categorySelect.disabled = true;
            document.getElementById('rp-complaint-group').style.display = 'none';

            const modal = new bootstrap.Modal(document.getElementById('reportProblemModal'));
            modal.show();
        }

        function rpPopulateCategories() {
            const stageSelect = document.getElementById('rp-stage-select');
            const categorySelect = document.getElementById('rp-category-select');
            const selectedOption = stageSelect.options[stageSelect.selectedIndex];

            categorySelect.innerHTML = '<option value="">اختر التصنيف...</option>';
            categorySelect.disabled = true;
            document.getElementById('rp-complaint-group').style.display = 'none';

            if (!stageSelect.value) return;

            const categories = JSON.parse(selectedOption.getAttribute('data-categories') || '[]');
            categories.forEach(cat => {
                const opt = document.createElement('option');
                opt.value = cat.id;
                opt.textContent = cat.name;
                opt.setAttribute('data-complaints', JSON.stringify(cat.complaints || []));
                categorySelect.appendChild(opt);
            });
            categorySelect.disabled = false;
        }

        function rpPopulateComplaints() {
            const categorySelect = document.getElementById('rp-category-select');
            const complaintSelect = document.getElementById('rp-complaint-select');
            const complaintGroup = document.getElementById('rp-complaint-group');
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];

            complaintSelect.innerHTML = '<option value="">---</option>';
            complaintGroup.style.display = 'none';

            if (!categorySelect.value) return;

            const complaints = JSON.parse(selectedOption.getAttribute('data-complaints') || '[]');
            if (complaints.length > 0) {
                complaints.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name;
                    complaintSelect.appendChild(opt);
                });
                complaintGroup.style.display = 'block';
            }
        }

        // ─── Absence Report Modal ────────────────────────────────────────────────
        function openAbsenceReport(lectureId, program, group) {
            // Reset state
            document.getElementById('ar-loading').style.display = 'block';
            document.getElementById('ar-content').style.display = 'none';
            document.getElementById('ar-error').style.display = 'none';
            document.getElementById('ar-lecture-subtitle').textContent = program + ' — ' + group;
            document.getElementById('ar-mark-link').href =
                '{{ route('educational.attendance.show', ['lecture_id' => '__ID__']) }}'.replace('__ID__', lectureId);

            const modal = new bootstrap.Modal(document.getElementById('absenceReportModal'));
            modal.show();

            // Fetch data
            fetch(`/educational/attendance/${lectureId}/absence-report`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(data => {
                    // Update summary
                    document.getElementById('ar-total').textContent = data.summary.total;
                    document.getElementById('ar-present').textContent = data.summary.present;
                    document.getElementById('ar-late').textContent = data.summary.late + ' / ' + data.summary.excused;
                    document.getElementById('ar-absent').textContent = data.summary.absent;
                    document.getElementById('ar-absent-count-badge').textContent = data.summary.absent + ' غائب';

                    // Populate table
                    const tbody = document.getElementById('ar-table-body');
                    tbody.innerHTML = '';

                    if (data.absent_trainees.length === 0) {
                        document.getElementById('ar-empty').style.display = 'block';
                        document.getElementById('ar-table-wrapper').style.display = 'none';
                    } else {
                        document.getElementById('ar-empty').style.display = 'none';
                        document.getElementById('ar-table-wrapper').style.display = 'block';

                        data.absent_trainees.forEach((t, idx) => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                        <td class="px-3 py-3 text-muted small fw-bold">${idx + 1}</td>
                        <td class="px-3 py-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-xs flex-shrink-0">
                                    <div class="avatar-title bg-danger-subtle text-danger rounded-circle fs-12 fw-bold">
                                        ${t.full_name ? t.full_name.charAt(0) : '?'}
                                    </div>
                                </div>
                                <span class="fw-bold text-dark">${t.full_name}</span>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            <span class="text-muted small fw-medium">${t.military_number || '-'}</span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="badge bg-danger-subtle text-danger rounded-pill px-3 fw-bold">
                                <i class="ri-user-unfollow-line me-1"></i> غائب
                            </span>
                        </td>
                    `;
                            tbody.appendChild(tr);
                        });
                    }

                    document.getElementById('ar-loading').style.display = 'none';
                    document.getElementById('ar-content').style.display = 'block';
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('ar-loading').style.display = 'none';
                    document.getElementById('ar-error').style.display = 'block';
                });
        }
    </script>

    <script>
        // ─── Building → Floor Cascade ──────────────────────────────────────────
        function onBuildingChange(buildingId) {
            const floorSelect = document.getElementById('floorSelect');
            floorSelect.innerHTML = '<option value="">كل الطوابق</option>';

            if (!buildingId) {
                floorSelect.disabled = true;
                return;
            }

            // Get floors from the selected building's data attribute
            const buildingSelect = document.getElementById('buildingSelect');
            const selectedOption = buildingSelect.options[buildingSelect.selectedIndex];
            const floors = JSON.parse(selectedOption.getAttribute('data-floors') || '[]');

            floors.forEach(floor => {
                const opt = document.createElement('option');
                opt.value = floor.id;
                opt.textContent = floor.name;
                floorSelect.appendChild(opt);
            });

            floorSelect.disabled = floors.length === 0;

            // Auto-submit so the page reloads with the building filter applied
            // (needed so server knows which floors to preselect on next load)
            // Only submit to update building, not auto-submit on every change.
        }
    </script>
@endpush
