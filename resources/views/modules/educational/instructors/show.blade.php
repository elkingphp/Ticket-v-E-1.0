@extends('core::layouts.master')
@section('title', __('educational::messages.instructor_profile'))

@section('content')
@include('modules.educational.shared.alerts')

{{-- ══════════════════════════════ PROFILE BANNER ══════════════════════════════ --}}
<div class="row">
    <div class="col-lg-12">
        <div class="card mt-n4 mx-n4 border-0 rounded-0 shadow-lg profile-banner-premium">
            <div class="card-body pb-0 px-4 pt-4">

                {{-- Top Row: Avatar + Name + Actions --}}
                <div class="row mb-4 align-items-center">
                    <div class="col-md">
                        <div class="row align-items-center g-3">
                            <div class="col-md-auto">
                                <div class="profile-avatar-wrap position-relative">
                                    <div class="avatar-xl p-1 rounded-circle shadow-lg" style="background:rgba(255,255,255,0.15);backdrop-filter:blur(10px);border:2px solid rgba(255,255,255,0.4);">
                                        <div class="avatar-title bg-gradient-primary text-white rounded-circle fw-black fs-28 shadow-inner">
                                            {{ strtoupper(substr($instructor->user->first_name, 0, 1)) }}{{ strtoupper(substr($instructor->user->last_name, 0, 1)) }}
                                        </div>
                                    </div>
                                    @php
                                        $statusConfig = [
                                            'active'    => ['class' => 'success', 'icon' => 'ri-checkbox-circle-fill'],
                                            'inactive'  => ['class' => 'warning', 'icon' => 'ri-time-fill'],
                                            'suspended' => ['class' => 'danger',  'icon' => 'ri-error-warning-fill'],
                                        ];
                                        $conf = $statusConfig[$instructor->status] ?? ['class' => 'secondary', 'icon' => 'ri-question-fill'];
                                    @endphp
                                    <div class="status-dot position-absolute bottom-0 end-0 bg-{{ $conf['class'] }} border border-white border-2 rounded-circle shadow-sm"
                                         style="width:18px;height:18px;"
                                         title="{{ __('educational::messages.status_'.$instructor->status) }}"></div>
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="profile-name-shadow">
                                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                        <h3 class="fw-black mb-0 text-white">{{ $instructor->arabic_name ?? $instructor->user->full_name }}</h3>
                                        <span class="badge bg-{{ $conf['class'] }} text-white border-0 shadow-sm px-3 py-1 rounded-pill">
                                            <i class="{{ $conf['icon'] }} me-1 align-middle"></i>
                                            {{ __('educational::messages.status_'.$instructor->status) }}
                                        </span>
                                    </div>
                                    <div class="hstack gap-3 flex-wrap text-white-75 fs-14">
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="ri-medal-fill text-warning"></i>
                                            <span>{{ $instructor->track->name ?? '---' }}</span>
                                        </div>
                                        <div class="vr bg-white-50"></div>
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="ri-shield-user-fill"></i>
                                            <span>{{ __('educational::messages.instructor') }}</span>
                                        </div>
                                        <div class="vr bg-white-50"></div>
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="ri-briefcase-line"></i>
                                            <span>{{ __('educational::messages.'.$instructor->employment_type) }}</span>
                                        </div>
                                        @if($instructor->user->email)
                                        <div class="vr bg-white-50"></div>
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="ri-mail-line"></i>
                                            <span>{{ $instructor->user->email }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-auto mt-md-0 mt-3">
                        <div class="hstack gap-2 flex-wrap">
                            <a href="{{ route('educational.instructors.index') }}"
                               class="btn btn-glass-simple btn-icon rounded-pill"
                               title="{{ __('educational::messages.back_to_list') }}">
                                <i class="ri-arrow-left-line fs-20 text-white"></i>
                            </a>
                            <a href="{{ route('educational.instructors.edit', $instructor->id) }}"
                               class="btn btn-glass-premium btn-label rounded-pill shadow-lg">
                                <i class="ri-edit-2-line label-icon align-middle fs-18 me-2"></i>
                                {{ __('educational::messages.edit') }}
                            </a>
                            <button type="button"
                                    class="btn btn-glass-danger btn-icon rounded-pill shadow-lg pulse-danger"
                                    onclick="confirmDeleteInstructor({{ $instructor->id }})"
                                    title="{{ __('educational::messages.delete') }}">
                                <i class="ri-delete-bin-fill fs-18"></i>
                            </button>
                            <form id="delete-instructor-form-{{ $instructor->id }}"
                                  action="{{ route('educational.instructors.destroy', $instructor->id) }}"
                                  method="POST" class="d-none">
                                @csrf @method('DELETE')
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ── Stats Bar ── --}}
                <div class="row g-3 mb-3">
                    @php
                        $totalLectures    = \Illuminate\Support\Facades\DB::table('education.schedule_templates')
                            ->where('instructor_profile_id', $instructor->id)->count();
                        $totalGroups      = \Illuminate\Support\Facades\DB::table('education.schedule_templates')
                            ->where('instructor_profile_id', $instructor->id)->distinct('group_id')->count('group_id');
                        $assignedProfiles  = $assignments->sum(fn($p) => $p->count());
                        $assignedCompanies = $assignments->count();
                    @endphp
                    <div class="col-6 col-md-3">
                        <div class="stats-glass-card text-center p-3 rounded-3">
                            <div class="fs-22 fw-black text-white mb-0">{{ $totalLectures }}</div>
                            <div class="fs-12 text-white-75">{{ __('educational::messages.schedule_templates') ?? 'جداول' }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stats-glass-card text-center p-3 rounded-3">
                            <div class="fs-22 fw-black text-white mb-0">{{ $totalGroups }}</div>
                            <div class="fs-12 text-white-75">{{ __('educational::messages.groups') ?? 'مجموعات' }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stats-glass-card text-center p-3 rounded-3">
                            <div class="fs-22 fw-black text-white mb-0">{{ $assignedProfiles }}</div>
                            <div class="fs-12 text-white-75">{{ __('educational::messages.job_profiles') ?? 'ملفات وظيفية' }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stats-glass-card text-center p-3 rounded-3">
                            <div class="fs-22 fw-black text-white mb-0">{{ $assignedCompanies }}</div>
                            <div class="fs-12 text-white-75">{{ __('educational::messages.training_companies') ?? 'شركات تدريب' }}</div>
                        </div>
                    </div>
                </div>

                {{-- ── Tabs ── --}}
                <ul class="nav nav-tabs-custom-glass border-bottom-0" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active fw-bold px-4" data-bs-toggle="tab" href="#overview-tab" role="tab">
                            <i class="ri-user-6-fill me-1 align-bottom"></i> {{ __('educational::messages.personal_info') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold px-4" data-bs-toggle="tab" href="#academic-tab" role="tab">
                            <i class="ri-graduation-cap-fill me-1 align-bottom"></i> {{ __('educational::messages.academic_info') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold px-4" data-bs-toggle="tab" href="#assignments-tab" role="tab">
                            <i class="ri-links-fill me-1 align-bottom"></i> {{ __('educational::messages.assignments') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold px-4" data-bs-toggle="tab" href="#schedules-tab" role="tab">
                            <i class="ri-calendar-schedule-fill me-1 align-bottom"></i> {{ __('educational::messages.schedule_templates') ?? 'الجداول' }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════ CONTENT AREA ══════════════════════════════ --}}
<div class="row mt-4 g-4">

    {{-- ── Sidebar ── --}}
    <div class="col-xl-3 col-lg-4">

        {{-- Contact Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-soft-primary border-0 py-3">
                <h6 class="mb-0 fw-bold text-primary"><i class="ri-contacts-book-2-line me-2"></i>{{ __('educational::messages.contact_info') }}</h6>
            </div>
            <div class="card-body pt-3">
                <div class="info-item d-flex align-items-start mb-3">
                    <div class="avatar-xs flex-shrink-0 me-3">
                        <div class="avatar-title bg-soft-info text-info rounded-circle"><i class="ri-mail-line"></i></div>
                    </div>
                    <div>
                        <div class="text-muted small mb-0">{{ __('educational::messages.email') }}</div>
                        <div class="fw-medium text-break">{{ $instructor->user->email }}</div>
                    </div>
                </div>

                <div class="info-item d-flex align-items-start mb-3">
                    <div class="avatar-xs flex-shrink-0 me-3">
                        <div class="avatar-title bg-soft-success text-success rounded-circle"><i class="ri-phone-line"></i></div>
                    </div>
                    <div>
                        <div class="text-muted small mb-0">{{ __('educational::messages.phone') }}</div>
                        <div class="fw-medium">{{ $instructor->user->phone ?? '---' }}</div>
                    </div>
                </div>

                <div class="info-item d-flex align-items-start mb-3">
                    <div class="avatar-xs flex-shrink-0 me-3">
                        <div class="avatar-title bg-soft-danger text-danger rounded-circle"><i class="ri-map-pin-2-line"></i></div>
                    </div>
                    <div>
                        <div class="text-muted small mb-0">{{ __('educational::messages.governorate') }}</div>
                        <div class="fw-medium">{{ $instructor->governorate->name ?? '---' }}</div>
                    </div>
                </div>

                @if($instructor->address)
                <div class="info-item d-flex align-items-start">
                    <div class="avatar-xs flex-shrink-0 me-3">
                        <div class="avatar-title bg-soft-warning text-warning rounded-circle"><i class="ri-home-line"></i></div>
                    </div>
                    <div>
                        <div class="text-muted small mb-0">{{ __('educational::messages.address') }}</div>
                        <div class="fw-medium">{{ $instructor->address }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Account Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-soft-secondary border-0 py-3">
                <h6 class="mb-0 fw-bold text-secondary"><i class="ri-account-box-line me-2"></i>{{ __('educational::messages.account_details') }}</h6>
            </div>
            <div class="card-body pt-3">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-bottom-dashed">
                    <span class="text-muted small">{{ __('educational::messages.username') }}</span>
                    <span class="badge bg-light text-dark fw-medium">@ {{ $instructor->user->username }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-bottom-dashed">
                    <span class="text-muted small">{{ __('educational::messages.joining_date') }}</span>
                    <span class="fw-medium">{{ $instructor->user->created_at->format('Y/m/d') }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">{{ __('educational::messages.status') }}</span>
                    <span class="badge bg-{{ $conf['class'] }} rounded-pill px-3">
                        <i class="{{ $conf['icon'] }} me-1"></i>
                        {{ __('educational::messages.status_'.$instructor->status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Quick Actions Card --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-soft-dark border-0 py-3">
                <h6 class="mb-0 fw-bold"><i class="ri-flashlight-line me-2 text-warning"></i>إجراءات سريعة</h6>
            </div>
            <div class="card-body p-3">
                <div class="d-grid gap-2">
                    <a href="{{ route('educational.instructors.edit', $instructor->id) }}" class="btn btn-soft-info btn-sm">
                        <i class="ri-edit-2-line me-1"></i> {{ __('educational::messages.edit') }}
                    </a>
                    <button type="button" class="btn btn-soft-danger btn-sm" onclick="confirmDeleteInstructor({{ $instructor->id }})">
                        <i class="ri-delete-bin-line me-1"></i> {{ __('educational::messages.delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main Content ── --}}
    <div class="col-xl-9 col-lg-8">
        <div class="tab-content">

            {{-- ─── TAB: Personal Info ─── --}}
            <div class="tab-pane active" id="overview-tab" role="tabpanel">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-0 py-3 bg-white">
                        <h5 class="card-title mb-0 fw-bold"><i class="ri-user-6-line me-2 text-primary"></i>{{ __('educational::messages.personal_info') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="info-field p-3 bg-light rounded-3">
                                    <div class="text-muted small mb-1">{{ __('educational::messages.arabic_name') }}</div>
                                    <div class="fw-bold text-dark fs-15">{{ $instructor->arabic_name ?? '---' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-field p-3 bg-light rounded-3">
                                    <div class="text-muted small mb-1">{{ __('educational::messages.english_name') }}</div>
                                    <div class="fw-bold text-dark fs-15">{{ $instructor->english_name ?? '---' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-field p-3 bg-light rounded-3">
                                    <div class="text-muted small mb-1">{{ __('educational::messages.gender') }}</div>
                                    <div class="fw-bold text-dark">
                                        @if($instructor->gender === 'male')
                                            <i class="ri-men-line text-info me-1"></i>
                                        @elseif($instructor->gender === 'female')
                                            <i class="ri-women-line text-danger me-1"></i>
                                        @endif
                                        {{ $instructor->gender ? __('educational::messages.'.$instructor->gender) : '---' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-field p-3 bg-light rounded-3">
                                    <div class="text-muted small mb-1">{{ __('educational::messages.date_of_birth') }}</div>
                                    <div class="fw-bold text-dark">{{ $instructor->date_of_birth ? $instructor->date_of_birth->format('Y-m-d') : '---' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-field p-3 bg-light rounded-3">
                                    <div class="text-muted small mb-1">{{ __('educational::messages.national_id') }}</div>
                                    <div class="fw-bold text-dark font-monospace">{{ $instructor->getSensitiveData('national_id') ?? '---' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bio --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-3 bg-white">
                        <h5 class="card-title mb-0 fw-bold"><i class="ri-file-text-line me-2 text-info"></i>{{ __('educational::messages.bio') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="p-4 bg-light rounded-3 border-start border-4 border-primary" style="min-height:80px;">
                            <p class="mb-0 text-muted">{{ $instructor->bio ?? __('educational::messages.no_bio_entry') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─── TAB: Academic Info ─── --}}
            <div class="tab-pane" id="academic-tab" role="tabpanel">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-0 py-3 bg-white">
                        <h5 class="card-title mb-0 fw-bold"><i class="ri-graduation-cap-line me-2 text-success"></i>{{ __('educational::messages.employment_info') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="info-field p-4 rounded-3 border border-dashed border-primary h-100">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar-sm flex-shrink-0 me-3">
                                            <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                <i class="ri-briefcase-line fs-20"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-muted small">{{ __('educational::messages.employment_type') }}</div>
                                            <div class="fw-bold text-dark fs-15">{{ __('educational::messages.'.$instructor->employment_type) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-field p-4 rounded-3 border border-dashed border-info h-100">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar-sm flex-shrink-0 me-3">
                                            <div class="avatar-title bg-soft-info text-info rounded-circle">
                                                <i class="ri-book-read-line fs-20"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-muted small">{{ __('educational::messages.specialization') }}</div>
                                            <div class="fw-bold text-dark fs-15">{{ $instructor->track->name ?? '---' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($instructor->specialization_notes)
                        <div class="mt-4">
                            <h6 class="fw-bold mb-2 text-muted small text-uppercase">{{ __('educational::messages.specialization_notes') }}</h6>
                            <div class="p-3 bg-light rounded-3 border-start border-4 border-info">
                                <p class="mb-0">{{ $instructor->specialization_notes }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ─── TAB: Assignments ─── --}}
            <div class="tab-pane" id="assignments-tab" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-3 bg-white d-flex align-items-center">
                        <h5 class="card-title flex-grow-1 mb-0 fw-bold">
                            <i class="ri-links-line me-2 text-warning"></i>{{ __('educational::messages.assignments') }}
                        </h5>
                        <span class="badge bg-soft-warning text-warning rounded-pill px-3 fs-13">
                            {{ $assignments->count() }} {{ __('educational::messages.assigned_companies') }}
                        </span>
                    </div>
                    <div class="card-body">
                        @forelse($assignments as $companyName => $profiles)
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-sm flex-shrink-0 me-3">
                                        <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                            <i class="ri-building-4-line fs-18"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ $companyName }}</h6>
                                        <small class="text-muted">{{ $profiles->count() }} {{ __('educational::messages.job_profiles') ?? 'ملف وظيفي' }}</small>
                                    </div>
                                </div>
                                <div class="row g-2 ps-5">
                                    @foreach($profiles as $profile)
                                        <div class="col-md-6 col-xl-4">
                                            <div class="p-3 border rounded-3 bg-white shadow-sm d-flex align-items-center gap-2 h-100">
                                                <div class="avatar-xs flex-shrink-0">
                                                    <div class="avatar-title bg-soft-success text-success rounded-circle">
                                                        <i class="ri-checkbox-circle-line"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="fw-medium fs-13">{{ $profile->profile_name }}</div>
                                                    <small class="text-muted font-monospace">{{ $profile->profile_code }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @if(!$loop->last)<hr class="border-light">@endif
                        @empty
                            <div class="text-center py-5">
                                <div class="avatar-xl mx-auto mb-3">
                                    <div class="avatar-title bg-light text-muted rounded-circle fs-2">
                                        <i class="ri-links-line"></i>
                                    </div>
                                </div>
                                <h6 class="text-muted mb-1">{{ __('educational::messages.no_assignments_found') }}</h6>
                                <p class="text-muted small mb-0">لم يتم ربط هذا المدرب بأي شركة أو ملف وظيفي بعد.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ─── TAB: Schedules ─── --}}
            <div class="tab-pane" id="schedules-tab" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-3 bg-white d-flex align-items-center">
                        <h5 class="card-title flex-grow-1 mb-0 fw-bold">
                            <i class="ri-calendar-schedule-line me-2 text-info"></i>{{ __('educational::messages.schedule_templates') ?? 'الجداول المتكررة' }}
                        </h5>
                        <span class="badge bg-soft-info text-info rounded-pill px-3 fs-13">{{ $totalLectures }}</span>
                    </div>
                    <div class="card-body p-0">
                        @php
                            $schedules = \Illuminate\Support\Facades\DB::table('education.schedule_templates as st')
                                ->leftJoin('education.groups as g', 'st.group_id', '=', 'g.id')
                                ->leftJoin('education.rooms as r', 'st.room_id', '=', 'r.id')
                                ->leftJoin('education.session_types as stype', 'st.session_type_id', '=', 'stype.id')
                                ->where('st.instructor_profile_id', $instructor->id)
                                ->select(
                                    'st.id', 'st.day_of_week', 'st.start_time', 'st.end_time',
                                    'st.is_active', 'st.recurrence_type',
                                    'g.name as group_name',
                                    'r.name as room_name',
                                    'stype.name as session_type_name'
                                )
                                ->latest('st.id')
                                ->get();
                        @endphp
                        @if($schedules->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">المجموعة</th>
                                        <th>اليوم</th>
                                        <th>الوقت</th>
                                        <th>القاعة</th>
                                        <th>نوع الجلسة</th>
                                        <th class="text-center">الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedules as $schedule)
                                    @php
                                        $days = [0=>'الأحد',1=>'الإثنين',2=>'الثلاثاء',3=>'الأربعاء',4=>'الخميس',5=>'الجمعة',6=>'السبت'];
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-medium">{{ $schedule->group_name ?? '---' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-soft-primary text-primary rounded-pill px-3">{{ $days[$schedule->day_of_week] ?? '---' }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ substr($schedule->start_time, 0, 5) }}</span>
                                            <span class="text-muted mx-1">→</span>
                                            <span class="fw-medium">{{ substr($schedule->end_time, 0, 5) }}</span>
                                        </td>
                                        <td class="text-muted small">{{ $schedule->room_name ?? '---' }}</td>
                                        <td>
                                            @if($schedule->session_type_name)
                                                <span class="badge bg-soft-secondary text-secondary rounded-pill">{{ $schedule->session_type_name }}</span>
                                            @else
                                                <span class="text-muted">---</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($schedule->is_active)
                                                <span class="badge bg-success-subtle text-success rounded-pill px-3"><i class="ri-check-line me-1"></i>نشط</span>
                                            @else
                                                <span class="badge bg-light text-muted rounded-pill px-3"><i class="ri-pause-line me-1"></i>موقوف</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                            <div class="text-center py-5">
                                <div class="avatar-xl mx-auto mb-3">
                                    <div class="avatar-title bg-light text-muted rounded-circle fs-2">
                                        <i class="ri-calendar-schedule-line"></i>
                                    </div>
                                </div>
                                <h6 class="text-muted mb-1">لا توجد جداول مسجلة</h6>
                                <p class="text-muted small mb-0">لم يُعيَّن هذا المدرب في أي جدول متكرر بعد.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* ── Banner Gradient ── */
    .profile-banner-premium {
        background: linear-gradient(135deg, #624bff 0%, #3e1b74 50%, #1e3a8a 100%);
        position: relative;
        overflow: hidden;
    }
    .profile-banner-premium::before {
        content: "";
        position: absolute;
        inset: 0;
        background: url('https://www.transparenttextures.com/patterns/cubes.png');
        opacity: 0.07;
        pointer-events: none;
    }

    /* ── Stats Glass Cards ── */
    .stats-glass-card {
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: transform 0.25s ease, background 0.25s ease;
    }
    .stats-glass-card:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-3px);
    }

    /* ── Tabs ── */
    .nav-tabs-custom-glass { gap: 6px; padding-bottom: 2px; }
    .nav-tabs-custom-glass .nav-link {
        color: rgba(255,255,255,0.65);
        border: none;
        padding: 10px 18px;
        border-radius: 10px 10px 0 0;
        transition: all 0.25s ease;
        position: relative;
    }
    .nav-tabs-custom-glass .nav-link:hover { color: #fff; background: rgba(255,255,255,0.1); }
    .nav-tabs-custom-glass .nav-link.active {
        color: #fff !important;
        background: rgba(255,255,255,0.18);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    .nav-tabs-custom-glass .nav-link.active::after {
        content: "";
        position: absolute;
        bottom: 0; left: 20%; right: 20%;
        height: 3px;
        background: #fff;
        border-radius: 2px 2px 0 0;
        box-shadow: 0 -2px 8px rgba(255,255,255,0.4);
    }

    /* ── Glass Buttons ── */
    .btn-glass-premium {
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff !important;
        transition: all 0.25s ease;
    }
    .btn-glass-premium:hover {
        background: rgba(255,255,255,0.25);
        border-color: rgba(255,255,255,0.5);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .btn-glass-simple {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.15);
        color: #fff;
        transition: all 0.2s;
    }
    .btn-glass-simple:hover { background: rgba(255,255,255,0.2); color: #fff; }
    .btn-glass-danger {
        background: rgba(240,101,72,0.2);
        border: 1px solid rgba(240,101,72,0.4);
        color: #fff !important;
        transition: all 0.2s;
    }
    .btn-glass-danger:hover { background: rgba(240,101,72,0.4); border-color: #f06548; }

    /* ── Avatar ── */
    .profile-avatar-wrap { transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .profile-avatar-wrap:hover { transform: scale(1.05) rotate(2deg); }
    .bg-gradient-primary { background: linear-gradient(135deg, #5a70d8, #9b72f5); }

    /* ── Info Fields ── */
    .info-field { transition: box-shadow 0.2s ease; }
    .info-field:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.06); }

    /* ── Helpers ── */
    .profile-name-shadow { text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
    .fw-black  { font-weight: 800; }
    .bg-white-50  { background-color: rgba(255,255,255,0.2); }
    .text-white-75 { color: rgba(255,255,255,0.75) !important; }
    .border-dashed { border-style: dashed !important; }
    .font-monospace { font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; }

    /* ── Delete Button Pulse ── */
    .pulse-danger { animation: pulse-red 2.5s infinite; }
    @keyframes pulse-red {
        0%   { box-shadow: 0 0 0 0 rgba(240,101,72,0.6); }
        70%  { box-shadow: 0 0 0 10px rgba(240,101,72,0); }
        100% { box-shadow: 0 0 0 0 rgba(240,101,72,0); }
    }

    /* ── Table ── */
    .table-hover tbody tr:hover { background-color: rgba(98,75,255,0.04); }
</style>
@endpush

@push('scripts')
<script>
    function confirmDeleteInstructor(id) {
        Swal.fire({
            title: 'هل أنت متأكد من حذف المحاضر؟',
            text: "لن تتمكن من استعادة بيانات هذا المحاضر بعد الحذف!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، قم بالحذف',
            cancelButtonText: 'إلغاء',
            customClass: {
                confirmButton: 'btn btn-danger rounded-pill px-4 me-2',
                cancelButton:  'btn btn-light rounded-pill px-4',
            },
            buttonsStyling: false,
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-instructor-form-' + id).submit();
            }
        });
    }
</script>
@endpush
