@extends('core::layouts.master')
@section('title', 'جدول المحاضرات اليومي')

@section('title-actions')
    <div class="hstack gap-2">
        @if (auth()->user()->hasRole('super-admin'))
            <a href="{{ route('educational.requests.index') }}"
                class="btn btn-primary position-relative d-flex align-items-center shadow-sm">
                <i class="ri-git-pull-request-line fs-18 me-1"></i> طلبات المراجعة
                @if ($pendingApprovalsCount > 0)
                    <span
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light">
                        {{ $pendingApprovalsCount }}
                        <span class="visually-hidden">طلبات معلقة</span>
                    </span>
                @endif
            </a>
        @endif
        @can('settings.view')
            <a href="{{ route('settings.index') . '#educational' }}"
                class="btn btn-soft-info btn-icon shadow-sm d-flex align-items-center justify-content-center"
                style="height: 38.5px; width: 38.5px;" title="إعدادات المركز التعليمي" data-bs-toggle="tooltip">
                <i class="ri-settings-4-line fs-18"></i>
            </a>
        @endcan
        @can('education.lectures.create')
            <a href="{{ route('educational.lectures.create') }}" class="btn btn-success d-flex align-items-center shadow-sm">
                <i class="ri-add-circle-line fs-18 me-1"></i> {{ __('educational::messages.generate_lectures') }}
            </a>
        @endcan
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --schedule-bg: #f8f9fa;
            --room-card-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            --lecture-scheduled: #3577f1;
            --lecture-running: #4b38b3;
            --lecture-completed: #0ab39c;
            --lecture-cancelled: #f06548;
            --lecture-rescheduled: #ffbe0b;
        }

        .schedule-container {
            padding: 20px 0;
            background: var(--schedule-bg);
            border-radius: 12px;
        }

        .room-column {
            min-width: 300px;
            max-width: 400px;
            background: #fff;
            border-radius: 16px;
            margin-bottom: 24px;
            box-shadow: var(--room-card-shadow);
            border: 1px solid rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: transform 0.2s;
        }

        .room-column:hover {
            transform: translateY(-5px);
        }

        .room-header {
            padding: 16px 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .room-title {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #405189;
        }

        .room-subtitle {
            font-size: 0.8rem;
            color: #878a99;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .lectures-list {
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-height: 100px;
        }

        .lecture-card {
            background: #fff;
            border: 1px solid #e9ebec;
            border-radius: 12px;
            padding: 12px;
            position: relative;
            border-right: 4px solid #3577f1;
            transition: all 0.2s;
        }

        .lecture-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            background: #fdfdfd;
        }

        .lecture-time {
            font-size: 0.9rem;
            font-weight: 600;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
        }

        .lecture-subject {
            font-weight: 700;
            font-size: 1rem;
            color: #212529;
            margin-bottom: 4px;
        }

        .lecture-details {
            font-size: 0.85rem;
            color: #878a99;
        }

        .lecture-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .status-scheduled {
            border-right-color: var(--lecture-scheduled);
        }

        .status-running {
            border-right-color: var(--lecture-running);
        }

        .status-completed {
            border-right-color: var(--lecture-completed);
        }

        .status-cancelled {
            border-right-color: var(--lecture-cancelled);
        }

        .status-rescheduled {
            border-right-color: var(--lecture-rescheduled);
        }

        .empty-room {
            text-align: center;
            padding: 40px 20px;
            color: #adb5bd;
            font-style: italic;
        }

        /* Tabs Styling */
        .nav-pills-custom .nav-link {
            border-radius: 20px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .nav-pills-custom .nav-link.active {
            background: #405189;
            box-shadow: 0 4px 10px rgba(64, 81, 137, 0.3);
        }

        .instructor-name {
            color: #405189;
            font-weight: 600;
        }

        .supervisor-name {
            color: #f7b84b;
            font-weight: 600;
        }

        .lecture-card-mini .lecture-details-mini {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
        }

        .dropdown-item i {
            font-size: 16px;
        }

        /* Glassmorphism Filter */
        .filter-section {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
            position: relative;
            z-index: 1001;
            /* Ensure it's above the schedule content */
        }

        .date-nav {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .current-date-display {
            font-size: 1.2rem;
            font-weight: 700;
            color: #405189;
            min-width: 200px;
            text-align: center;
        }

        /* Table Schedule View Styling */
        .room-schedule-row {
            display: flex;
            align-items: stretch;
            background: #fff;
            border-radius: 12px;
            margin-bottom: 15px;
            border: 1px solid #e9ebec;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            position: relative;
            z-index: 1;
        }

        .room-info-cell {
            padding: 20px;
            background: #f3f6f9;
            min-width: 220px;
            max-width: 220px;
            border-left: 1px solid #e9ebec;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .draggable-scroll {
            cursor: grab;
            user-select: none;
            scrollbar-width: thin;
            scrollbar-color: #40518933 transparent;
            scroll-behavior: auto;
            /* Important for JS dragging */
        }

        .draggable-scroll:active {
            cursor: grabbing;
        }

        .draggable-scroll::-webkit-scrollbar {
            height: 6px;
        }

        .draggable-scroll::-webkit-scrollbar-thumb {
            background: #40518933;
            border-radius: 10px;
        }

        .lectures-scroll-area {
            display: flex;
            padding: 15px;
            gap: 15px;
            overflow-x: auto;
            flex-grow: 1;
            background: #ffffff;
        }

        .lecture-card-mini {
            min-width: 260px;
            max-width: 260px;
            background: #fff;
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #eee;
            border-right: 4px solid #3577f1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .lecture-card-mini .lecture-status {
            position: absolute;
            top: 10px;
            left: 10px;
            font-size: 10px;
        }

        /* Batch Selection Styling */
        .batch-actions-bar {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #ffffff;
            padding: 12px 25px;
            border-radius: 50px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
            border: 1px solid #e9ebec;
        }

        .batch-actions-bar.active {
            transform: translateX(-50%) translateY(0);
        }

        .lecture-select-check {
            width: 18px;
            height: 18px;
            cursor: pointer;
            border-radius: 4px;
        }

        .lecture-card.selected,
        .lecture-card-mini.selected {
            background-color: #f0f7ff !important;
            border-color: #3577f1 !important;
            box-shadow: 0 0 0 2px rgba(53, 119, 241, 0.2) !important;
        }

        .selection-count-badge {
            background: #3577f1;
            color: #fff;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 13px;
        }

        /* Choices Custom Styling */
        .choices {
            margin-bottom: 0;
            z-index: 1002;
        }

        .choices__list--dropdown {
            z-index: 1005 !important;
            background-color: #ffffff !important;
            border: 1px solid #e9ebec !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .choices__list--multiple .choices__item {
            background-color: #405189 !important;
            border: 1px solid #405189 !important;
            border-radius: 4px !important;
        }

        .filter-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            color: #878a99;
            margin-bottom: 4px;
            display: block;
        }

        /* ── Add Manual Lecture card ───────────────────────────────── */
        .add-lecture-card {
            border: 2px dashed #ced4da;
            border-radius: 12px;
            padding: 18px 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #adb5bd;
            cursor: pointer;
            transition: all 0.25s;
            background: transparent;
            text-align: center;
            min-height: 80px;
        }

        .add-lecture-card:hover {
            border-color: #405189;
            color: #405189;
            background: #f0f4ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(64, 81, 137, 0.12);
        }

        .add-lecture-card i {
            font-size: 1.5rem;
        }

        .add-lecture-card span {
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Mini card in table-view */
        .add-lecture-card-mini {
            min-width: 180px;
            max-width: 180px;
            border: 2px dashed #ced4da;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: #adb5bd;
            cursor: pointer;
            transition: all 0.25s;
            background: transparent;
            text-align: center;
            padding: 14px 8px;
            flex-shrink: 0;
        }

        .add-lecture-card-mini:hover {
            border-color: #405189;
            color: #405189;
            background: #f0f4ff;
        }

        .add-lecture-card-mini i {
            font-size: 1.3rem;
        }

        .add-lecture-card-mini span {
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    @php
        $statusColors = [
            'scheduled' => 'info',
            'running' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            'rescheduled' => 'warning',
        ];
    @endphp
    @include('modules.educational.shared.alerts')

    {{-- Removed redundant container-fluid --}}
    {{-- Removed redundant header row as it moved to master title-actions --}}

    {{-- Filter & Navigation Section --}}
    <div class="filter-section">
        <form action="{{ route('educational.lectures.index') }}" method="GET" id="filterForm">
            <div class="row g-3 mb-3 align-items-center">
                <div class="col-lg-4">
                    <div class="date-nav justify-content-center justify-content-lg-start">
                        <a href="{{ route('educational.lectures.index', array_merge(request()->query(), ['date' => \Carbon\Carbon::parse($date)->subDay()->format('Y-m-d')])) }}"
                            class="btn btn-soft-secondary btn-icon rounded-circle">
                            <i class="ri-arrow-right-s-line"></i>
                        </a>
                        <div class="current-date-display">
                            <i class="ri-calendar-calendar-line me-2"></i>
                            {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d M Y') }}
                        </div>
                        <a href="{{ route('educational.lectures.index', array_merge(request()->query(), ['date' => \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d')])) }}"
                            class="btn btn-soft-secondary btn-icon rounded-circle">
                            <i class="ri-arrow-left-s-line"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3">
                    <label class="filter-label">اختر التاريخ</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="ri-calendar-line"></i></span>
                        <input type="date" name="date" class="form-control bg-light border-0"
                            value="{{ $date }}" onchange="this.form.submit()">
                    </div>
                </div>
                <div class="col-lg-5 text-end">
                    <div class="d-flex justify-content-end align-items-center gap-2">
                        @if (request()->hasAny(['program_ids', 'room_ids', 'instructor_ids']))
                            <a href="{{ route('educational.lectures.index', ['date' => $date]) }}"
                                class="btn btn-soft-danger px-3 shadow-sm">
                                <i class="ri-refresh-line me-1"></i> تفريغ
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="ri-filter-3-line me-1"></i> تطبيق
                        </button>
                        <ul class="nav nav-pills nav-pills-custom" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#schedule-view" role="tab">
                                    <i class="ri-grid-fill me-1"></i> المربعات
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#table-view" role="tab">
                                    <i class="ri-list-check me-1"></i> القائمة
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-3">
                    <label class="filter-label">فلترة بالتخصصات</label>
                    <select name="track_ids[]" class="form-control js-choices" multiple>
                        @foreach ($allTracks as $track)
                            <option value="{{ $track->id }}"
                                {{ in_array($track->id, $appliedTrackIds) ? 'selected' : '' }}>
                                {{ $track->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="filter-label">فلترة بالبرامج</label>
                    <select name="program_ids[]" class="form-control js-choices" multiple>
                        @foreach ($allPrograms as $program)
                            <option value="{{ $program->id }}"
                                {{ in_array($program->id, $appliedProgramIds) ? 'selected' : '' }}>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="filter-label">فلترة بالقاعات والمعامل</label>
                    <select name="room_ids[]" class="form-control js-choices" multiple>
                        @foreach ($allRooms as $room)
                            <option value="{{ $room->id }}"
                                {{ in_array($room->id, (array) request('room_ids')) ? 'selected' : '' }}>
                                {{ $room->name }} ({{ $room->floor->building->name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="filter-label">فلترة بالمدربين</label>
                    <select name="instructor_ids[]" class="form-control js-choices" multiple>
                        @foreach ($allInstructors as $instructor)
                            <option value="{{ $instructor->id }}"
                                {{ in_array($instructor->id, (array) request('instructor_ids')) ? 'selected' : '' }}>
                                {{ $instructor->user->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>

    {{-- Main Content Tabs --}}
    <div class="tab-content">
        {{-- 1. Schedule View (New) --}}
        <div class="tab-pane active" id="schedule-view" role="tabpanel">
            <div class="row g-4 overflow-auto flex-nowrap pb-4 draggable-scroll" style="min-height: 600px;">
                @forelse($allRooms->filter(function($r) use ($roomsWithLectures) { return $roomsWithLectures->has($r->id) || request('room_ids'); }) as $room)
                    @php
                        $roomLectures = $roomsWithLectures->get($room->id, collect());
                    @endphp
                    @if (request('room_id') || $roomLectures->isNotEmpty())
                        <div class="col-auto">
                            <div class="room-column">
                                <div class="room-header">
                                    <h5 class="room-title">
                                        <div class="avatar-xxs flex-shrink-0">
                                            <div class="avatar-title bg-soft-primary text-primary rounded-circle fs-14">
                                                <i class="ri-building-2-line"></i>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input room-select-all" type="checkbox">
                                            </div>
                                            {{ $room->name }}
                                        </div>
                                    </h5>
                                    <div class="room-subtitle">
                                        <span><i class="ri-map-pin-2-line align-middle me-1"></i>
                                            {{ $room->floor->building->name ?? '' }} -
                                            {{ $room->floor->name ?? '' }}</span>
                                        <span class="badge bg-primary-subtle text-primary">{{ $roomLectures->count() }}
                                            محاضرة</span>
                                    </div>
                                </div>
                                <div class="lectures-list">
                                    @forelse($roomLectures as $index => $lecture)
                                        <div class="lecture-card status-{{ $lecture->status }}">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input lecture-select-check"
                                                            type="checkbox" value="{{ $lecture->id }}"
                                                            data-subject="{{ $lecture->subject ?? $lecture->sessionType->name }}">
                                                    </div>
                                                    <span
                                                        class="badge bg-light text-primary border border-primary-subtle">المحاضرة
                                                        {{ $index + 1 }}</span>
                                                </div>
                                                @php
                                                    $color = $statusColors[$lecture->status] ?? 'secondary';
                                                @endphp
                                                <span
                                                    class="badge bg-{{ $color }}-subtle text-{{ $color }} fs-11">
                                                    {{ ucfirst($lecture->status) }}
                                                </span>
                                            </div>

                                            <div class="lecture-time">
                                                <i class="ri-time-line"></i>
                                                {{ $lecture->starts_at->format('H:i') }} -
                                                {{ $lecture->ends_at->format('H:i') }}
                                            </div>

                                            <div class="lecture-subject selectable-title" style="cursor: pointer;">
                                                {{ $lecture->subject ?? $lecture->sessionType->name }}</div>

                                            <div class="lecture-details mt-2">
                                                <div class="mb-1">
                                                    <i class="ri-user-star-line me-1"></i>
                                                    {{ $lecture->instructorProfile->user->full_name ?? 'غير محدد' }}
                                                </div>
                                                <div class="mb-1">
                                                    <i class="ri-group-line me-1"></i> {{ $lecture->program->name ?? '' }}
                                                    - {{ $lecture->group->name ?? '' }}
                                                    <span class="badge bg-soft-info text-info rounded-pill ms-1 fs-10"
                                                        title="عدد الطلاب المسجلين">
                                                        {{ $lecture->group->trainees_count ?? 0 }} طالب
                                                    </span>
                                                </div>
                                                @if ($lecture->supervisor)
                                                    <div class="mb-1 text-warning">
                                                        <i class="ri-user-follow-line me-1"></i> مراقب:
                                                        {{ $lecture->supervisor->full_name }}
                                                    </div>
                                                @endif
                                            </div>

                                            @php
                                                $presentCount = $lecture->attendances
                                                    ->where('status', 'present')
                                                    ->count();
                                                $absentCount = $lecture->attendances
                                                    ->where('status', 'absent')
                                                    ->count();
                                                $lateCount = $lecture->attendances->where('status', 'late')->count();
                                                $excusedCount = $lecture->attendances
                                                    ->where('status', 'excused')
                                                    ->count();
                                                $totalAttendance =
                                                    $presentCount + $absentCount + $lateCount + $excusedCount;
                                            @endphp
                                            @if ($totalAttendance > 0)
                                                <div
                                                    class="d-flex justify-content-between align-items-center mt-2 border-top border-top-dashed pt-2 px-1">
                                                    <div class="text-success fs-12 fw-medium" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="حاضر"><i
                                                            class="ri-checkbox-circle-fill align-middle me-1"></i>{{ $presentCount }}
                                                    </div>
                                                    <div class="text-danger fs-12 fw-medium" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="غياب"><i
                                                            class="ri-close-circle-fill align-middle me-1"></i>{{ $absentCount }}
                                                    </div>
                                                    <div class="text-warning fs-12 fw-medium" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="تأخير"><i
                                                            class="ri-time-fill align-middle me-1"></i>{{ $lateCount }}
                                                    </div>
                                                    <div class="text-info fs-12 fw-medium" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="عذر"><i
                                                            class="ri-history-fill align-middle me-1"></i>{{ $excusedCount }}
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="mt-3 d-flex flex-column gap-2">
                                                @php
                                                    $activeAssignment = $lecture->formAssignments
                                                        ->where('is_active', true)
                                                        ->first();
                                                    $evalCount = $lecture->evaluations->count();
                                                    $isSupervisor = auth()->id() === $lecture->supervisor_id;
                                                    $isGlobalManager =
                                                        auth()
                                                            ->user()
                                                            ->hasAnyRole($globalSupervisorRoles ?? []) ||
                                                        auth()->user()->hasRole('super-admin');
                                                    $canMark = $isSupervisor || $isGlobalManager;
                                                @endphp

                                                @if ($activeAssignment)
                                                    <div class="d-flex gap-1 w-100">
                                                        @if ($evalCount > 0)
                                                            <a href="{{ route('educational.evaluations.assignments.results', $activeAssignment) }}"
                                                                class="btn btn-xs btn-soft-success flex-grow-1 py-1"
                                                                data-bs-toggle="tooltip"
                                                                title="تم استلام {{ $evalCount }} تقييم">
                                                                <i class="ri-checkbox-circle-line"></i> تم التقييم
                                                                ({{ $evalCount }})
                                                            </a>
                                                        @else
                                                            <a href="{{ route('educational.evaluations.assignments.results', $activeAssignment) }}"
                                                                class="btn btn-xs btn-soft-warning flex-grow-1 py-1"
                                                                data-bs-toggle="tooltip" title="بانتظار استلام التقييمات">
                                                                <i class="ri-time-line"></i> بانتظار التقييم
                                                            </a>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex gap-1 w-100">
                                                        @if ($canMark)
                                                            <a href="{{ route('educational.evaluations.assignments.fill', $activeAssignment) }}"
                                                                class="btn btn-xs btn-soft-primary flex-grow-1 py-1">
                                                                <i class="ri-survey-line me-1"></i> تعبئة تقييم
                                                            </a>
                                                        @endif
                                                        <div class="dropdown">
                                                            <button class="btn btn-xs btn-soft-secondary py-1"
                                                                data-bs-toggle="dropdown"><i
                                                                    class="ri-more-2-fill"></i></button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                @can('lectures.edit')
                                                                    <button class="dropdown-item text-primary fw-semibold"
                                                                        onclick="openEditLectureModal({{ json_encode(['id' => $lecture->id, 'room_id' => $lecture->room_id, 'subject' => $lecture->subject, 'date' => $lecture->starts_at->format('Y-m-d'), 'time_start' => $lecture->starts_at->format('H:i'), 'time_end' => $lecture->ends_at->format('H:i'), 'program_id' => $lecture->program_id, 'group_id' => $lecture->group_id, 'instructor_profile_id' => $lecture->instructor_profile_id, 'session_type_id' => $lecture->session_type_id, 'form_id' => optional($lecture->formAssignments->where('is_active', true)->first())->form_id, 'notes' => $lecture->notes, 'room_name' => $lecture->room->name]) }})">
                                                                        <i class="ri-edit-line align-middle me-1"></i>
                                                                        تعديل المحاضرة
                                                                    </button>
                                                                    <div class="dropdown-divider"></div>
                                                                @endcan
                                                                @if ($canMark)
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('educational.attendance.show', $lecture->id) }}">
                                                                        <i
                                                                            class="ri-calendar-check-line align-middle me-1 text-muted"></i>
                                                                        تسجيل الحضور
                                                                    </a>
                                                                    <div class="dropdown-divider"></div>
                                                                @endif

                                                                @if ($isGlobalManager)
                                                                    <button class="dropdown-item"
                                                                        onclick="openAssignSupervisorModal({{ $lecture->id }}, '{{ addslashes($lecture->subject ?? $lecture->sessionType->name) }}', '{{ $lecture->supervisor_id }}', '{{ $lecture->supervisor ? addslashes($lecture->supervisor->full_name) : '' }}')">
                                                                        <i
                                                                            class="ri-user-received-2-line align-middle me-1 text-muted"></i>
                                                                        إسناد لمراقب
                                                                    </button>
                                                                    @if ($lecture->status == 'scheduled')
                                                                        <button class="dropdown-item"
                                                                            onclick="confirmCancel({{ $lecture->id }})">
                                                                            <i
                                                                                class="ri-close-circle-line align-middle text-muted"></i>
                                                                            إلغاء المحاضرة
                                                                        </button>
                                                                    @endif
                                                                    <button class="dropdown-item text-danger"
                                                                        onclick="confirmDelete({{ $lecture->id }})">
                                                                        <i class="ri-delete-bin-line align-middle"></i> حذف
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="d-flex gap-1 w-100">
                                                        @if ($isGlobalManager)
                                                            <button type="button"
                                                                class="btn btn-xs btn-soft-light text-muted flex-grow-1 py-1"
                                                                onclick="openAssignModal({{ $lecture->id }}, '{{ addslashes($lecture->subject ?? $lecture->sessionType->name) }}')">
                                                                <i class="ri-add-line me-1"></i> تفعيل الرقابة
                                                            </button>
                                                        @endif
                                                        <div class="dropdown">
                                                            <button class="btn btn-xs btn-soft-secondary py-1"
                                                                data-bs-toggle="dropdown"><i
                                                                    class="ri-more-2-fill"></i></button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                @can('lectures.edit')
                                                                    <button class="dropdown-item text-primary fw-semibold"
                                                                        onclick="openEditLectureModal({{ json_encode(['id' => $lecture->id, 'room_id' => $lecture->room_id, 'subject' => $lecture->subject, 'date' => $lecture->starts_at->format('Y-m-d'), 'time_start' => $lecture->starts_at->format('H:i'), 'time_end' => $lecture->ends_at->format('H:i'), 'program_id' => $lecture->program_id, 'group_id' => $lecture->group_id, 'instructor_profile_id' => $lecture->instructor_profile_id, 'session_type_id' => $lecture->session_type_id, 'form_id' => optional($lecture->formAssignments->where('is_active', true)->first())->form_id, 'notes' => $lecture->notes, 'room_name' => $lecture->room->name]) }})">
                                                                        <i class="ri-edit-line align-middle me-1"></i>
                                                                        تعديل المحاضرة
                                                                    </button>
                                                                    <div class="dropdown-divider"></div>
                                                                @endcan
                                                                @if ($canMark)
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('educational.attendance.show', $lecture->id) }}">
                                                                        <i
                                                                            class="ri-calendar-check-line align-middle me-1 text-muted"></i>
                                                                        تسجيل الحضور
                                                                    </a>
                                                                    <div class="dropdown-divider"></div>
                                                                @endif

                                                                @if ($isGlobalManager)
                                                                    <button class="dropdown-item"
                                                                        onclick="openAssignSupervisorModal({{ $lecture->id }}, '{{ addslashes($lecture->subject ?? $lecture->sessionType->name) }}', '{{ $lecture->supervisor_id }}', '{{ $lecture->supervisor ? addslashes($lecture->supervisor->full_name) : '' }}')">
                                                                        <i
                                                                            class="ri-user-received-2-line align-middle me-1 text-muted"></i>
                                                                        إسناد لمراقب
                                                                    </button>
                                                                    @if ($lecture->status == 'scheduled')
                                                                        <button class="dropdown-item"
                                                                            onclick="confirmCancel({{ $lecture->id }})">
                                                                            <i
                                                                                class="ri-close-circle-line align-middle text-muted"></i>
                                                                            إلغاء المحاضرة
                                                                        </button>
                                                                    @endif
                                                                    <button class="dropdown-item text-danger"
                                                                        onclick="confirmDelete({{ $lecture->id }})">
                                                                        <i class="ri-delete-bin-line align-middle"></i> حذف
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="empty-room">
                                            <i class="ri-inbox-line fs-24 mb-2 d-block"></i>
                                            لا يوجد محاضرات اليوم
                                        </div>
                                    @endforelse
                                    {{-- ✦ زر إضافة محاضرة خارج الجدولة (منظور المربعات) --}}
                                    @can('education.lectures.create')
                                        <div class="add-lecture-card"
                                            onclick="openManualLectureModal({{ $room->id }}, '{{ addslashes($room->name) }}', '{{ $date }}')"
                                            title="إضافة محاضرة خارج الجدولة">
                                            <i class="ri-add-circle-line"></i>
                                            <span>محاضرة إضافية</span>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="avatar-lg mx-auto mb-4">
                            <div class="avatar-title bg-soft-info text-info rounded-circle fs-24">
                                <i class="ri-search-line"></i>
                            </div>
                        </div>
                        <h5>لا يوجد غرف متاحة</h5>
                        <p class="text-muted">يرجى التحقق من إعدادات القاعات والمعامل.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- 2. Table-Schedule View --}}
        <div class="tab-pane" id="table-view" role="tabpanel">
            @forelse($allRooms->when(request('room_id'), function($q) { return $q->where('id', request('room_id')); }) as $room)
                @php
                    $roomLectures = $roomsWithLectures->get($room->id, collect());
                @endphp
                @if (request('room_id') || $roomLectures->isNotEmpty())
                    <div class="room-schedule-row">
                        <div class="room-info-cell">
                            <h6 class="mb-1 fw-bold text-primary d-flex align-items-center gap-2">
                                <div class="form-check mb-0">
                                    <input class="form-check-input room-select-all" type="checkbox"
                                        style="width: 15px; height: 15px;">
                                </div>
                                {{ $room->name }}
                            </h6>
                            <small class="text-muted"><i class="ri-building-line"></i>
                                {{ $room->floor->building->name ?? '' }}</small>
                            <hr class="my-2 opacity-25">
                            <span class="badge bg-soft-info text-info">{{ $roomLectures->count() }} محاضرة</span>
                        </div>
                        <div class="lectures-scroll-area draggable-scroll">
                            @forelse($roomLectures as $index => $lecture)
                                <div class="lecture-card-mini status-{{ $lecture->status }}">
                                    <div class="form-check position-absolute"
                                        style="top: 10px; right: 10px; z-index: 10;">
                                        <input class="form-check-input lecture-select-check" type="checkbox"
                                            value="{{ $lecture->id }}"
                                            data-subject="{{ $lecture->subject ?? $lecture->sessionType->name }}">
                                    </div>
                                    @php $color = $statusColors[$lecture->status] ?? 'secondary'; @endphp
                                    <span
                                        class="badge bg-{{ $color }}-subtle text-{{ $color }} lecture-status"
                                        style="right: auto; left: 10px;">
                                        {{ ucfirst($lecture->status) }}
                                    </span>

                                    <div class="mt-2">
                                        <div class="text-muted fs-11 fw-medium mb-1">
                                            <i class="ri-time-line"></i> {{ $lecture->starts_at->format('H:i') }} -
                                            {{ $lecture->ends_at->format('H:i') }}
                                        </div>
                                        <div class="fw-bold fs-13 text-truncate mb-1 selectable-title"
                                            style="cursor: pointer;" title="{{ $lecture->subject }}">
                                            {{ $lecture->subject ?? $lecture->sessionType->name }}
                                        </div>
                                        <div class="fs-11 text-muted">
                                            <i class="ri-user-2-line me-1"></i> <span
                                                class="instructor-name">{{ $lecture->instructorProfile->user->full_name ?? '—' }}</span>
                                        </div>
                                        <div class="fs-11 text-muted d-flex align-items-center">
                                            <i class="ri-group-line me-1"></i> {{ $lecture->group->name ?? '—' }}
                                            <span
                                                class="ms-1 text-info fw-bold">({{ $lecture->group->trainees_count ?? 0 }})</span>
                                        </div>
                                        @if ($lecture->supervisor)
                                            <div class="fs-11 text-muted">
                                                <i class="ri-user-received-2-line me-1"></i> <span
                                                    class="supervisor-name">{{ $lecture->supervisor->full_name }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    @php
                                        $presentCount = $lecture->attendances->where('status', 'present')->count();
                                        $absentCount = $lecture->attendances->where('status', 'absent')->count();
                                        $lateCount = $lecture->attendances->where('status', 'late')->count();
                                        $excusedCount = $lecture->attendances->where('status', 'excused')->count();
                                        $totalAttendance = $presentCount + $absentCount + $lateCount + $excusedCount;
                                    @endphp
                                    @if ($totalAttendance > 0)
                                        <div
                                            class="d-flex justify-content-between align-items-center mt-2 border-top border-top-dashed pt-2">
                                            <div class="text-success fs-11 fw-medium" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="حاضر"><i
                                                    class="ri-checkbox-circle-fill align-middle"></i> {{ $presentCount }}
                                            </div>
                                            <div class="text-danger fs-11 fw-medium" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="غياب"><i
                                                    class="ri-close-circle-fill align-middle"></i> {{ $absentCount }}
                                            </div>
                                            <div class="text-warning fs-11 fw-medium" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="تأخير"><i
                                                    class="ri-time-fill align-middle"></i> {{ $lateCount }}</div>
                                            <div class="text-info fs-11 fw-medium" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="عذر"><i
                                                    class="ri-history-fill align-middle"></i> {{ $excusedCount }}</div>
                                        </div>
                                    @endif

                                    <div class="mt-2 d-flex flex-column gap-1">
                                        @php
                                            $activeAssignment = $lecture->formAssignments
                                                ->where('is_active', true)
                                                ->first();
                                            $evCount = $lecture->evaluations->count();
                                            $isSupervisor = auth()->id() === $lecture->supervisor_id;
                                            $isGlobalManager =
                                                auth()
                                                    ->user()
                                                    ->hasAnyRole($globalSupervisorRoles ?? []) ||
                                                auth()->user()->hasRole('super-admin');
                                            $canMark = $isSupervisor || $isGlobalManager;
                                        @endphp
                                        @if ($activeAssignment)
                                            <div class="d-flex gap-1">
                                                @if ($evCount > 0)
                                                    <a href="{{ route('educational.evaluations.assignments.results', $activeAssignment) }}"
                                                        class="btn btn-xs btn-soft-success flex-grow-1 py-0 fs-10"
                                                        title="مشاهدة النتائج">
                                                        <i class="ri-checkbox-circle-fill"></i> تم ({{ $evCount }})
                                                    </a>
                                                @else
                                                    <a href="{{ route('educational.evaluations.assignments.results', $activeAssignment) }}"
                                                        class="btn btn-xs btn-soft-warning flex-grow-1 py-0 fs-10"
                                                        title="بانتظار التقييم">
                                                        <i class="ri-time-line"></i> بانتظار
                                                    </a>
                                                @endif
                                                @if ($canMark)
                                                    <a href="{{ route('educational.evaluations.assignments.fill', $activeAssignment) }}"
                                                        class="btn btn-xs btn-primary py-0 fs-10" title="تعبئة تقييم"><i
                                                            class="ri-pencil-line"></i></a>
                                                @endif

                                                <div class="dropdown">
                                                    <button class="btn btn-xs btn-soft-secondary py-0 fs-10"
                                                        data-bs-toggle="dropdown"><i class="ri-more-2-fill"></i></button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        @if ($canMark)
                                                            <a class="dropdown-item"
                                                                href="{{ route('educational.attendance.show', $lecture->id) }}">
                                                                <i
                                                                    class="ri-calendar-check-line align-middle me-1 text-muted"></i>
                                                                تسجيل الحضور
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                        @endif

                                                        @if ($isGlobalManager)
                                                            <button class="dropdown-item"
                                                                onclick="openAssignSupervisorModal({{ $lecture->id }}, '{{ addslashes($lecture->subject ?? $lecture->sessionType->name) }}', '{{ $lecture->supervisor_id }}', '{{ $lecture->supervisor ? addslashes($lecture->supervisor->full_name) : '' }}')">
                                                                <i
                                                                    class="ri-user-follow-line align-middle me-1 text-muted"></i>
                                                                إسناد لمراقب
                                                            </button>
                                                            @if ($lecture->status == 'scheduled')
                                                                <button class="dropdown-item"
                                                                    onclick="confirmCancel({{ $lecture->id }})">
                                                                    <i
                                                                        class="ri-close-circle-line align-middle text-muted"></i>
                                                                    إلغاء
                                                                </button>
                                                            @endif
                                                            <button class="dropdown-item text-danger"
                                                                onclick="confirmDelete({{ $lecture->id }})">
                                                                <i class="ri-delete-bin-line align-middle"></i> حذف
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="d-flex gap-1 w-100">
                                                @if ($isGlobalManager)
                                                    <button type="button"
                                                        class="btn btn-xs btn-outline-light text-muted flex-grow-1 py-0 fs-10"
                                                        onclick="openAssignModal({{ $lecture->id }}, '{{ addslashes($lecture->subject ?? $lecture->sessionType->name) }}')">تفعيل
                                                        الرقابة</button>
                                                @endif
                                                <div class="dropdown">
                                                    <button class="btn btn-xs btn-soft-secondary py-0 fs-10"
                                                        data-bs-toggle="dropdown"><i class="ri-more-2-fill"></i></button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        @if ($canMark)
                                                            <a class="dropdown-item"
                                                                href="{{ route('educational.attendance.show', $lecture->id) }}">
                                                                <i
                                                                    class="ri-calendar-check-line align-middle me-1 text-muted"></i>
                                                                تسجيل الحضور
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                        @endif

                                                        @if ($isGlobalManager)
                                                            <button class="dropdown-item"
                                                                onclick="openAssignSupervisorModal({{ $lecture->id }}, '{{ addslashes($lecture->subject ?? $lecture->sessionType->name) }}', '{{ $lecture->supervisor_id }}', '{{ $lecture->supervisor ? addslashes($lecture->supervisor->full_name) : '' }}')">
                                                                <i
                                                                    class="ri-user-follow-line align-middle me-1 text-muted"></i>
                                                                إسناد لمراقب
                                                            </button>
                                                            @if ($lecture->status == 'scheduled')
                                                                <button class="dropdown-item"
                                                                    onclick="confirmCancel({{ $lecture->id }})">
                                                                    <i
                                                                        class="ri-close-circle-line align-middle text-muted"></i>
                                                                    إلغاء
                                                                </button>
                                                            @endif
                                                            <button class="dropdown-item text-danger"
                                                                onclick="confirmDelete({{ $lecture->id }})">
                                                                <i class="ri-delete-bin-line align-middle"></i> حذف
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="d-flex align-items-center text-muted italic fs-13">لا يوجد محاضرات مجدولة اليوم
                                </div>
                            @endforelse
                            {{-- ✦ بطاقة منقطة في نهاية الصف (منظور القائمة) --}}
                            @can('education.lectures.create')
                                <div class="add-lecture-card-mini"
                                    onclick="openManualLectureModal({{ $room->id }}, '{{ addslashes($room->name) }}', '{{ $date }}')"
                                    title="إضافة محاضرة خارج الجدولة">
                                    <i class="ri-add-circle-line"></i>
                                    <span>محاضرة إضافية</span>
                                </div>
                            @endcan
                        </div>
                    </div>
                @endif
            @empty
                <div class="text-center py-5">
                    <h5>لا يوجد بيانات للعرض</h5>
                </div>
            @endforelse
        </div>
    </div>
    {{-- Removed redundant closing div for container-fluid --}}

    {{-- Batch Actions Bar --}}
    <div id="batchActionsBar" class="batch-actions-bar">
        <div class="d-flex align-items-center gap-3">
            <span class="selection-count-badge" id="selectedCount">0</span>
            <span class="text-muted fw-medium">محاضرات مختارة</span>
        </div>
        <div class="vr opacity-25"></div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold"
                onclick="openBatchAssignSupervisorModal()">
                <i class="ri-user-received-2-line me-1"></i> إسناد لمراقب
            </button>
            <button type="button" class="btn btn-ghost-danger btn-sm rounded-pill px-3" onclick="clearSelection()">
                <i class="ri-close-line me-1"></i> إلغاء التحديد
            </button>
        </div>
    </div>

    {{-- Batch Assign Supervisor Modal --}}
    <div class="modal fade" id="batchAssignSupervisorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-soft-warning border-0">
                    <h5 class="modal-title fw-bold">إسناد جماعي لمراقب</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('educational.lectures.batch_assign_supervisor') }}" method="POST">
                    @csrf
                    <div id="batch_lecture_ids_container"></div>
                    <div class="modal-body p-4">
                        <div class="alert alert-soft-warning border-0 mb-4">
                            <div class="d-flex">
                                <i class="ri-information-line fs-20 me-2"></i>
                                <div>
                                    سيتم تعيين المراقب المختار لـ <strong id="batch-selected-count-label">0</strong> محاضرة
                                    تم اختيارها.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">اختر المراقب</label>
                            <select name="supervisor_id" id="batch_supervisor_select" class="form-select js-choices">
                                <option value="">بدون مراقب (إلغاء الإسناد)</option>
                                @foreach ($eligibleSupervisors as $supervisor)
                                    <option value="{{ $supervisor->id }}">{{ $supervisor->full_name }}
                                        ({{ $supervisor->roles->pluck('name')->first() }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-warning px-4 fw-bold">تأفيذ الإسناد الجماعي</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ✦ Modal: إنشاء محاضرة يدوية خارج الجدولة --}}
    @can('education.lectures.create')
        <div class="modal fade" id="manualLectureModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg">

                    {{-- Header --}}
                    <div class="modal-header border-0" style="background: linear-gradient(135deg, #405189, #6a7fca);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-sm">
                                <div class="avatar-title bg-white bg-opacity-25 rounded-circle text-white fs-18">
                                    <i class="ri-time-line"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-white mb-0">إضافة محاضرة خارج الجدولة</h5>
                                <small class="text-white-50" id="manual-room-label">—</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <form id="manualLectureForm" action="{{ route('educational.lectures.store_manual') }}" method="POST">
                        @csrf
                        {{-- Hidden fields: room & date --}}
                        <input type="hidden" name="room_id" id="manual_room_id">
                        <input type="hidden" name="lecture_date" id="manual_lecture_date">

                        <div class="modal-body p-4">

                            {{-- ┌─ الأوقات المحجوزة ─────────────────────────────── --}}
                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-semibold fs-13 text-muted">
                                        <i class="ri-calendar-event-line me-1 text-primary"></i>
                                        الأوقات المستخدمة في هذه القاعة اليوم
                                    </span>
                                    <small id="booked-date-label" class="text-muted"></small>
                                </div>

                                {{-- Timeline strip (08:00 → 22:00) --}}
                                <div class="position-relative"
                                    style="height:36px; background:#f3f6f9; border-radius:8px; overflow:hidden;">
                                    <div id="timeline-track"
                                        style="position:absolute; top:0; left:0; right:0; bottom:0; border-radius:8px;">
                                    </div>
                                    {{-- Pointer for selected time --}}
                                    <div id="timeline-selected"
                                        style="position:absolute; top:0; bottom:0; background:rgba(64,81,137,0.25);
                                            border:2px solid #405189; border-radius:6px; display:none; pointer-events:none;">
                                    </div>
                                    {{-- Hour ticks --}}
                                    @for ($h = 8; $h <= 22; $h += 2)
                                        <span
                                            style="position:absolute; bottom:2px; font-size:9px; color:#adb5bd;
                                                 left:{{ (($h - 8) / 14) * 100 }}%;">
                                            {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                    @endfor
                                </div>

                                {{-- Booked slots list --}}
                                <div id="booked-slots-list" class="mt-2 d-flex flex-wrap gap-1">
                                    <span class="text-muted fs-12 fst-italic" id="booked-empty-msg">— لا توجد محاضرات مجدولة
                                        —</span>
                                </div>

                                {{-- Conflict warning --}}
                                <div id="conflict-warning" class="alert alert-danger border-0 py-2 px-3 mt-2 fs-13 d-none">
                                    <i class="ri-error-warning-line me-1"></i>
                                    <span id="conflict-msg">يوجد تعارض مع محاضرة موجودة!</span>
                                </div>
                            </div>
                            {{-- └─────────────────────────────────────────────────── --}}

                            <div class="row g-3">

                                {{-- وقت البداية والنهاية (ساعة فقط) --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        وقت البداية <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0">
                                            <i class="ri-play-circle-line text-success"></i>
                                        </span>
                                        <input type="time" name="time_start" id="manual_time_start" class="form-control"
                                            step="300" min="06:00" max="22:00" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        وقت النهاية <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0">
                                            <i class="ri-stop-circle-line text-danger"></i>
                                        </span>
                                        <input type="time" name="time_end" id="manual_time_end" class="form-control"
                                            step="300" min="06:00" max="23:00" required>
                                    </div>
                                    <div id="end-before-start-warning" class="text-danger fs-12 mt-1 d-none">
                                        <i class="ri-error-warning-line"></i> وقت النهاية يجب أن يكون بعد وقت البداية
                                    </div>
                                </div>

                                {{-- الموضوع --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">موضوع / عنوان المحاضرة</label>
                                    <input type="text" name="subject" id="manual_subject" class="form-control"
                                        placeholder="مثال: محاضرة تعويضية — الوحدة الثالثة" maxlength="255">
                                </div>

                                {{-- البرنامج والمجموعة --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">البرنامج</label>
                                    <select name="program_id" id="manual_program_id" class="form-select"
                                        onchange="loadGroupsForManual(this.value)">
                                        <option value="">— اختياري —</option>
                                        @foreach ($allPrograms as $prog)
                                            <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المجموعة</label>
                                    <select name="group_id" id="manual_group_id" class="form-select">
                                        <option value="">— اختياري —</option>
                                    </select>
                                </div>

                                {{-- المدرب ونوع الجلسة --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المدرب</label>
                                    <select name="instructor_profile_id" class="form-select">
                                        <option value="">— اختياري —</option>
                                        @foreach ($allInstructors as $inst)
                                            <option value="{{ $inst->id }}">{{ $inst->user->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">نوع الجلسة</label>
                                    <select name="session_type_id" class="form-select">
                                        <option value="">— اختياري —</option>
                                        @foreach (\Modules\Educational\Domain\Models\SessionType::all() as $st)
                                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- ★ نموذج التقييم --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-survey-line me-1 text-primary"></i>
                                        نموذج الأسئلة / التقييم
                                        <small class="text-muted fw-normal">(اختياري)</small>
                                    </label>
                                    <select name="form_id" class="form-select">
                                        <option value="">— بدون نموذج تقييم —</option>
                                        @foreach (\Modules\Educational\Domain\Models\EvaluationForm::published()->get() as $ef)
                                            <option value="{{ $ef->id }}">{{ $ef->title }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted fs-12">
                                        إذا اخترت نموذجاً سيتم تفعيل جمع التقييمات تلقائياً بعد إنشاء المحاضرة.
                                    </div>
                                </div>

                                {{-- ملاحظات --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">ملاحظات</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="أي ملاحظات إضافية..."></textarea>
                                </div>

                            </div>
                        </div>{{-- end modal-body --}}

                        <div class="modal-footer border-0 bg-light">
                            <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">
                                <i class="ri-close-line me-1"></i> إلغاء
                            </button>
                            <button type="submit" id="manual-submit-btn" class="btn btn-primary px-5 fw-bold">
                                <i class="ri-add-circle-line me-1"></i> إنشاء المحاضرة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    {{-- Modals --}}
    {{-- ✦ Modal: تعديل المحاضرة --}}
    @can('lectures.edit')
        <div class="modal fade" id="editLectureModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg">

                    <div class="modal-header border-0" style="background: linear-gradient(135deg, #405189, #6a7fca);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-sm">
                                <div class="avatar-title bg-white bg-opacity-25 rounded-circle text-white fs-18">
                                    <i class="ri-edit-2-line"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-white mb-0">تعديل بيانات المحاضرة</h5>
                                <small class="text-white-50" id="edit-room-label">—</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <form id="editLectureForm" method="POST" action="">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="room_id" id="edit_room_id">
                        <input type="hidden" name="lecture_date" id="edit_lecture_date">

                        <div class="modal-body p-4">

                            {{-- أوقات القاعة المحجوزة --}}
                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-semibold fs-13 text-muted">
                                        <i class="ri-calendar-event-line me-1 text-success"></i>
                                        الأوقات المستخدمة في هذه القاعة (المحاضرة الحالية مُستثناة)
                                    </span>
                                    <small id="edit-booked-date-label" class="text-muted"></small>
                                </div>
                                <div class="position-relative"
                                    style="height:36px; background:#f3f6f9; border-radius:8px; overflow:hidden;">
                                    <div id="edit-timeline-track"
                                        style="position:absolute;top:0;left:0;right:0;bottom:0;border-radius:8px;"></div>
                                    <div id="edit-timeline-selected"
                                        style="position:absolute;top:0;bottom:0;background:rgba(64,81,137,0.2);
                                            border:2px solid #405189;border-radius:6px;display:none;pointer-events:none;">
                                    </div>
                                    @for ($h = 8; $h <= 22; $h += 2)
                                        <span
                                            style="position:absolute;bottom:2px;font-size:9px;color:#adb5bd;left:{{ (($h - 8) / 14) * 100 }}%;">
                                            {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                    @endfor
                                </div>
                                <div id="edit-booked-slots-list" class="mt-2 d-flex flex-wrap gap-1">
                                    <span class="text-muted fs-12 fst-italic">— محاضرات القاعة —</span>
                                </div>
                                <div id="edit-conflict-warning"
                                    class="alert alert-danger border-0 py-2 px-3 mt-2 fs-13 d-none">
                                    <i class="ri-error-warning-line me-1"></i>
                                    <span id="edit-conflict-msg">يوجد تعارض!</span>
                                </div>
                            </div>

                            <div class="row g-3">
                                {{-- وقت البداية والنهاية --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">وقت البداية <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i
                                                class="ri-play-circle-line text-success"></i></span>
                                        <input type="time" name="time_start" id="edit_time_start" class="form-control"
                                            step="300" min="06:00" max="22:00" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">وقت النهاية <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i
                                                class="ri-stop-circle-line text-danger"></i></span>
                                        <input type="time" name="time_end" id="edit_time_end" class="form-control"
                                            step="300" min="06:00" max="23:00" required>
                                    </div>
                                    <div id="edit-end-before-start-warning" class="text-danger fs-12 mt-1 d-none">
                                        <i class="ri-error-warning-line"></i> وقت النهاية يجب أن يكون بعد البداية
                                    </div>
                                </div>

                                {{-- القاعة --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">القاعة <span class="text-danger">*</span></label>
                                    <select name="room_id_select" id="edit_room_id_select" class="form-select"
                                        onchange="onEditRoomChange(this.value)">
                                        @foreach (\Modules\Educational\Domain\Models\Room::with('floor.building')->orderBy('name')->get() as $r)
                                            <option value="{{ $r->id }}" data-name="{{ $r->name }}">
                                                {{ $r->name }}
                                                @if ($r->floor?->building)
                                                    — {{ $r->floor->building->name }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- الموضوع --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">موضوع / عنوان المحاضرة</label>
                                    <input type="text" name="subject" id="edit_subject" class="form-control"
                                        placeholder="عنوان المحاضرة" maxlength="255">
                                </div>

                                {{-- البرنامج والمجموعة --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">البرنامج</label>
                                    <select name="program_id" id="edit_program_id" class="form-select"
                                        onchange="loadGroupsForEdit(this.value, null)">
                                        <option value="">— اختياري —</option>
                                        @foreach ($allPrograms as $prog)
                                            <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المجموعة</label>
                                    <select name="group_id" id="edit_group_id" class="form-select">
                                        <option value="">— اختياري —</option>
                                    </select>
                                </div>

                                {{-- المدرب ونوع الجلسة --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المدرب</label>
                                    <select name="instructor_profile_id" id="edit_instructor_id" class="form-select">
                                        <option value="">— اختياري —</option>
                                        @foreach ($allInstructors as $inst)
                                            <option value="{{ $inst->id }}">{{ $inst->user->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">نوع الجلسة</label>
                                    <select name="session_type_id" id="edit_session_type_id" class="form-select">
                                        <option value="">— اختياري —</option>
                                        @foreach (\Modules\Educational\Domain\Models\SessionType::all() as $st)
                                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- نموذج التقييم --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        <i class="ri-survey-line me-1 text-success"></i>
                                        نموذج الأسئلة / التقييم
                                        <small class="text-muted fw-normal">(اختياري)</small>
                                    </label>
                                    <select name="form_id" id="edit_form_id" class="form-select">
                                        <option value="">— بدون نموذج تقييم —</option>
                                        @foreach (\Modules\Educational\Domain\Models\EvaluationForm::published()->get() as $ef)
                                            <option value="{{ $ef->id }}">{{ $ef->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- ملاحظات --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">ملاحظات</label>
                                    <textarea name="notes" id="edit_notes" class="form-control" rows="2" placeholder="أي ملاحظات إضافية..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-0 bg-light">
                            <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">
                                <i class="ri-close-line me-1"></i> إلغاء
                            </button>
                            <button type="submit" id="edit-submit-btn" class="btn btn-primary px-5 fw-bold">
                                <i class="ri-save-line me-1"></i> حفظ التعديلات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    <div class="modal fade" id="assignFormModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-soft-primary border-0">
                    <h5 class="modal-title fw-bold">تعيين نموذج تقييم</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="assignFormEl" method="POST" action="">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">المحاضرة:</label>
                            <div id="modal-lecture-label" class="form-control-plaintext text-primary fw-bold">—</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">اختر نموذج التقييم <span
                                    class="text-danger">*</span></label>
                            <select name="form_id" class="form-select border-0 bg-light" required>
                                <option value="">— اختر النموذج —</option>
                                @foreach (\Modules\Educational\Domain\Models\EvaluationForm::published()->get() as $ef)
                                    <option value="{{ $ef->id }}">{{ $ef->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary px-4">حفظ وتعيين</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Assign Supervisor Modal --}}
    <div class="modal fade" id="assignSupervisorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-soft-warning border-0">
                    <h5 class="modal-title fw-bold">إسناد مراقب للمحاضرة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="assignSupervisorForm" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">المحاضرة المختارة</label>
                            <div id="modal-supervisor-lecture-label" class="form-control-plaintext text-warning fw-bold">—
                            </div>
                            <div id="modal-current-supervisor-info" class="mt-1 small"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">اختر المراقب</label>
                            <select name="supervisor_id" id="supervisor_select" class="form-select js-choices">
                                <option value="">بدون مراقب (إلغاء الإسناد)</option>
                                @foreach ($eligibleSupervisors as $supervisor)
                                    <option value="{{ $supervisor->id }}">{{ $supervisor->full_name }}
                                        ({{ $supervisor->roles->pluck('name')->first() }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text mt-2">سيتمكن هذا المستخدم من تحضير الطلاب وتعبئة التقييم لهذه المحاضرة
                                تحديداً.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-warning px-4 fw-bold">حفظ وإسناد</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Existing Forms for actions --}}
    @foreach ($lectures as $lecture)
        <form id="cancel-form-{{ $lecture->id }}"
            action="{{ route('educational.lectures.update', $lecture->id) . '?' . http_build_query(request()->query()) }}"
            method="POST" class="d-none">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="cancelled">
            <input type="hidden" name="reason" id="cancel-reason-{{ $lecture->id }}">
        </form>
        <form id="delete-form-{{ $lecture->id }}"
            action="{{ route('educational.lectures.destroy', $lecture->id) . '?' . http_build_query(request()->query()) }}"
            method="POST" class="d-none">
            @csrf @method('DELETE')
            <input type="hidden" name="reason" id="delete-reason-{{ $lecture->id }}">
        </form>
    @endforeach

@endsection

@push('scripts')
    <script>
        // Initialize Choices.js
        document.querySelectorAll('.js-choices').forEach(el => {
            new Choices(el, {
                removeItemButton: true,
                placeholderValue: 'اختر المتاح...',
                searchPlaceholderValue: 'بحث...',
                noChoicesText: 'لا يوجد خيارات متاحة',
                itemSelectText: 'اضغط للاختيار',
            });
        });

        function openAssignModal(lectureId, lectureLabel) {
            const form = document.getElementById('assignFormEl');
            const label = document.getElementById('modal-lecture-label');
            form.action = "{{ url('educational/evaluations/lectures') }}/" + lectureId + '/assign';
            label.textContent = lectureLabel;
            new bootstrap.Modal(document.getElementById('assignFormModal')).show();
        }

        function openAssignSupervisorModal(lectureId, lectureLabel, currentSupervisorId, currentSupervisorName) {
            try {
                const form = document.getElementById('assignSupervisorForm');
                const label = document.getElementById('modal-supervisor-lecture-label');
                const currentInfo = document.getElementById('modal-current-supervisor-info');
                const select = document.getElementById('supervisor_select');

                if (!form || !label || !select) {
                    console.error("Required modal elements not found");
                    return;
                }

                form.action = "{{ url('educational/lectures') }}/" + lectureId + '/assign-supervisor';
                label.textContent = lectureLabel;

                if (currentSupervisorName && currentSupervisorName !== 'null' && currentSupervisorName !== '') {
                    currentInfo.innerHTML =
                        `<span class="badge bg-soft-success text-success p-2 w-100 d-block text-start"><i class="ri-user-follow-line me-1"></i> معين حالياً: ${currentSupervisorName}</span>`;
                } else {
                    currentInfo.innerHTML =
                        `<span class="badge bg-soft-secondary text-secondary p-2 w-100 d-block text-start">لا يوجد مراقب معين حالياً</span>`;
                }

                const targetValue = currentSupervisorId ? String(currentSupervisorId) : "";

                // Safe Choices update
                if (typeof Choices !== 'undefined') {
                    try {
                        const instance = Choices.getInstance(select);
                        if (instance) {
                            instance.setChoiceByValue(targetValue);
                        } else {
                            select.value = targetValue;
                        }
                    } catch (e) {
                        select.value = targetValue;
                    }
                } else {
                    select.value = targetValue;
                }

                const modalEl = document.getElementById('assignSupervisorModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                }
            } catch (e) {
                console.error("Error opening supervisor modal:", e);
            }
        }

        const isSuperAdmin = {{ auth()->user()->hasRole('super-admin') ? 'true' : 'false' }};

        function confirmCancel(id) {
            if (isSuperAdmin) {
                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    text: "سيتم إلغاء هذه المحاضرة فوراً!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f06548',
                    confirmButtonText: 'نعم، إلغاء',
                    cancelButtonText: 'تراجع'
                }).then((result) => {
                    if (result.isConfirmed) document.getElementById('cancel-form-' + id).submit();
                });
            } else {
                Swal.fire({
                    title: 'طلب إلغاء محاضرة',
                    text: "برجاء ذكر سبب الإلغاء ليتم مراجعته من قبل الإدارة:",
                    input: 'textarea',
                    inputPlaceholder: 'اكتب السبب هنا...',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#f06548',
                    confirmButtonText: 'إرسال الطلب',
                    cancelButtonText: 'تراجع',
                    inputValidator: (value) => {
                        if (!value) return 'يجب ذكر السبب لتقديم الطلب!'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('cancel-reason-' + id).value = result.value;
                        document.getElementById('cancel-form-' + id).submit();
                    }
                });
            }
        }

        function confirmDelete(id) {
            if (isSuperAdmin) {
                Swal.fire({
                    title: 'حذف المحاضرة؟',
                    text: "لا يمكن التراجع عن هذا الإجراء!",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#f06548',
                    confirmButtonText: 'حذف نهائي',
                    cancelButtonText: 'تراجع'
                }).then((result) => {
                    if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
                });
            } else {
                Swal.fire({
                    title: 'طلب حذف محاضرة',
                    text: "الحذف يتطلب موافقة الإدارة. برجاء ذكر السبب:",
                    input: 'textarea',
                    inputPlaceholder: 'اكتب السبب هنا...',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f06548',
                    confirmButtonText: 'تقديم طلب حذف',
                    cancelButtonText: 'تراجع',
                    inputValidator: (value) => {
                        if (!value) return 'يجب ذكر السبب لتقديم الطلب!'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-reason-' + id).value = result.value;
                        document.getElementById('delete-form-' + id).submit();
                    }
                });
            }
        }

        // Drag-to-scroll functionality for horizontal lists
        const initDraggableScroll = () => {
            const folders = document.querySelectorAll('.draggable-scroll');

            folders.forEach(slider => {
                let isDown = false;
                let startX;
                let scrollLeft;
                let velX = 0;
                let momentumID;

                const beginMomentum = () => {
                    cancelAnimationFrame(momentumID);
                    momentumID = requestAnimationFrame(momentumLoop);
                };

                const momentumLoop = () => {
                    slider.scrollLeft += velX;
                    velX *= 0.95; // Friction
                    if (Math.abs(velX) > 0.5) {
                        momentumID = requestAnimationFrame(momentumLoop);
                    }
                };

                slider.addEventListener('mousedown', (e) => {
                    isDown = true;
                    slider.classList.add('grabbing');
                    startX = e.pageX - slider.offsetLeft;
                    scrollLeft = slider.scrollLeft;
                    cancelAnimationFrame(momentumID);
                });

                slider.addEventListener('mouseleave', () => {
                    isDown = false;
                    slider.classList.remove('grabbing');
                    beginMomentum();
                });

                slider.addEventListener('mouseup', () => {
                    isDown = false;
                    slider.classList.remove('grabbing');
                    beginMomentum();
                });

                slider.addEventListener('mousemove', (e) => {
                    if (!isDown) return;
                    e.preventDefault();
                    const x = e.pageX - slider.offsetLeft;
                    const walk = (x - startX) * 1.5; // Scroll speed
                    const prevScrollLeft = slider.scrollLeft;
                    slider.scrollLeft = scrollLeft - walk;
                    velX = slider.scrollLeft - prevScrollLeft;
                });

                // Prevent clicks on links during drag
                slider.addEventListener('click', (e) => {
                    if (Math.abs(velX) > 5) {
                        e.preventDefault();
                    }
                });
            });
        };

        initDraggableScroll();

        // Persist active tab
        const activeTab = localStorage.getItem('lecture_active_tab');
        if (activeTab) {
            const tabEl = document.querySelector(`a[href="${activeTab}"]`);
            if (tabEl) {
                const tab = new bootstrap.Tab(tabEl);
                tab.show();
            }
        }

        document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', (e) => {
                localStorage.setItem('lecture_active_tab', e.target.getAttribute('href'));
            });
        });

        // Batch Selection Logic
        const batchBar = document.getElementById('batchActionsBar');
        const selectedCountBadge = document.getElementById('selectedCount');
        const checkboxes = document.querySelectorAll('.lecture-select-check');

        function updateBatchBar() {
            const uniqueSelectedIds = new Set(Array.from(checkboxes)
                .filter(i => i.checked)
                .map(i => i.value));

            const count = uniqueSelectedIds.size;
            selectedCountBadge.textContent = count;

            if (count > 0) {
                batchBar.classList.add('active');
            } else {
                batchBar.classList.remove('active');
            }

            // Highlight cards across all views
            checkboxes.forEach(cb => {
                const card = cb.closest('.lecture-card') || cb.closest('.lecture-card-mini');
                if (card) {
                    if (uniqueSelectedIds.has(cb.value)) {
                        cb.checked = true; // Sync checkbox state
                        card.classList.add('selected');
                    } else {
                        cb.checked = false; // Sync checkbox state
                        card.classList.remove('selected');
                    }
                }
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                // Find all other checkboxes for the same lecture id and sync them
                const val = this.value;
                const checked = this.checked;
                document.querySelectorAll(`.lecture-select-check[value="${val}"]`).forEach(other => {
                    other.checked = checked;
                });
                updateBatchBar();
            });
        });

        // Make titles clickable
        document.querySelectorAll('.selectable-title').forEach(title => {
            title.addEventListener('click', function(e) {
                const card = this.closest('.lecture-card') || this.closest('.lecture-card-mini');
                const checkbox = card.querySelector('.lecture-select-check');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    // Trigger change manually to trigger the sync logic
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
        });

        // Select All in Room
        document.querySelectorAll('.room-select-all').forEach(selectAll => {
            selectAll.addEventListener('change', function() {
                const container = this.closest('.room-column') || this.closest('.room-schedule-row');
                if (container) {
                    const innerChecks = container.querySelectorAll('.lecture-select-check');
                    innerChecks.forEach(cb => {
                        cb.checked = this.checked;
                    });
                    updateBatchBar();
                }
            });
        });

        function clearSelection() {
            checkboxes.forEach(cb => cb.checked = false);
            updateBatchBar();
        }

        function openBatchAssignSupervisorModal() {
            const selectedIds = Array.from(checkboxes)
                .filter(i => i.checked)
                .map(i => i.value);

            const container = document.getElementById('batch_lecture_ids_container');
            const countLabel = document.getElementById('batch-selected-count-label');

            container.innerHTML = '';
            selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'lecture_ids[]';
                input.value = id;
                container.appendChild(input);
            });

            countLabel.textContent = selectedIds.length;

            new bootstrap.Modal(document.getElementById('batchAssignSupervisorModal')).show();
        }

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // ═══════════════════════════════════════════════════════════════
        // Manual (Unscheduled) Lecture Modal — Full Implementation
        // ═══════════════════════════════════════════════════════════════
        let _bookedSlots = []; // cache of booked slots for current room+date
        let _hasConflict = false;
        const TIMELINE_START = 6; // 06:00
        const TIMELINE_END = 23; // 23:00
        const TIMELINE_SPAN = TIMELINE_END - TIMELINE_START; // 17 hours

        /** Convert "HH:MM" to fraction 0..1 on the timeline */
        function timeToFraction(hhmm) {
            const [h, m] = hhmm.split(':').map(Number);
            return Math.max(0, Math.min(1, (h + m / 60 - TIMELINE_START) / TIMELINE_SPAN));
        }

        /** Open the modal pre-filled with room & date context */
        function openManualLectureModal(roomId, roomName, date) {
            document.getElementById('manual_room_id').value = roomId;
            document.getElementById('manual_lecture_date').value = date;
            document.getElementById('manual-room-label').textContent = 'القاعة: ' + roomName;
            document.getElementById('booked-date-label').textContent = date;

            // Reset time inputs and warnings
            document.getElementById('manual_time_start').value = '';
            document.getElementById('manual_time_end').value = '';
            document.getElementById('conflict-warning').classList.add('d-none');
            document.getElementById('end-before-start-warning').classList.add('d-none');
            document.getElementById('timeline-selected').style.display = 'none';
            document.getElementById('manual_program_id').value = '';
            document.getElementById('manual_group_id').innerHTML = '<option value="">— اختياري —</option>';
            _hasConflict = false;

            // Fetch booked slots for this room & date
            fetchBookedSlots(roomId, date);

            new bootstrap.Modal(document.getElementById('manualLectureModal')).show();
        }

        /** Fetch and render booked time slots */
        function fetchBookedSlots(roomId, date) {
            const listEl = document.getElementById('booked-slots-list');
            const trackEl = document.getElementById('timeline-track');
            const emptyEl = document.getElementById('booked-empty-msg');

            listEl.innerHTML = '<span class="text-muted fs-12 fst-italic">جاري التحميل...</span>';
            trackEl.innerHTML = '';
            _bookedSlots = [];

            const url = '{{ route('educational.api.rooms.booked_slots', ':rid') }}'
                .replace(':rid', roomId) + '?date=' + date;

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    _bookedSlots = data.slots || [];
                    listEl.innerHTML = '';
                    trackEl.innerHTML = '';

                    if (_bookedSlots.length === 0) {
                        listEl.innerHTML =
                            '<span class="text-muted fs-12 fst-italic" id="booked-empty-msg">— لا توجد محاضرات مجدولة —</span>';
                        return;
                    }

                    _bookedSlots.forEach(slot => {
                        // Badge in list
                        const colorMap = {
                            scheduled: 'primary',
                            running: 'warning',
                            completed: 'success'
                        };
                        const color = colorMap[slot.status] || 'secondary';
                        const badge = document.createElement('span');
                        badge.className =
                            `badge bg-${color}-subtle text-${color} fs-11 border border-${color}-subtle`;
                        badge.innerHTML =
                            `<i class="ri-time-line me-1"></i>${slot.start}–${slot.end} <small class="opacity-75">${slot.subject}</small>`;
                        listEl.appendChild(badge);

                        // Block on timeline
                        const left = (timeToFraction(slot.start) * 100).toFixed(2);
                        const width = ((timeToFraction(slot.end) - timeToFraction(slot.start)) * 100).toFixed(
                            2);
                        const block = document.createElement('div');
                        block.style.cssText = `position:absolute; top:0; bottom:0; left:${left}%; width:${width}%;
                                               background:rgba(240,101,72,0.35); border-radius:4px;
                                               border-left:2px solid #f06548;`;
                        block.title = `${slot.start}–${slot.end}: ${slot.subject}`;
                        trackEl.appendChild(block);
                    });

                    checkConflict(); // re-check if times already entered
                })
                .catch(() => {
                    listEl.innerHTML = '<span class="text-warning fs-12">تعذر تحميل الأوقات المحجوزة</span>';
                });
        }

        /** Check if selected times conflict with any booked slot */
        function checkConflict() {
            const startVal = document.getElementById('manual_time_start').value;
            const endVal = document.getElementById('manual_time_end').value;
            const warnEl = document.getElementById('conflict-warning');
            const msgEl = document.getElementById('conflict-msg');
            const selEl = document.getElementById('timeline-selected');
            const endWarn = document.getElementById('end-before-start-warning');

            // Validate end > start
            if (startVal && endVal && endVal <= startVal) {
                endWarn.classList.remove('d-none');
                _hasConflict = true;
                selEl.style.display = 'none';
                return;
            }
            endWarn.classList.add('d-none');

            if (!startVal || !endVal) {
                warnEl.classList.add('d-none');
                selEl.style.display = 'none';
                _hasConflict = false;
                return;
            }

            // Render selected range on timeline
            const left = (timeToFraction(startVal) * 100).toFixed(2);
            const width = ((timeToFraction(endVal) - timeToFraction(startVal)) * 100).toFixed(2);
            selEl.style.left = left + '%';
            selEl.style.width = width + '%';
            selEl.style.display = 'block';

            // Check conflict
            _hasConflict = false;
            for (const slot of _bookedSlots) {
                // Overlap: new.start < slot.end AND new.end > slot.start
                if (startVal < slot.end && endVal > slot.start) {
                    _hasConflict = true;
                    msgEl.textContent = `يوجد تعارض مع محاضرة (${slot.start}–${slot.end}: ${slot.subject})`;
                    break;
                }
            }

            if (_hasConflict) {
                warnEl.classList.remove('d-none');
                selEl.style.background = 'rgba(240,101,72,0.25)';
                selEl.style.border = '2px solid #f06548';
            } else {
                warnEl.classList.add('d-none');
                selEl.style.background = 'rgba(64,81,137,0.2)';
                selEl.style.border = '2px solid #405189';
            }
        }

        // Attach change listeners to time inputs
        document.getElementById('manual_time_start')?.addEventListener('change', checkConflict);
        document.getElementById('manual_time_end')?.addEventListener('change', checkConflict);

        // Block form submission on conflict
        document.getElementById('manualLectureForm')?.addEventListener('submit', function(e) {
            if (_hasConflict) {
                e.preventDefault();
                document.getElementById('conflict-warning').classList.remove('d-none');
                document.getElementById('manual-submit-btn').classList.add('shake');
                setTimeout(() => document.getElementById('manual-submit-btn').classList.remove('shake'), 500);
                return false;
            }
        });

        // Dynamically load groups when program is selected
        function loadGroupsForManual(programId) {
            const groupSel = document.getElementById('manual_group_id');
            groupSel.innerHTML = '<option value="">جاري التحميل...</option>';

            if (!programId) {
                groupSel.innerHTML = '<option value="">— اختياري —</option>';
                return;
            }

            fetch('{{ route('educational.api.programs.groups', ':id') }}'.replace(':id', programId))
                .then(r => r.json())
                .then(data => {
                    groupSel.innerHTML = '<option value="">— اختياري —</option>';
                    (data.groups || data || []).forEach(g => {
                        const opt = document.createElement('option');
                        opt.value = g.id;
                        opt.textContent = g.name;
                        groupSel.appendChild(opt);
                    });
                })
                .catch(() => {
                    groupSel.innerHTML = '<option value="">— اختياري —</option>';
                });
        }

        // ═══════════════════════════════════════════════════════════════
        // Edit Lecture Modal
        // ═══════════════════════════════════════════════════════════════
        let _editBookedSlots = [];
        let _editHasConflict = false;
        let _editCurrentLectureId = null; // exclude self from conflict check

        /** Open edit modal and pre-fill all fields */
        function openEditLectureModal(data) {
            _editCurrentLectureId = data.id;

            // Set form action
            const baseUrl = '{{ route('educational.lectures.update_details', ':id') }}';
            document.getElementById('editLectureForm').action = baseUrl.replace(':id', data.id);

            // Hidden fields
            document.getElementById('edit_room_id').value = data.room_id;
            document.getElementById('edit_lecture_date').value = data.date;

            // Room select pre-fill
            const roomSel = document.getElementById('edit_room_id_select');
            if (roomSel) {
                roomSel.value = data.room_id;
                document.getElementById('edit-room-label').textContent =
                    'القاعة: ' + (data.room_name || '');
            }

            // Time fields
            document.getElementById('edit_time_start').value = data.time_start || '';
            document.getElementById('edit_time_end').value = data.time_end || '';

            // Text/select fields
            document.getElementById('edit_subject').value = data.subject || '';
            document.getElementById('edit_notes').value = data.notes || '';

            // Program & group
            const progSel = document.getElementById('edit_program_id');
            progSel.value = data.program_id || '';
            if (data.program_id) {
                loadGroupsForEdit(data.program_id, data.group_id);
            } else {
                document.getElementById('edit_group_id').innerHTML =
                    '<option value="">— اختياري —</option>';
            }

            // Instructor
            const instSel = document.getElementById('edit_instructor_id');
            if (instSel) instSel.value = data.instructor_profile_id || '';

            // Session type
            const stSel = document.getElementById('edit_session_type_id');
            if (stSel) stSel.value = data.session_type_id || '';

            // Evaluation form
            const formSel = document.getElementById('edit_form_id');
            if (formSel) formSel.value = data.form_id || '';

            // Timeline
            document.getElementById('edit-booked-date-label').textContent = data.date;
            document.getElementById('edit-conflict-warning').classList.add('d-none');
            document.getElementById('edit-end-before-start-warning').classList.add('d-none');
            document.getElementById('edit-timeline-selected').style.display = 'none';
            _editHasConflict = false;

            fetchEditBookedSlots(data.room_id, data.date);

            new bootstrap.Modal(document.getElementById('editLectureModal')).show();
        }

        /** Load groups dynamically for edit modal */
        function loadGroupsForEdit(programId, selectedGroupId) {
            const groupSel = document.getElementById('edit_group_id');
            groupSel.innerHTML = '<option value="">جاري التحميل...</option>';

            if (!programId) {
                groupSel.innerHTML = '<option value="">— اختياري —</option>';
                return;
            }

            fetch('{{ route('educational.api.programs.groups', ':id') }}'.replace(':id', programId))
                .then(r => r.json())
                .then(data => {
                    groupSel.innerHTML = '<option value="">— اختياري —</option>';
                    (data.groups || data || []).forEach(g => {
                        const opt = document.createElement('option');
                        opt.value = g.id;
                        opt.textContent = g.name;
                        if (selectedGroupId && g.id == selectedGroupId) opt.selected = true;
                        groupSel.appendChild(opt);
                    });
                })
                .catch(() => {
                    groupSel.innerHTML = '<option value="">— اختياري —</option>';
                });
        }

        /** When room changes in edit modal — reload booked slots */
        function onEditRoomChange(roomId) {
            document.getElementById('edit_room_id').value = roomId;
            const selOpt = document.getElementById('edit_room_id_select').selectedOptions[0];
            if (selOpt) document.getElementById('edit-room-label').textContent = 'القاعة: ' + selOpt.dataset.name;

            const date = document.getElementById('edit_lecture_date').value;
            if (date) fetchEditBookedSlots(roomId, date);
        }

        /** Fetch booked slots for edit modal (same API, filtered server-side except self) */
        function fetchEditBookedSlots(roomId, date) {
            const listEl = document.getElementById('edit-booked-slots-list');
            const trackEl = document.getElementById('edit-timeline-track');
            listEl.innerHTML = '<span class="text-muted fs-12 fst-italic">جاري التحميل...</span>';
            trackEl.innerHTML = '';
            _editBookedSlots = [];

            const url = '{{ route('educational.api.rooms.booked_slots', ':rid') }}'
                .replace(':rid', roomId) +
                '?date=' + date + '&exclude=' + (_editCurrentLectureId || '');

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    _editBookedSlots = data.slots || [];
                    listEl.innerHTML = '';
                    trackEl.innerHTML = '';

                    if (_editBookedSlots.length === 0) {
                        listEl.innerHTML =
                            '<span class="text-muted fs-12 fst-italic">— لا توجد تعارضات محجوزة —</span>';
                    } else {
                        _editBookedSlots.forEach(slot => {
                            const colorMap = {
                                scheduled: 'primary',
                                running: 'warning',
                                completed: 'success'
                            };
                            const color = colorMap[slot.status] || 'secondary';
                            const badge = document.createElement('span');
                            badge.className =
                                `badge bg-${color}-subtle text-${color} fs-11 border border-${color}-subtle`;
                            badge.innerHTML =
                                `<i class="ri-time-line me-1"></i>${slot.start}–${slot.end} <small class="opacity-75">${slot.subject}</small>`;
                            listEl.appendChild(badge);

                            const left = (timeToFraction(slot.start) * 100).toFixed(2);
                            const width = ((timeToFraction(slot.end) - timeToFraction(slot.start)) * 100)
                                .toFixed(2);
                            const block = document.createElement('div');
                            block.style.cssText = `position:absolute;top:0;bottom:0;left:${left}%;width:${width}%;
                                background:rgba(240,101,72,0.35);border-radius:4px;border-left:2px solid #f06548;`;
                            block.title = `${slot.start}–${slot.end}: ${slot.subject}`;
                            trackEl.appendChild(block);
                        });
                    }
                    checkEditConflict();
                })
                .catch(() => {
                    listEl.innerHTML = '<span class="text-warning fs-12">تعذر تحميل الأوقات</span>';
                });
        }

        /** Conflict check for edit modal */
        function checkEditConflict() {
            const startVal = document.getElementById('edit_time_start').value;
            const endVal = document.getElementById('edit_time_end').value;
            const warnEl = document.getElementById('edit-conflict-warning');
            const msgEl = document.getElementById('edit-conflict-msg');
            const selEl = document.getElementById('edit-timeline-selected');
            const endWarn = document.getElementById('edit-end-before-start-warning');

            if (startVal && endVal && endVal <= startVal) {
                endWarn.classList.remove('d-none');
                _editHasConflict = true;
                selEl.style.display = 'none';
                return;
            }
            endWarn.classList.add('d-none');

            if (!startVal || !endVal) {
                warnEl.classList.add('d-none');
                selEl.style.display = 'none';
                _editHasConflict = false;
                return;
            }

            const left = (timeToFraction(startVal) * 100).toFixed(2);
            const width = ((timeToFraction(endVal) - timeToFraction(startVal)) * 100).toFixed(2);
            selEl.style.left = left + '%';
            selEl.style.width = width + '%';
            selEl.style.display = 'block';

            _editHasConflict = false;
            for (const slot of _editBookedSlots) {
                if (startVal < slot.end && endVal > slot.start) {
                    _editHasConflict = true;
                    msgEl.textContent = `يوجد تعارض مع محاضرة (${slot.start}–${slot.end}: ${slot.subject})`;
                    break;
                }
            }

            if (_editHasConflict) {
                warnEl.classList.remove('d-none');
                selEl.style.background = 'rgba(240,101,72,0.25)';
                selEl.style.border = '2px solid #f06548';
            } else {
                warnEl.classList.add('d-none');
                selEl.style.background = 'rgba(64,81,137,0.2)';
                selEl.style.border = '2px solid #405189';
            }
        }

        document.getElementById('edit_time_start')?.addEventListener('change', checkEditConflict);
        document.getElementById('edit_time_end')?.addEventListener('change', checkEditConflict);

        document.getElementById('editLectureForm')?.addEventListener('submit', function(e) {
            if (_editHasConflict) {
                e.preventDefault();
                document.getElementById('edit-conflict-warning').classList.remove('d-none');
                document.getElementById('edit-submit-btn').classList.add('shake');
                setTimeout(() => document.getElementById('edit-submit-btn').classList.remove('shake'), 500);
                return false;
            }
        });
    </script>
    <style>
        @keyframes shake {

            0%,
            100% {
                transform: translateX(0)
            }

            25% {
                transform: translateX(-6px)
            }

            75% {
                transform: translateX(6px)
            }
        }

        .shake {
            animation: shake 0.4s ease;
        }
    </style>
@endpush
