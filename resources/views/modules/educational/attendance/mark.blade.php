@extends('core::layouts.master')
@section('title', __('educational::messages.mark_attendance_title'))

@section('css')
    <style>
        /* Trainee Card Consistency */
        .trainee-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            border: 1px solid var(--vz-border-color) !important;
            border-radius: 0.75rem !important;
            position: relative;
            overflow: hidden;
        }

        .trainee-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--vz-box-shadow-lg) !important;
            border-color: var(--vz-primary) !important;
        }

        /* Using native border utility classes for status instead of pseudo elements */
        .trainee-card.selected-present {
            border-color: var(--vz-success) !important;
        }

        .trainee-card.selected-absent {
            border-color: var(--vz-danger) !important;
        }

        .trainee-card.selected-late {
            border-color: var(--vz-warning) !important;
        }

        .trainee-card.selected-excused {
            border-color: var(--vz-info) !important;
        }

        /* Segmented Control using native colors */
        .status-btn-group {
            background-color: var(--vz-light);
            border-radius: 0.5rem;
            padding: 4px;
            display: flex;
            gap: 4px;
        }

        .status-btn-group .btn {
            padding: 0.5rem 0.25rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            border: none !important;
            border-radius: 0.4rem !important;
            color: var(--vz-body-color);
            background: transparent;
            transition: all 0.2s ease-in-out;
        }

        .status-btn-group .btn:hover {
            background-color: var(--vz-card-bg);
        }

        .status-btn-group .btn i {
            font-size: 18px;
            line-height: 1;
        }

        /* Active States */
        .status-btn-group .btn-check:checked+.btn-outline-success {
            background-color: var(--vz-card-bg) !important;
            color: var(--vz-success) !important;
            box-shadow: var(--vz-box-shadow-sm);
        }

        .status-btn-group .btn-check:checked+.btn-outline-danger {
            background-color: var(--vz-card-bg) !important;
            color: var(--vz-danger) !important;
            box-shadow: var(--vz-box-shadow-sm);
        }

        .status-btn-group .btn-check:checked+.btn-outline-warning {
            background-color: var(--vz-card-bg) !important;
            color: var(--vz-warning) !important;
            box-shadow: var(--vz-box-shadow-sm);
        }

        .status-btn-group .btn-check:checked+.btn-outline-info {
            background-color: var(--vz-card-bg) !important;
            color: var(--vz-info) !important;
            box-shadow: var(--vz-box-shadow-sm);
        }

        /* Save Bar */
        .sticky-save-bar {
            position: sticky;
            bottom: 0px;
            background-color: rgba(var(--vz-card-bg-rgb), 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-top: 1px solid var(--vz-border-color);
            z-index: 1010;
            padding: 1.25rem 0;
            box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.06);
        }
    </style>
@endsection

@section('content')
    {{-- ─── KPI Summary Cards ───────────────────────────────────────────── --}}
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate border-0 shadow-sm bg-primary bg-gradient">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-white-50 text-truncate mb-0">
                                {{ __('educational::messages.total_trainees') }}</p>
                            <div class="d-flex align-items-center mb-3">
                                <h4 class="fs-4 fw-semibold ff-secondary text-white flex-grow-1 mb-0"><span
                                        id="total-count">{{ $trainees->count() }}</span></h4>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="ri-group-line text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate border-0 shadow-sm bg-success bg-gradient">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-white-50 text-truncate mb-0">
                                {{ __('educational::messages.present') }}</p>
                            <div class="d-flex align-items-center mb-3">
                                <h4 class="fs-4 fw-semibold ff-secondary text-white flex-grow-1 mb-0"><span
                                        id="present-count">0</span></h4>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="ri-checkbox-circle-line text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate border-0 shadow-sm bg-danger bg-gradient">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-white-50 text-truncate mb-0">
                                {{ __('educational::messages.absent') }}</p>
                            <div class="d-flex align-items-center mb-3">
                                <h4 class="fs-4 fw-semibold ff-secondary text-white flex-grow-1 mb-0"><span
                                        id="absent-count">0</span></h4>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="ri-close-circle-line text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate border-0 shadow-sm bg-warning bg-gradient">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-white-50 text-truncate mb-0">
                                {{ __('educational::messages.attendance_other_statuses') }}</p>
                            <div class="d-flex align-items-center mb-3">
                                <h4 class="fs-4 fw-semibold ff-secondary text-white flex-grow-1 mb-0"><span
                                        id="other-count">0</span></h4>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="ri-error-warning-line text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card" id="lecture-summary">
                <div class="card-body">
                    <div class="row align-items-center mb-4">
                        <div class="col-sm">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 avatar-sm">
                                    <div class="avatar-title bg-primary-subtle text-primary rounded fs-20">
                                        <i class="ri-flight-takeoff-line"></i> <!-- Program Icon -->
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fs-15 mb-1 fw-bold text-dark">
                                        {{ $lecture->group->program->name ?? __('educational::messages.program_not_selected') }}
                                    </h6>
                                    <p class="text-muted mb-0">
                                        <span
                                            class="badge bg-soft-info text-info me-1">{{ $lecture->sessionType->name ?? __('educational::messages.lecture') }}</span>
                                        <span class="badge bg-soft-secondary text-secondary"><i
                                                class="ri-group-line align-middle me-1"></i>
                                            {{ $lecture->group->name ?? '—' }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-auto mt-4 mt-sm-0">
                            <div class="d-flex align-items-center gap-2">
                                <div class="text-sm-end border-end pe-3">
                                    <p class="text-muted mb-1 fs-12 text-uppercase fw-semibold">
                                        {{ __('educational::messages.lecture_datetime') }}</p>
                                    <h5 class="fs-14 mb-0 fw-bold">
                                        <span class="text-success"><i class="ri-calendar-check-line align-bottom me-1"></i>
                                            {{ $lecture->starts_at->format('Y-m-d') }}</span>
                                        <span class="ms-1 text-muted">({{ $lecture->starts_at->format('H:i') }} -
                                            {{ $lecture->ends_at->format('H:i') }})</span>
                                    </h5>
                                </div>

                                <div class="d-flex align-items-center ps-3">
                                    <div class="flex-shrink-0 me-2">
                                        @if ($lecture->instructorProfile->user->avatar)
                                            <img src="{{ Storage::url($lecture->instructorProfile->user->avatar) }}"
                                                alt="" class="avatar-xs rounded-circle object-cover">
                                        @else
                                            <div class="avatar-xs">
                                                <span class="avatar-title bg-info-subtle text-info rounded-circle fs-12">
                                                    {{ mb_substr($lecture->instructorProfile->user->first_name ?? 'I', 0, 1) }}{{ mb_substr($lecture->instructorProfile->user->last_name ?? '', 0, 1) }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-bold fs-13">
                                            {{ $lecture->instructorProfile->user->full_name ?? '—' }}</h6>
                                        <p class="text-muted mb-0 fs-11">{{ __('educational::messages.instructor') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <div class="border border-dashed rounded p-3 h-100">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-light text-primary rounded fs-20">
                                            <i class="ri-book-3-line"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted mb-1 fs-12 text-uppercase fw-semibold">
                                            {{ __('educational::messages.specialization') }}</p>
                                        <h5 class="fs-14 mb-0 fw-bold">
                                            {{ $lecture->group->jobProfile->track->name ?? '—' }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="border border-dashed rounded p-3 h-100">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-light text-primary rounded fs-20">
                                            <i class="ri-briefcase-4-line"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted mb-1 fs-12 text-uppercase fw-semibold">
                                            {{ __('educational::messages.job_profile') }}</p>
                                        <h5 class="fs-14 mb-0 fw-bold">{{ $lecture->group->jobProfile->name ?? '—' }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="border border-dashed rounded p-3 h-100">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-light text-primary rounded fs-20">
                                            <i class="ri-map-pin-line"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted mb-1 fs-12 text-uppercase fw-semibold">
                                            {{ __('educational::messages.campus_and_building') }}</p>
                                        <h5 class="fs-14 mb-0 fw-bold text-truncate">
                                            {{ $lecture->room->floor->building->campus->name ?? '—' }} -
                                            {{ $lecture->room->floor->building->name ?? '—' }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="border border-dashed rounded p-3 h-100">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-light text-primary rounded fs-20">
                                            <i class="ri-door-open-line"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted mb-1 fs-12 text-uppercase fw-semibold">
                                            {{ __('educational::messages.floor_and_room') }}</p>
                                        <h5 class="fs-14 mb-0 fw-bold">{{ __('educational::messages.floor_label') }}
                                            {{ $lecture->room->floor->name ?? '—' }} - {{ $lecture->room->name ?? '—' }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            @if (isset($isLocked) && $isLocked)
                <div class="alert alert-danger border-0 d-flex align-items-center mb-4 rounded-3 shadow-sm">
                    <i class="ri-lock-2-fill fs-24 me-3 flex-shrink-0"></i>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading fw-bold mb-1">{{ __('educational::messages.attendance_locked_title') }}
                        </h5>
                        <p class="mb-0">{{ __('educational::messages.attendance_locked_message') }}</p>
                    </div>
                </div>
            @endif
            <div class="card border-0 shadow-sm overflow-hidden">
                <form action="{{ route('educational.attendance.store', $lecture->id) }}" method="POST"
                    id="attendanceForm">
                    @csrf
                    <div class="card-header border-0 bg-white py-3">
                        <div class="row align-items-center g-3">
                            <div class="col-lg-5">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <div class="search-box flex-grow-1" style="min-width: 200px;">
                                        <input type="text" class="form-control form-control-sm border-2"
                                            id="studentSearch"
                                            placeholder="{{ __('educational::messages.attendance_search_placeholder') }}">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                    <div class="bg-light p-1 rounded d-flex align-items-center filter-btn-group shadow-sm"
                                        role="group">
                                        <input type="radio" class="btn-check" name="statusFilter" id="filter_all"
                                            value="all" checked onchange="filterGrid()">
                                        <label class="btn btn-sm btn-ghost-dark fw-medium px-3 border-0 rounded"
                                            for="filter_all">{{ __('educational::messages.filter_all') }}</label>

                                        <input type="radio" class="btn-check" name="statusFilter" id="filter_present"
                                            value="present" onchange="filterGrid()">
                                        <label class="btn btn-sm btn-ghost-success fw-medium px-3 border-0 rounded"
                                            for="filter_present">{{ __('educational::messages.filter_present') }}</label>

                                        <input type="radio" class="btn-check" name="statusFilter" id="filter_absent"
                                            value="absent" onchange="filterGrid()">
                                        <label class="btn btn-sm btn-ghost-danger fw-medium px-3 border-0 rounded"
                                            for="filter_absent">{{ __('educational::messages.filter_absent') }}</label>

                                        <input type="radio" class="btn-check" name="statusFilter" id="filter_late"
                                            value="late" onchange="filterGrid()">
                                        <label class="btn btn-sm btn-ghost-warning fw-medium px-3 border-0 rounded"
                                            for="filter_late">{{ __('educational::messages.filter_late') }}</label>

                                        <input type="radio" class="btn-check" name="statusFilter" id="filter_excused"
                                            value="excused" onchange="filterGrid()">
                                        <label class="btn btn-sm btn-ghost-info fw-medium px-3 border-0 rounded"
                                            for="filter_excused">{{ __('educational::messages.filter_excused') }}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="d-flex flex-wrap justify-content-lg-end gap-2 align-items-center">
                                    <span class="text-muted small fw-bold me-2"><i class="ri-equalizer-line me-1"></i>
                                        {{ __('educational::messages.bulk_control') }}</span>
                                    <div class="btn-group shadow-sm" role="group">
                                        <button type="button" class="btn btn-soft-success"
                                            onclick="bulkMark('present')"><i class="ri-check-line me-1"></i>
                                            {{ __('educational::messages.mark_all_present_btn') }}</button>
                                        <button type="button" class="btn btn-soft-danger"
                                            onclick="bulkMark('absent')"><i class="ri-close-line me-1"></i>
                                            {{ __('educational::messages.mark_all_absent_btn') }}</button>
                                        <button type="button" class="btn btn-soft-warning" onclick="bulkMark('late')"><i
                                                class="ri-time-line me-1"></i>
                                            {{ __('educational::messages.mark_all_late_btn') }}</button>
                                        <button type="button" class="btn btn-soft-info" onclick="bulkMark('excused')"><i
                                                class="ri-history-line me-1"></i>
                                            {{ __('educational::messages.mark_all_excused_btn') }}</button>
                                    </div>
                                    <div id="bulk-time-container"
                                        class="d-none animate__animated animate__fadeIn border-start ps-2">
                                        <input type="time" class="form-control form-control-sm" id="bulk-time-input"
                                            onchange="applyBulkTime(this.value)"
                                            placeholder="{{ __('educational::messages.delay_time') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Completion Progress -->
                        <div class="border-top border-top-dashed pt-3 mt-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0 fw-bold fs-14" id="completion-text">
                                    {{ __('educational::messages.loading_stats') }}</h6>
                                <span class="badge bg-info-subtle border border-info text-info fs-12 px-2 py-1"
                                    id="completion-status-badge">
                                    <i class="ri-loader-4-line spin-slow me-1"></i>
                                    {{ __('educational::messages.preparing') }}
                                </span>
                            </div>
                            <div class="progress progress-sm rounded-pill" style="height: 6px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                    role="progressbar" id="completion-progress" style="width: 0%;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body bg-light p-4">
                        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4" id="trainee-grid">
                            @forelse($trainees->sortBy('user.full_name') as $index => $trainee)
                                @php
                                    $attendance = $existingAttendances->get($trainee->id);
                                    $currentStatus = $attendance ? $attendance->status : 'present';
                                    $currentNotes = $attendance ? $attendance->notes : '';
                                    $checkInTime = $attendance
                                        ? ($attendance->check_in_time
                                            ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i')
                                            : '')
                                        : '';
                                @endphp
                                <div class="col trainee-item-col"
                                    data-search="{{ ($trainee->user->full_name ?? '') . ' ' . ($trainee->national_id ?? '') . ' ' . ($trainee->military_number ?? '') . ' ' . ($trainee->sect ?? '') }}"
                                    data-current-status="{{ $currentStatus }}">
                                    <div class="card h-100 trainee-card border-0 trainee-status-{{ $currentStatus }}">
                                        <div class="card-body p-4 position-relative">
                                            <div class="d-flex align-items-center mb-4">
                                                <div class="flex-shrink-0">
                                                    @if ($trainee->user && $trainee->user->avatar)
                                                        <img src="{{ Storage::url($trainee->user->avatar) }}"
                                                            class="rounded-circle"
                                                            style="width: 52px; height: 52px; object-fit: cover;">
                                                    @else
                                                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle fw-bold fs-16"
                                                            style="width: 52px; height: 52px;">
                                                            {{ mb_substr($trainee->user->first_name ?? 'S', 0, 1) }}{{ mb_substr($trainee->user->last_name ?? '', 0, 1) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1 ms-3 overflow-hidden">
                                                    <h6 class="fs-15 mb-1 text-truncate fw-bold text-dark">
                                                        {{ $trainee->user->full_name ?? 'N/A' }}</h6>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="text-muted small"><i
                                                                class="ri-shield-user-line me-1 text-primary"></i>{{ $trainee->military_number ?? '—' }}</span>
                                                        <span
                                                            class="badge bg-light text-body border border-light-subtle">{{ $trainee->sect ?? '—' }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="attendance-status-box mb-3">
                                                <div class="btn-group w-100 status-btn-group" role="group">
                                                    <input type="radio" class="btn-check status-radio"
                                                        name="attendance[{{ $trainee->id }}][status]"
                                                        id="present_{{ $trainee->id }}" value="present"
                                                        {{ $currentStatus == 'present' || !$attendance ? 'checked' : '' }}
                                                        onchange="handleStatusChange(this, '{{ $trainee->id }}')">
                                                    <label class="btn btn-outline-success"
                                                        for="present_{{ $trainee->id }}"
                                                        title="{{ __('educational::messages.present') }}">
                                                        <i class="ri-check-line"></i>
                                                        <span>{{ __('educational::messages.present') }}</span>
                                                    </label>

                                                    <input type="radio" class="btn-check status-radio"
                                                        name="attendance[{{ $trainee->id }}][status]"
                                                        id="absent_{{ $trainee->id }}" value="absent"
                                                        {{ $currentStatus == 'absent' ? 'checked' : '' }}
                                                        onchange="handleStatusChange(this, '{{ $trainee->id }}')">
                                                    <label class="btn btn-outline-danger"
                                                        for="absent_{{ $trainee->id }}"
                                                        title="{{ __('educational::messages.absent') }}">
                                                        <i class="ri-close-line"></i>
                                                        <span>{{ __('educational::messages.absent') }}</span>
                                                    </label>

                                                    <input type="radio" class="btn-check status-radio"
                                                        name="attendance[{{ $trainee->id }}][status]"
                                                        id="late_{{ $trainee->id }}" value="late"
                                                        {{ $currentStatus == 'late' ? 'checked' : '' }}
                                                        onchange="handleStatusChange(this, '{{ $trainee->id }}')">
                                                    <label class="btn btn-outline-warning" for="late_{{ $trainee->id }}"
                                                        title="{{ __('educational::messages.filter_late') }}">
                                                        <i class="ri-time-line"></i>
                                                        <span>{{ __('educational::messages.filter_late') }}</span>
                                                    </label>

                                                    <input type="radio" class="btn-check status-radio"
                                                        name="attendance[{{ $trainee->id }}][status]"
                                                        id="excused_{{ $trainee->id }}" value="excused"
                                                        {{ $currentStatus == 'excused' ? 'checked' : '' }}
                                                        onchange="handleStatusChange(this, '{{ $trainee->id }}')">
                                                    <label class="btn btn-outline-info" for="excused_{{ $trainee->id }}"
                                                        title="{{ __('educational::messages.filter_excused') }}">
                                                        <i class="ri-history-line"></i>
                                                        <span>{{ __('educational::messages.filter_excused') }}</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div id="time_field_{{ $trainee->id }}"
                                                class="mb-3 {{ $currentStatus == 'late' ? '' : 'd-none' }}">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light text-muted px-3"><i
                                                            class="ri-time-line"></i></span>
                                                    <input type="time"
                                                        name="attendance[{{ $trainee->id }}][check_in_time]"
                                                        class="form-control bg-light time-input fw-medium"
                                                        value="{{ $checkInTime }}"
                                                        placeholder="{{ __('educational::messages.delay_time') }}">
                                                </div>
                                            </div>

                                            <div class="notes-field">
                                                <textarea name="attendance[{{ $trainee->id }}][notes]"
                                                    class="form-control form-control-sm bg-light border-0 rounded-3 px-3 py-2 text-body" rows="1"
                                                    placeholder="{{ __('educational::messages.add_note_optional') }}">{{ $currentNotes }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 py-5 text-center">
                                    <div class="avatar-lg mx-auto mb-3">
                                        <div class="avatar-title bg-light text-muted rounded-circle fs-36"><i
                                                class="ri-user-unfollow-line"></i></div>
                                    </div>
                                    <h5 class="text-muted">{{ __('educational::messages.no_trainees_in_group') }}</h5>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="sticky-save-bar card-footer border-0 shadow-lg">
                        <div class="container-fluid">
                            <div class="d-flex align-items-center justify-content-end">
                                <div class="d-flex gap-3">
                                    <a href="{{ route('educational.attendance.dashboard') }}"
                                        class="btn btn-light btn-label left rounded shadow-sm px-4"><i
                                            class="ri-close-line label-icon align-middle fs-16 me-2"></i>
                                        {{ __('educational::messages.cancel') }}</a>
                                    @if (isset($isLocked) && $isLocked)
                                        <button type="button"
                                            class="btn btn-secondary btn-label right rounded shadow-lg px-5 fw-bold"
                                            disabled>
                                            <i class="ri-lock-2-line label-icon align-middle fs-16 ms-2"></i>
                                            {{ __('educational::messages.locked_read_only') }}
                                        </button>
                                    @else
                                        <button type="submit"
                                            class="btn btn-primary btn-label right rounded shadow-lg px-5 fw-bold">
                                            <i class="ri-save-line label-icon align-middle fs-16 ms-2"></i>
                                            {{ __('educational::messages.save_attendance_sheet') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Run initial calculations
            updateKPIs();

            // Ensure accurate visibility of time fields and card styles on load
            document.querySelectorAll('.trainee-item-col').forEach(col => {
                const checkedRadio = col.querySelector('.status-radio:checked');
                if (checkedRadio) {
                    updateCardVisuals(col, checkedRadio.value);
                }
            });

            // Search and Filter Implementation
            const searchInput = document.getElementById('studentSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    filterGrid();
                });

                // Prevent form sumbission on Enter in search box
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.keyCode === 13) {
                        e.preventDefault();
                    }
                });
            }

            // Prevention of accidental close
            window.formDirty = false;
            const attendanceForm = document.getElementById('attendanceForm');
            if (attendanceForm) {
                attendanceForm.addEventListener('change', () => {
                    window.formDirty = true;
                });
                attendanceForm.addEventListener('submit', () => {
                    window.formDirty = false;
                });
            }

            window.addEventListener('beforeunload', (e) => {
                if (window.formDirty) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        });

        // Central Filter Function
        function filterGrid() {
            const searchInput = document.getElementById('studentSearch');
            const term = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const activeFilter = document.querySelector('input[name="statusFilter"]:checked').value;
            const cards = document.querySelectorAll('.trainee-item-col');

            cards.forEach(card => {
                const searchText = card.getAttribute('data-search').toLowerCase();
                const cardStatus = card.getAttribute('data-current-status');

                const matchSearch = term === '' || searchText.indexOf(term) !== -1;
                const matchStatus = activeFilter === 'all' || cardStatus === activeFilter;

                if (matchSearch && matchStatus) {
                    card.style.setProperty('display', '', 'important');
                } else {
                    card.style.setProperty('display', 'none', 'important');
                }
            });

            updateKPIs();
        }

        function handleStatusChange(radio, traineeId) {
            const traineeCol = radio.closest('.trainee-item-col');
            updateCardVisuals(traineeCol, radio.value);
            updateKPIs();
            window.formDirty = true;
        }

        function updateCardVisuals(cardElement, status) {
            const radio = cardElement.querySelector('.status-radio');
            if (!radio) return;
            const traineeId = radio.id.split('_')[1];
            const timeField = document.getElementById(`time_field_${traineeId}`);
            const traineeCard = cardElement.querySelector('.trainee-card');

            // Updates data-attribute for filters
            cardElement.setAttribute('data-current-status', status);

            // Reset classes
            if (traineeCard) {
                traineeCard.classList.remove('selected-present', 'selected-absent', 'selected-late', 'selected-excused');
                traineeCard.classList.add(`selected-${status}`);
            }

            // Re-filter if necessary
            if (window.formDirty) filterGrid();

            if (status === 'late') {
                if (timeField) {
                    timeField.classList.remove('d-none');
                    const timeInput = timeField.querySelector('input[type="time"]');
                    if (timeInput && !timeInput.value) {
                        const now = new Date();
                        timeInput.value = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString()
                            .padStart(2, '0');
                    }
                }
            } else {
                if (timeField) timeField.classList.add('d-none');
            }
        }

        function updateKPIs() {
            let present = 0,
                absent = 0,
                others = 0,
                checked = 0;
            const total = {{ $trainees->count() }};

            const allRadios = document.querySelectorAll('.status-radio:checked');
            allRadios.forEach(radio => {
                checked++;
                if (radio.value === 'present') present++;
                else if (radio.value === 'absent') absent++;
                else if (radio.value === 'late' || radio.value === 'excused') others++;
            });

            const presentEl = document.getElementById('present-count');
            const absentEl = document.getElementById('absent-count');
            const otherEl = document.getElementById('other-count');

            if (presentEl) presentEl.innerText = present;
            if (absentEl) absentEl.innerText = absent;
            if (otherEl) otherEl.innerText = others;

            const completionText = document.getElementById('completion-text');
            const completionBadge = document.getElementById('completion-status-badge');
            const completionProgress = document.getElementById('completion-progress');

            if (completionText && completionProgress && completionBadge) {
                const percentage = total > 0 ? Math.round((checked / total) * 100) : 0;
                completionProgress.style.width = percentage + '%';

                if (checked >= total && total > 0) {
                    completionText.innerText = '{{ __('educational::messages.record_all_success') }}';
                    completionBadge.className =
                    'badge bg-success-subtle border border-success text-success fs-12 px-2 py-1';
                    completionBadge.innerHTML =
                        '<i class="ri-checkbox-circle-line me-1"></i> {{ __('educational::messages.completed') }}';
                    completionProgress.className = 'progress-bar bg-success';
                } else {
                    completionText.innerText =
                        `{{ __('educational::messages.recorded_x_of_y', ['checked' => '${checked}', 'total' => '${total}']) }}`;
                    completionBadge.className = 'badge bg-info-subtle border border-info text-info fs-12 px-2 py-1';
                    completionBadge.innerHTML =
                        '<i class="ri-loader-4-line spin-slow me-1"></i> {{ __('educational::messages.preparing') }}';
                    completionProgress.className = 'progress-bar progress-bar-striped progress-bar-animated bg-primary';
                }
            }
        }

        function bulkMark(status) {
            document.querySelectorAll(`.status-radio[value="${status}"]`).forEach(radio => {
                radio.checked = true;
                const traineeCol = radio.closest('.trainee-item-col');
                updateCardVisuals(traineeCol, status);
            });

            const bulkTimeContainer = document.getElementById('bulk-time-container');
            if (status === 'late') {
                if (bulkTimeContainer) {
                    bulkTimeContainer.classList.remove('d-none');
                    bulkTimeContainer.classList.add('d-flex');
                }
                const bulkTimeInput = document.getElementById('bulk-time-input');
                if (bulkTimeInput) {
                    const now = new Date();
                    const time = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2,
                        '0');
                    bulkTimeInput.value = time;
                    applyBulkTime(time);
                }
            } else {
                if (bulkTimeContainer) {
                    bulkTimeContainer.classList.remove('d-flex');
                    bulkTimeContainer.classList.add('d-none');
                }
            }

            updateKPIs();
            window.formDirty = true;
        }

        function applyBulkTime(timeValue) {
            document.querySelectorAll('.time-input').forEach(input => {
                const traineeCol = input.closest('.trainee-item-col');
                if (traineeCol) {
                    const lateRadio = traineeCol.querySelector('.status-radio[value="late"]');
                    if (lateRadio && lateRadio.checked) {
                        input.value = timeValue;
                    }
                }
            });
        }
    </script>
@endpush
