@extends('core::layouts.master')
@section('title', $student->arabic_name ?? $student->user->full_name)

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="card mt-n4 mx-n4 border-0 profile-header-premium">
                <div class="card-body pb-0 px-4">
                    <div class="row mb-3 align-items-center">
                        <div class="col-md">
                            <div class="row align-items-center g-3">
                                <div class="col-md-auto">
                                    <div class="avatar-lg p-1 bg-white-50 rounded-circle shadow-lg profile-avatar-wrap">
                                        <div
                                            class="avatar-title bg-white text-primary rounded-circle shadow-sm overflow-hidden border border-3 border-white">
                                            @if ($student->user->avatar)
                                                <img src="{{ Storage::url($student->user->avatar) }}" alt=""
                                                    class="img-fluid rounded-circle">
                                            @else
                                                <div class="avatar-title-text fw-black text-primary">
                                                    {{ mb_strtoupper(mb_substr($student->user->first_name, 0, 1) . mb_substr($student->user->last_name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div>
                                        <h3 class="fw-bold mb-1 text-white profile-name-shadow">
                                            {{ $student->arabic_name ?? $student->user->full_name }}</h3>
                                        <div class="hstack gap-3 flex-wrap text-white-75">
                                            <div class="d-flex align-items-center text-white"><i
                                                    class="ri-building-line align-bottom me-1 fs-16"></i>
                                                {{ $student->program->name ?? '---' }}</div>
                                            <div class="vr bg-white-50"></div>
                                            <div class="d-flex align-items-center text-white"><i
                                                    class="ri-map-pin-2-line align-bottom me-1 fs-16"></i>
                                                {{ $student->governorate->name ?? '---' }}</div>
                                            <div class="vr bg-white-50"></div>
                                            <div class="d-flex align-items-center">
                                                <span
                                                    class="badge badge-glow bg-{{ $student->enrollment_status == 'active' ? 'success' : 'danger' }} px-3 py-2 rounded-pill fs-11">
                                                    <i class="ri-checkbox-circle-line align-bottom me-1"></i>
                                                    {{ __('educational::messages.' . $student->enrollment_status) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-auto mt-md-0 mt-3">
                            <div class="hstack gap-2 flex-wrap">
                                <a href="{{ route('educational.students.index') }}"
                                    class="btn btn-inst-secondary btn-icon rounded-pill shadow-none"
                                    title="{{ __('educational::messages.back_to_list') }}">
                                    <i class="ri-arrow-left-line fs-20 text-white"></i>
                                </a>
                                <a href="{{ route('educational.students.edit', $student->id) }}"
                                    class="btn btn-inst-primary btn-label rounded-pill">
                                    <i class="ri-palette-line label-icon align-middle fs-18"></i>
                                    {{ __('educational::messages.edit') }}
                                </a>
                                <button type="button" class="btn btn-inst-danger btn-label rounded-pill shadow-none"
                                    onclick="confirmDeleteStudent({{ $student->id }})">
                                    <i class="ri-delete-bin-fill label-icon align-middle fs-18"></i>
                                    {{ __('educational::messages.delete') }}
                                </button>
                                <form id="delete-student-form-{{ $student->id }}"
                                    action="{{ route('educational.students.destroy', $student->id) }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs-custom border-bottom-0 custom-white-nav" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active fw-bold" data-bs-toggle="tab" href="#overview-tab" role="tab">
                                {{ __('educational::messages.overview') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" data-bs-toggle="tab" href="#schedule-tab" role="tab">
                                {{ __('educational::messages.academic_schedule') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" data-bs-toggle="tab" href="#attendance-tab" role="tab">
                                {{ __('educational::messages.attendance_log') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" data-bs-toggle="tab" href="#complaints-tab" role="tab">
                                {{ __('educational::messages.complaints_requests') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" data-bs-toggle="tab" href="#documents-tab" role="tab">
                                {{ __('educational::messages.documents_attachments') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="tab-content text-muted">
                <div class="tab-pane active" id="overview-tab" role="tabpanel">
                    <div class="row">
                        <div class="col-xxl-3">
                            <div class="card shadow-sm border-0">
                                <div class="card-body">
                                    <h5 class="card-title mb-3 fw-bold text-primary">
                                        {{ __('educational::messages.basic_data') }}</h5>
                                    <div class="table-responsive">
                                        <table class="table table-borderless mb-0 align-middle">
                                            <tbody>
                                                <tr class="border-bottom border-light-subtle">
                                                    <th class="ps-0 py-3 text-muted" scope="row"><i
                                                            class="ri-user-settings-line me-2"></i>
                                                        {{ __('educational::messages.username') }}:</th>
                                                    <td class="fw-medium text-end">{{ $student->user->username }}</td>
                                                </tr>
                                                <tr class="border-bottom border-light-subtle">
                                                    <th class="ps-0 py-3 text-muted" scope="row"><i
                                                            class="ri-mail-line me-2"></i>
                                                        {{ __('educational::messages.email') }}:</th>
                                                    <td class="fw-medium text-end">{{ $student->user->email }}</td>
                                                </tr>
                                                <tr class="border-bottom border-light-subtle">
                                                    <th class="ps-0 py-3 text-muted" scope="row"><i
                                                            class="ri-phone-line me-2"></i>
                                                        {{ __('educational::messages.phone') }}:</th>
                                                    <td class="fw-medium text-end">{{ $student->user->phone ?? '---' }}
                                                    </td>
                                                </tr>
                                                <tr class="border-bottom border-light-subtle">
                                                    <th class="ps-0 py-3 text-muted" scope="row"><i
                                                            class="ri-fingerprint-line me-2"></i>
                                                        {{ __('educational::messages.national_id_label') }}:</th>
                                                    <td class="fw-medium text-end">
                                                        {{ $student->revealSensitive('national_id') ?? '---' }}</td>
                                                </tr>
                                                <tr class="border-bottom border-light-subtle">
                                                    <th class="ps-0 py-3 text-muted" scope="row"><i
                                                            class="ri-genderless-line me-2"></i>
                                                        {{ __('educational::messages.gender') }}:</th>
                                                    <td class="fw-medium text-end">
                                                        <span
                                                            class="badge bg-soft-info text-info">{{ $student->gender == 'male' ? __('educational::messages.male') : __('educational::messages.female') }}</span>
                                                    </td>
                                                </tr>
                                                <tr class="border-bottom border-light-subtle">
                                                    <th class="ps-0 py-3 text-muted" scope="row"><i
                                                            class="ri-flag-line me-2"></i>
                                                        {{ __('educational::messages.nationality') }}:</th>
                                                    <td class="fw-medium text-end">{{ $student->nationality ?? '---' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0 py-3 text-muted" scope="row"><i
                                                            class="ri-calendar-event-line me-2"></i>
                                                        {{ __('educational::messages.date_of_birth') }}:</th>
                                                    <td class="fw-medium text-end">
                                                        {{ optional($student->date_of_birth)->format('Y-m-d') ?? '---' }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            @if ($student->emergencyContacts && $student->emergencyContacts->count() > 0)
                                <div class="card shadow-sm border-0">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3 fw-bold text-primary">
                                            {{ __('educational::messages.emergency_contacts') }}</h5>
                                        <div class="vstack gap-2">
                                            @foreach ($student->emergencyContacts as $contact)
                                                <div
                                                    class="d-flex align-items-center p-2 rounded-3 bg-light-subtle border border-dashed border-light">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar-xs">
                                                            <div
                                                                class="avatar-title bg-soft-primary text-primary rounded-circle fw-bold">
                                                                {{ mb_substr($contact->name, 0, 1) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-0 fs-13 fw-bold">{{ $contact->name }}</h6>
                                                        <p class="text-muted mb-0 fs-12"><span
                                                                class="badge bg-soft-info text-info me-1">{{ $contact->relation }}</span>
                                                            {{ $contact->phone }}</p>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <a href="tel:{{ $contact->phone }}"
                                                            class="btn btn-sm btn-icon btn-soft-success rounded-circle"><i
                                                                class="ri-phone-fill"></i></a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-xxl-9">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="card premium-stat-card border-0 shadow-sm"
                                        style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-grow-1">
                                                    <h6 class="text-white-50 text-uppercase fw-bold fs-12 mb-0">
                                                        {{ __('educational::messages.attendance_percentage') }}</h6>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm">
                                                        <div class="avatar-title glass-bg text-white rounded-circle fs-20">
                                                            <i class="ri-user-follow-line"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <h2 class="fw-black text-white mb-1"><span class="counter-value"
                                                        data-target="{{ $attendancePercentage }}">{{ $attendancePercentage }}</span>%
                                                </h2>
                                                <div class="progress progress-sm glass-bg mt-2" style="height: 6px;">
                                                    <div class="progress-bar bg-{{ $attendancePercentage > 85 ? 'success' : ($attendancePercentage > 65 ? 'warning' : 'danger') }}"
                                                        role="progressbar" style="width: {{ $attendancePercentage }}%"
                                                        aria-valuenow="{{ $attendancePercentage }}" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <div class="p-2 rounded-3 glass-bg text-center">
                                                        <h6 class="text-white-50 fs-10 mb-1 text-uppercase">تأخير</h6>
                                                        <span class="text-white fw-bold fs-14">{{ $lateCount }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="p-2 rounded-3 glass-bg text-center">
                                                        <h6 class="text-white-50 fs-10 mb-1 text-uppercase">غياب</h6>
                                                        <span class="text-white fw-bold fs-14">{{ $absentCount }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="text-white-50 fs-10 mt-3 mb-0">تم احتساب النسبة من <span
                                                    class="text-white fw-bold">{{ $totalPassedLectures }}</span> محاضرة
                                                مكتملة</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="card premium-stat-card border-0 shadow-sm"
                                        style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-grow-1">
                                                    <h6 class="text-white-50 text-uppercase fw-bold fs-12 mb-0">
                                                        {{ __('educational::messages.open_complaints') }}</h6>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm">
                                                        <div class="avatar-title glass-bg text-white rounded-circle fs-20">
                                                            <i class="ri-ticket-2-line"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <h2 class="fw-black text-white mb-1"><span class="counter-value"
                                                        data-target="{{ $openTicketsCount }}">{{ $openTicketsCount }}</span>
                                                </h2>
                                                @php
                                                    $resolvedCount = $totalTicketsCount - $openTicketsCount;
                                                    $complaintRate =
                                                        $totalTicketsCount > 0
                                                            ? ($resolvedCount / $totalTicketsCount) * 100
                                                            : 100;
                                                @endphp
                                                <div class="progress progress-sm glass-bg mt-2" style="height: 6px;">
                                                    <div class="progress-bar bg-info" role="progressbar"
                                                        style="width: {{ $complaintRate }}%"
                                                        aria-valuenow="{{ $complaintRate }}" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <div class="p-2 rounded-3 glass-bg text-center">
                                                        <h6 class="text-white-50 fs-10 mb-1 text-uppercase">الإجمالي</h6>
                                                        <span
                                                            class="text-white fw-bold fs-14">{{ $totalTicketsCount }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="p-2 rounded-3 glass-bg text-center">
                                                        <h6 class="text-white-50 fs-10 mb-1 text-uppercase">تم الحل</h6>
                                                        <span
                                                            class="text-white fw-bold fs-14">{{ $totalTicketsCount - $openTicketsCount }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="text-white-50 fs-10 mt-3 mb-0">معدل حل الشكاوى: <span
                                                    class="text-white fw-bold">{{ round($complaintRate) }}%</span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="card premium-stat-card border-0 shadow-sm"
                                        style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-grow-1">
                                                    <h6 class="text-white-50 text-uppercase fw-bold fs-12 mb-0">
                                                        {{ __('educational::messages.joining_date') }}</h6>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm">
                                                        <div class="avatar-title glass-bg text-white rounded-circle fs-20">
                                                            <i class="ri-calendar-event-line"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <h2 class="fw-black text-white mb-1">
                                                    {{ $student->created_at->format('Y-m-d') }}</h2>
                                                <div class="progress progress-sm glass-bg mt-2" style="height: 6px;">
                                                    <div class="progress-bar bg-primary" role="progressbar"
                                                        style="width: 100%" aria-valuenow="100" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <div class="p-2 rounded-3 glass-bg text-center">
                                                        <h6 class="text-white-50 fs-10 mb-1 text-uppercase">منذ</h6>
                                                        <span
                                                            class="text-white fw-bold fs-14">{{ number_format($student->created_at->diffInHours(now()) / 24, 2) }}
                                                            يوم</span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="p-2 rounded-3 glass-bg text-center">
                                                        <h6 class="text-white-50 fs-10 mb-1 text-uppercase">الحالة</h6>
                                                        <span
                                                            class="text-white fw-bold fs-14">{{ __('educational::messages.' . $student->enrollment_status) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="text-white-50 fs-10 mt-3 mb-0">آخر تحديث للملف: <span
                                                    class="text-white fw-bold">{{ $student->updated_at->format('Y-m-d') }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm">
                                <div class="card-header border-0 align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1 fw-bold">
                                        {{ __('educational::messages.current_academic_path') }}</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center g-4">
                                        <div class="col-md-3 border-end">
                                            <p class="text-muted mb-1 fs-12 text-uppercase">
                                                {{ __('educational::messages.program') }}</p>
                                            <h6 class="fw-bold">{{ $student->program->name ?? '---' }}</h6>
                                        </div>
                                        <div class="col-md-3 border-end">
                                            <p class="text-muted mb-1 fs-12 text-uppercase">
                                                {{ __('educational::messages.group') }}</p>
                                            <h6 class="fw-bold">{{ $student->group->name ?? '---' }}</h6>
                                        </div>
                                        <div class="col-md-3 border-end">
                                            <p class="text-muted mb-1 fs-12 text-uppercase">
                                                {{ __('educational::messages.specialization') }}</p>
                                            <h6 class="fw-bold">{{ $student->jobProfile->name ?? '---' }}</h6>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="text-muted mb-1 fs-12 text-uppercase">
                                                {{ __('educational::messages.joining_date') }}</p>
                                            <h6 class="fw-bold">{{ $student->created_at->format('Y-m-d') }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-premium overflow-hidden">
                                <div class="card-header bg-white border-0 align-items-center d-flex py-3">
                                    <div class="flex-grow-1">
                                        <h4 class="card-title mb-0 fw-black text-primary"><i
                                                class="ri-history-line me-1 align-middle"></i>
                                            {{ __('educational::messages.activity_log') }}</h4>
                                        <p class="text-muted mb-0 fs-11">تتبع آخر العمليات التي تمت على ملف الطالب</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span
                                            class="badge bg-soft-primary text-primary px-3 rounded-pill border border-primary-subtle">{{ __('educational::messages.last_10_updates') }}</span>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <div class="acitivity-timeline-modern">
                                        @forelse($activities as $activity)
                                            <div class="acitivity-item-modern d-flex">
                                                <div class="flex-shrink-0 acitivity-line-container">
                                                    <div
                                                        class="acitivity-avatar-modern shadow-sm {{ $activity->event == 'created' ? 'bg-success' : ($activity->event == 'updated' ? 'bg-info' : 'bg-danger') }}">
                                                        <i
                                                            class="ri-{{ $activity->event == 'created' ? 'add' : ($activity->event == 'updated' ? 'edit' : 'delete-bin') }}-line text-white"></i>
                                                    </div>
                                                    <div class="acitivity-line"></div>
                                                </div>
                                                <div class="flex-grow-1 ms-3 pb-4">
                                                    <div
                                                        class="acitivity-content-box p-3 rounded-4 border border-light-subtle bg-light-subtle shadow-sm transition-all hover-shadow">
                                                        <div
                                                            class="d-flex justify-content-between align-items-center mb-2">
                                                            <span
                                                                class="badge rounded-pill {{ $activity->event == 'created' ? 'bg-soft-success text-success' : ($activity->event == 'updated' ? 'bg-soft-info text-info' : 'bg-soft-danger text-danger') }} px-3">
                                                                {{ strtoupper($activity->event) }}
                                                            </span>
                                                            <span class="text-muted fs-11 fw-bold"><i
                                                                    class="ri-time-line align-bottom me-1 text-primary"></i>
                                                                {{ $activity->created_at->diffForHumans() }}</span>
                                                        </div>
                                                        <h6 class="mb-2 fw-black fs-14">
                                                            @if ($activity->event == 'created')
                                                                {{ __('educational::messages.created_record') ?? 'تم إنشاء سجل جديد في' }}
                                                                <span
                                                                    class="text-primary">{{ $activity->category }}</span>
                                                            @elseif($activity->event == 'updated')
                                                                {{ __('educational::messages.updated_record') ?? 'تم تعديل بيانات' }}
                                                                <span class="text-info">{{ $activity->category }}</span>
                                                            @else
                                                                {{ __('educational::messages.deleted_record') ?? 'تم حذف سجل من' }}
                                                                <span class="text-danger">{{ $activity->category }}</span>
                                                            @endif
                                                        </h6>
                                                        <div
                                                            class="d-flex align-items-center mt-3 pt-2 border-top border-light">
                                                            <div class="avatar-xs flex-shrink-0 me-2">
                                                                <div
                                                                    class="avatar-title bg-soft-primary text-primary rounded-circle fs-10 fw-bold">
                                                                    {{ mb_substr($activity->user->full_name ?? 'S', 0, 1) }}
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <p class="mb-0 fs-12 fw-medium text-dark">
                                                                    {{ $activity->user->full_name ?? __('educational::messages.system') }}
                                                                </p>
                                                                <p class="mb-0 fs-10 text-muted opacity-75">
                                                                    @if ($activity->ip_address)
                                                                        <i class="ri-global-line me-1"></i>
                                                                        {{ $activity->ip_address }}
                                                                    @endif
                                                                    <span class="mx-1">•</span> <i
                                                                        class="ri-calendar-line me-1"></i>
                                                                    {{ $activity->created_at->format('Y-m-d H:i') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5">
                                                <div class="avatar-lg mx-auto mb-3">
                                                    <div class="avatar-title bg-light text-muted rounded-circle fs-32">
                                                        <i class="ri-history-line"></i>
                                                    </div>
                                                </div>
                                                <h5 class="fw-bold text-muted">
                                                    {{ __('educational::messages.no_activities_recorded') ?? 'لا يوجد سجل عمليات حتى الآن' }}
                                                </h5>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="schedule-tab" role="tabpanel">
                    <div class="row">
                        <div class="col-xl-9">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body p-3">
                                    <form action="{{ request()->fullUrl() }}" method="GET"
                                        class="row g-3 align-items-center">
                                        <div class="col-md-auto">
                                            <h5 class="fw-black mb-0 me-3 text-primary"><i
                                                    class="ri-calendar-event-line"></i>
                                                {{ __('educational::messages.academic_schedule') }}</h5>
                                        </div>
                                        <div class="col-md">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light border-0">من</span>
                                                <input type="date" name="from_date" class="form-control border-light"
                                                    value="{{ request('from_date', now()->format('Y-m-d')) }}">
                                                <span class="input-group-text bg-light border-0">إلى</span>
                                                <input type="date" name="to_date" class="form-control border-light"
                                                    value="{{ request('to_date', now()->addDays(14)->format('Y-m-d')) }}">
                                                <button type="submit" class="btn btn-primary btn-sm px-3 shadow-none"><i
                                                        class="ri-search-2-line"></i> تصفية</button>
                                                <a href="{{ request()->url() }}" class="btn btn-light btn-sm border-0"><i
                                                        class="ri-refresh-line"></i></a>
                                            </div>
                                        </div>
                                        <div class="col-md-auto">
                                            <span class="badge bg-soft-info text-info p-2 px-3 rounded-pill">
                                                <i class="ri-calendar-todo-line me-1"></i> {{ count($schedule) }} محاضرة
                                                في الفترة المحددة
                                            </span>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            @php
                                $groupedSchedule = $schedule->groupBy(function ($item) {
                                    return $item->starts_at->translatedFormat('Y-m-d');
                                });
                            @endphp

                            @forelse($groupedSchedule as $dateString => $dayLectures)
                                @php
                                    $dateObj = \Carbon\Carbon::parse($dateString);
                                    $isToday = $dateObj->isToday();
                                @endphp

                                <div class="timetable-day-group mb-5">
                                    <div
                                        class="timetable-day-header {{ $isToday ? 'bg-soft-success border-success' : '' }}">
                                        <div class="flex-grow-1">
                                            <i
                                                class="ri-calendar-check-line me-2 {{ $isToday ? 'text-success' : 'text-primary' }}"></i>
                                            {{ $dateObj->translatedFormat('l, d F Y') }}
                                            @if ($isToday)
                                                <span class="badge bg-success ms-2 fs-10">اليوم</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="timetable-items-container">
                                        @foreach ($dayLectures as $lecture)
                                            <div class="timetable-item">
                                                <div class="time-indicator">
                                                    <span class="time-val">{{ $lecture->starts_at->format('H:i') }}</span>
                                                    <span
                                                        class="time-period">{{ $lecture->starts_at->format('A') == 'AM' ? 'صباحاً' : 'مساءً' }}</span>
                                                </div>
                                                <div class="lecture-card-premium">
                                                    <div class="lecture-status-indicator">
                                                        @php $attStatus = $lecture->attendances->first()->status ?? 'pending'; @endphp
                                                        <div class="status-circle bg-{{ $attStatus == 'present' ? 'success' : ($attStatus == 'late' ? 'warning' : ($attStatus == 'absent' ? 'danger' : 'light')) }}"
                                                            title="{{ __('educational::messages.' . $attStatus) }}">
                                                        </div>
                                                    </div>
                                                    <div class="lecture-icon-wrap">
                                                        <span
                                                            class="fs-12 fw-bold text-primary">{{ $loop->iteration }}</span>
                                                    </div>
                                                    <div class="lecture-details">
                                                        <div class="d-flex align-items-center gap-2 mb-1">
                                                            <h6 class="fw-bold mb-0 text-dark fs-15">
                                                                {{ $lecture->sessionType->name ?? $lecture->title }}</h6>
                                                            <span
                                                                class="badge bg-soft-dark text-muted border-0 fs-10 px-2 rounded-pill">محاضرة
                                                                رقم {{ $loop->iteration }}</span>
                                                            @if ($attStatus != 'pending')
                                                                <span
                                                                    class="badge bg-soft-{{ $attStatus == 'present' ? 'success' : ($attStatus == 'late' ? 'warning' : 'danger') }} border-0 text-uppercase fs-10">
                                                                    {{ __('educational::messages.' . $attStatus) }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="hstack gap-3 text-muted fs-12">
                                                            <span><i class="ri-user-star-line me-1 text-primary"></i>
                                                                {{ $lecture->instructorProfile->user->full_name ?? 'يحدد لاحقاً' }}</span>
                                                            <span class="vr"></span>
                                                            <span><i class="ri-map-pin-2-line me-1 text-primary"></i>
                                                                {{ $lecture->room->name ?? '---' }}
                                                                ({{ $lecture->room->floor->building->name ?? '' }})
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="lecture-action d-flex align-items-center gap-2">
                                                        <button type="button"
                                                            class="btn btn-sm btn-icon btn-soft-danger rounded-circle"
                                                            onclick="openLectureComplaint({{ $lecture->id }}, '{{ $lecture->sessionType->name ?? $lecture->title }}')"
                                                            title="تقديم شكوى على هذه المحاضرة">
                                                            <i class="ri-alert-line"></i>
                                                        </button>
                                                        <span class="badge bg-soft-primary text-primary rounded-pill">
                                                            {{ $lecture->starts_at->isFuture() ? $lecture->starts_at->diffForHumans() : $lecture->starts_at->format('H:i A') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                                    <img src="{{ asset('assets/images/coming-soon.png') }}" alt=""
                                        height="120" class="mb-3 opacity-50">
                                    <h5 class="fw-bold text-dark">{{ __('educational::messages.no_schedule_found') }}
                                    </h5>
                                    <p class="text-muted">لا توجد محاضرات مجدولة حالياً لهذا الطالب.</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="col-xl-3 mt-4 mt-xl-0">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-soft-primary border-0 py-3">
                                    <h6 class="card-title mb-0 fw-black text-primary"><i
                                            class="ri-bar-chart-2-line me-1"></i> إحصائيات الأسبوع الحالي</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        <div
                                            class="list-group-item d-flex justify-content-between align-items-center py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-2">
                                                    <div class="avatar-title bg-soft-info text-info rounded-circle"><i
                                                            class="ri-time-line"></i></div>
                                                </div>
                                                <span class="text-muted fs-13">الساعات المجدولة</span>
                                            </div>
                                            <span class="fw-bold">{{ $weeklyHours }} ساعة</span>
                                        </div>
                                        <div
                                            class="list-group-item d-flex justify-content-between align-items-center py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-2">
                                                    <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                        <i class="ri-book-open-line"></i>
                                                    </div>
                                                </div>
                                                <span class="text-muted fs-13">عدد المحاضرات</span>
                                            </div>
                                            <span class="fw-bold">{{ $weeklyLecturesCount }} محاضرة</span>
                                        </div>
                                        <div
                                            class="list-group-item d-flex justify-content-between align-items-center py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-2">
                                                    <div class="avatar-title bg-soft-success text-success rounded-circle">
                                                        <i class="ri-checkbox-circle-line"></i>
                                                    </div>
                                                </div>
                                                <span class="text-muted fs-13">نسبة الحضور</span>
                                            </div>
                                            <span
                                                class="fw-bold">{{ $weeklyLecturesCount > 0 ? round(($weeklyAttendance / $weeklyLecturesCount) * 100) : 0 }}%</span>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <div class="alert alert-soft-info border-0 mb-0 fs-12">
                                            <i class="ri-information-line me-1"></i> يتم تحديث الإحصائيات أسبوعياً بناءً
                                            على الجدول الزمني وتفاعلات الحضور.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="attendance-tab" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-soft-primary border-0 py-3 d-flex align-items-center">
                            <h5 class="card-title mb-0 fw-bold flex-grow-1 text-primary"><i
                                    class="ri-user-follow-line me-2"></i>
                                {{ __('educational::messages.attendance_log') }}</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr class="text-uppercase fs-12">
                                            <th class="ps-4">{{ __('educational::messages.session_type') }}</th>
                                            <th>{{ __('educational::messages.datetime') }}</th>
                                            <th>{{ __('educational::messages.time') }}</th>
                                            <th class="text-center">{{ __('educational::messages.status') }}</th>
                                            <th class="pe-4">{{ __('educational::messages.note') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($attendances as $attendance)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-xs me-2">
                                                            <div
                                                                class="avatar-title bg-soft-primary text-primary rounded-circle fs-14">
                                                                <i class="ri-book-open-line"></i>
                                                            </div>
                                                        </div>
                                                        <span
                                                            class="fw-bold">{{ $attendance->lesson_name ?? 'محاضرة عامة' }}</span>
                                                    </div>
                                                </td>
                                                <td>{{ $attendance->created_at->translatedFormat('d M Y') }}</td>
                                                <td>{{ $attendance->created_at->format('H:i') }}</td>
                                                <td class="text-center">
                                                    @if ($attendance->status == 'present')
                                                        <span
                                                            class="badge bg-soft-success text-success border-success px-3">{{ __('educational::messages.present') }}</span>
                                                    @elseif($attendance->status == 'late')
                                                        <span
                                                            class="badge bg-soft-warning text-warning border-warning px-3">{{ __('educational::messages.late') }}</span>
                                                    @elseif($attendance->status == 'excused')
                                                        <span
                                                            class="badge bg-soft-info text-info border-info px-3">{{ __('educational::messages.excused') }}</span>
                                                    @else
                                                        <span
                                                            class="badge bg-soft-danger text-danger border-danger px-3">{{ __('educational::messages.absent') }}</span>
                                                    @endif
                                                </td>
                                                <td class="pe-4 text-muted fs-13">{{ $attendance->notes ?? '---' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted">
                                                    <i class="ri-calendar-event-line fs-48 d-block mb-3 opacity-25"></i>
                                                    {{ __('educational::messages.no_attendance_records') ?? 'لا يوجد سجلات حضور مسجلة حالياً.' }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="complaints-tab" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-soft-info border-0 py-3 d-flex align-items-center">
                            <h5 class="card-title mb-0 fw-bold flex-grow-1 text-info"><i
                                    class="ri-ticket-2-line me-2"></i>
                                {{ __('educational::messages.complaints_requests') }}</h5>
                            <button class="btn btn-sm btn-info rounded-pill px-3" data-bs-toggle="modal"
                                data-bs-target="#addTicketModal"><i class="ri-add-line me-1"></i> فتح طلب جديد</button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr class="text-uppercase fs-12">
                                            <th class="ps-4">{{ __('educational::messages.id') }}</th>
                                            <th>{{ __('educational::messages.title') ?? 'الموضوع' }}</th>
                                            <th>{{ __('educational::messages.type') }}</th>
                                            <th>المحاضرة</th>
                                            <th>{{ __('educational::messages.datetime') }}</th>
                                            <th class="text-center">{{ __('educational::messages.status') }}</th>
                                            <th class="pe-4"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tickets as $ticket)
                                            <tr>
                                                <td class="ps-4 fw-bold text-primary">#{{ $ticket->id }}</td>
                                                <td><span class="fw-medium">{{ $ticket->subject }}</span></td>
                                                <td><span
                                                        class="badge badge-soft-secondary px-2">{{ $ticket->category->name ?? 'عام' }}</span>
                                                </td>
                                                <td>
                                                    @if ($ticket->lecture)
                                                        <span
                                                            class="text-muted fs-12">{{ $ticket->lecture->sessionType->name ?? 'محاضرة' }}</span>
                                                    @else
                                                        <span class="text-muted fs-12">-</span>
                                                    @endif
                                                </td>
                                                <td>{{ $ticket->created_at->diffForHumans() }}</td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge rounded-pill px-3 bg-soft-info text-info border-info">
                                                        {{ $ticket->status->name ?? 'مفتوح' }}
                                                    </span>
                                                </td>
                                                <td class="pe-4 text-end">
                                                    <a href="{{ route('agent.tickets.show', $ticket->uuid) }}"
                                                        class="btn btn-sm btn-icon btn-soft-primary rounded-circle"><i
                                                            class="ri-eye-line"></i></a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted">
                                                    <i class="ri-ticket-line fs-48 d-block mb-3 opacity-25"></i>
                                                    {{ __('educational::messages.no_complaints_found') ?? 'لم يقم المتدرب بفتح أي شكاوى أو طلبات حتى الآن.' }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="documents-tab" role="tabpanel">
                    <div class="row g-4">
                        @forelse($student->documents as $doc)
                            <div class="col-xl-3 col-md-4 col-sm-6">
                                <div
                                    class="card premium-stat-card border-0 shadow-sm h-100 lecture-card-premium p-0 overflow-hidden">
                                    <div class="p-4 text-center">
                                        <div class="avatar-md mx-auto mb-3">
                                            <div class="avatar-title bg-soft-primary text-primary rounded-circle fs-24">
                                                @php
                                                    $icon = 'ri-file-line';
                                                    if (str_contains($doc->file_type, 'image')) {
                                                        $icon = 'ri-image-line';
                                                    } elseif (str_contains($doc->file_type, 'pdf')) {
                                                        $icon = 'ri-file-pdf-line';
                                                    }
                                                @endphp
                                                <i class="{{ $icon }}"></i>
                                            </div>
                                        </div>
                                        <h6 class="text-truncate fw-bold mb-1">{{ $doc->name }}</h6>
                                        <p class="text-muted fs-11 mb-3 text-uppercase">{{ $doc->file_type }} •
                                            {{ number_format($doc->file_size / 1024, 1) }} KB</p>

                                        <div class="hstack gap-2 justify-content-center">
                                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank"
                                                class="btn btn-sm btn-soft-primary flex-grow-1 rounded-pill"><i
                                                    class="ri-eye-line me-1"></i>
                                                {{ __('educational::messages.show') }}</a>
                                            <a href="{{ Storage::url($doc->file_path) }}" download
                                                class="btn btn-sm btn-icon btn-soft-success rounded-circle"><i
                                                    class="ri-download-2-line"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div
                                class="col-12 text-center py-5 bg-white rounded-4 shadow-sm border border-dashed border-2">
                                <div class="avatar-lg mx-auto mb-3">
                                    <div class="avatar-title bg-light text-muted rounded-circle fs-32">
                                        <i class="ri-folder-open-line"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold">
                                    {{ __('educational::messages.no_documents_found') ?? 'لا توجد مستندات مرفوعة لهذا المتدرب.' }}
                                </h5>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Premium Design System */
            :root {
                --premium-primary: #405189;
                --premium-secondary: #0ab39c;
                --premium-glass: rgba(255, 255, 255, 0.1);
                --premium-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            }

            .profile-header-premium {
                background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
                padding-top: 60px;
                padding-bottom: 0px;
                position: relative;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                border-radius: 0 0 !important;
                overflow: hidden;
                border-bottom: 3px solid #3b82f6;
            }

            .profile-header-premium::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(to right, rgba(59, 130, 246, 0.1), transparent);
                pointer-events: none;
            }

            /* Institutional Control Buttons */
            .btn-inst-primary {
                background: #3b82f6;
                /* Corporate Blue */
                color: white !important;
                border: 1px solid rgba(255, 255, 255, 0.1);
                transition: all 0.3s ease;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .btn-inst-primary:hover {
                background: #2563eb;
                box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
                transform: translateY(-2px);
            }

            .btn-inst-danger {
                background: #ef4444;
                color: white !important;
                border: 1px solid rgba(255, 255, 255, 0.1);
                transition: all 0.3s ease;
                font-weight: 700;
            }

            .btn-inst-danger:hover {
                background: #dc2626;
                box-shadow: 0 8px 20px rgba(220, 38, 38, 0.4);
                transform: translateY(-2px);
            }

            .btn-inst-secondary {
                background: rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(10px);
                color: #fff !important;
                border: 1px solid rgba(255, 255, 255, 0.1);
                transition: all 0.3s ease;
            }

            /* Initials Avatar Styling */
            .avatar-title-text {
                font-size: 28px;
                background: #f8fafc;
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #1e293b !important;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            }

            /* Modern Timetable Styles */
            .timetable-day-header {
                background: #f8f9fa;
                padding: 12px 20px;
                border-radius: 12px;
                margin-bottom: 20px;
                border-right: 4px solid var(--premium-primary);
                font-weight: 700;
                color: #333;
                display: flex;
                align-items: center;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            }

            .timetable-item {
                position: relative;
                padding-right: 85px;
                /* Space for time indicator */
                margin-bottom: 15px;
            }

            .time-indicator {
                position: absolute;
                right: 0;
                top: 0;
                bottom: 0;
                width: 75px;
                text-align: center;
                padding-top: 15px;
                border-left: 2px dashed #e9ebec;
            }

            .time-val {
                font-weight: 800;
                color: var(--premium-primary);
                font-size: 14px;
                display: block;
                line-height: 1.2;
            }

            .time-period {
                font-size: 10px;
                color: #888;
                text-transform: uppercase;
            }

            .lecture-card-premium {
                background: #fff;
                border-radius: 16px;
                padding: 16px;
                border: 1px solid #eff2f7;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                display: flex;
                align-items: center;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            }

            .lecture-card-premium:hover {
                transform: translateX(-5px);
                box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
                border-color: var(--premium-primary);
            }

            .lecture-icon-wrap {
                width: 48px;
                height: 48px;
                background: rgba(64, 81, 137, 0.05);
                color: var(--premium-primary);
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 22px;
                flex-shrink: 0;
            }

            .lecture-details {
                flex-grow: 1;
                margin-right: 15px;
            }

            .lecture-status-indicator {
                position: absolute;
                right: -5px;
                top: 50%;
                transform: translateY(-50%);
                z-index: 5;
            }

            .status-circle {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                border: 2px solid #fff;
                box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            }

            /* Tabs Styling */
            .custom-white-nav .nav-link {
                color: rgba(255, 255, 255, 0.7) !important;
                border-bottom: 3px solid transparent !important;
                font-weight: 600 !important;
                padding: 18px 25px !important;
                transition: all 0.3s ease;
            }

            .custom-white-nav .nav-link:hover {
                color: #fff !important;
                background: rgba(255, 255, 255, 0.05);
            }

            .custom-white-nav .nav-link.active {
                color: #fff !important;
                background: rgba(255, 255, 255, 0.15) !important;
                border-bottom: 3px solid #fff !important;
                backdrop-filter: blur(10px);
            }

            /* Stats Cards */
            .premium-stat-card {
                border: none;
                border-radius: 20px;
                overflow: hidden;
                transition: all 0.3s;
            }

            .premium-stat-card:hover {
                transform: translateY(-5px);
            }

            .glass-bg {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }

            [dir="rtl"] .ps-0 {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            [dir="rtl"] .ms-3 {
                margin-left: 0 !important;
                margin-right: 1rem !important;
            }

            [dir="rtl"] .me-2 {
                margin-right: 0 !important;
                margin-left: 0.5rem !important;
            }

            [dir="rtl"] .ps-3 {
                padding-left: 0 !important;
                padding-right: 1rem !important;
            }

            /* Activity Log Modern Timeline */
            .acitivity-timeline-modern {
                position: relative;
            }

            .acitivity-line-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 32px;
            }

            .acitivity-avatar-modern {
                width: 32px;
                height: 32px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 2;
                font-size: 16px;
            }

            .acitivity-line {
                width: 2px;
                flex-grow: 1;
                background: #e9ebec;
                margin: 5px 0;
            }

            .acitivity-item-modern:last-child .acitivity-line {
                display: none;
            }

            .acitivity-content-box {
                transition: all 0.3s ease;
                position: relative;
            }

            .acitivity-content-box::before {
                content: "";
                position: absolute;
                right: -8px;
                top: 10px;
                border-width: 8px 0 8px 8px;
                border-style: solid;
                border-color: transparent transparent transparent rgba(0, 0, 0, 0.03);
            }

            .hover-shadow:hover {
                transform: translateY(-3px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05) !important;
                background: #fff !important;
                border-color: var(--premium-primary) !important;
            }

            .fw-black {
                font-weight: 900;
            }

            .ff-secondary {
                font-family: 'Inter', sans-serif;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function confirmDeleteStudent(id) {
                Swal.fire({
                    title: "{{ __('educational::messages.delete_student_title') ?? 'هل أنت متأكد؟' }}",
                    text: "{{ __('educational::messages.delete_student_text') ?? 'لن تتمكن من التراجع عن هذا الإجراء!' }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f06548',
                    cancelButtonColor: '#3577f1',
                    confirmButtonText: "{{ __('educational::messages.yes_delete') ?? 'نعم، قم بالحذف' }}",
                    cancelButtonText: "{{ __('educational::messages.cancel') ?? 'إلغاء' }}",
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'px-4 py-2 rounded-pill',
                        cancelButton: 'px-4 py-2 rounded-pill ms-2'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-student-form-' + id).submit();
                    }
                });
            }
        </script>
    @endpush

    <!-- Add Ticket Modal -->
    <div class="modal fade" id="addTicketModal" tabindex="-1" aria-labelledby="addTicketModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary p-3">
                    <h5 class="modal-title text-white fw-bold" id="addTicketModalLabel"><i
                            class="ri-ticket-2-line me-2"></i> فتح شكوى أو طلب جديد</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('educational.students.ticket.store', $student->id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div id="lectureInfo" class="alert alert-soft-danger border-0 mb-4 d-none">
                            <i class="ri-information-line me-2"></i> تقديم شكوى بخصوص: <strong
                                id="selectedLectureName"></strong>
                            <input type="hidden" name="lecture_id" id="selectedLectureId">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">المرحلة / النوع <span
                                        class="text-danger">*</span></label>
                                <select name="stage_id" id="ticket_stage" class="form-select border-light bg-light"
                                    required onchange="updateCategories()">
                                    <option value="">-- اختر النوع --</option>
                                    @foreach ($ticketStages as $stage)
                                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">التصنيف <span class="text-danger">*</span></label>
                                <select name="category_id" id="ticket_category" class="form-select border-light bg-light"
                                    required onchange="updateComplaints()">
                                    <option value="">-- اختر التصنيف --</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">الشكوى المحددة</label>
                                <select name="complaint_id" id="ticket_complaint"
                                    class="form-select border-light bg-light">
                                    <option value="">-- اختر الشكوى (اختياري) --</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">الأولوية <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3">
                                    @foreach ($ticketPriorities as $priority)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="priority_id"
                                                id="priority_{{ $priority->id }}" value="{{ $priority->id }}"
                                                {{ $loop->first ? 'checked' : '' }}>
                                            <label class="form-check-label" for="priority_{{ $priority->id }}">
                                                <span
                                                    class="badge bg-{{ $priority->color ?? 'secondary' }}">{{ $priority->name }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">الموضوع (اختياري)</label>
                                <input type="text" name="subject" class="form-control border-light bg-light"
                                    placeholder="عنوان ملخص للشكوى">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">التفاصيل <span class="text-danger">*</span></label>
                                <textarea name="details" class="form-control border-light bg-light" rows="4"
                                    placeholder="اشرح المشكلة بالتفصيل..." required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-ghost-danger rounded-pill px-4"
                            data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow"><i
                                class="ri-check-line me-1"></i> إرسال الشكوى</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const ticketStages = @json($ticketStages);

            function updateCategories() {
                const stageId = document.getElementById('ticket_stage').value;
                const categorySelect = document.getElementById('ticket_category');
                categorySelect.innerHTML = '<option value="">-- اختر التصنيف --</option>';
                document.getElementById('ticket_complaint').innerHTML = '<option value="">-- اختر الشكوى (اختياري) --</option>';

                if (!stageId) return;

                const stage = ticketStages.find(s => s.id == stageId);
                if (stage && stage.categories) {
                    stage.categories.forEach(cat => {
                        const opt = document.createElement('option');
                        opt.value = cat.id;
                        opt.text = cat.name;
                        categorySelect.add(opt);
                    });
                }
            }

            function updateComplaints() {
                const stageId = document.getElementById('ticket_stage').value;
                const categoryId = document.getElementById('ticket_category').value;
                const complaintSelect = document.getElementById('ticket_complaint');
                complaintSelect.innerHTML = '<option value="">-- اختر الشكوى (اختياري) --</option>';

                if (!stageId || !categoryId) return;

                const stage = ticketStages.find(s => s.id == stageId);
                const category = stage.categories.find(c => c.id == categoryId);
                if (category && category.complaints) {
                    category.complaints.forEach(comp => {
                        const opt = document.createElement('option');
                        opt.value = comp.id;
                        opt.text = comp.name;
                        complaintSelect.add(opt);
                    });
                }
            }

            function openLectureComplaint(lectureId, lectureName) {
                document.getElementById('selectedLectureId').value = lectureId;
                document.getElementById('selectedLectureName').innerText = lectureName;
                document.getElementById('lectureInfo').classList.remove('d-none');

                var modal = new bootstrap.Modal(document.getElementById('addTicketModal'));
                modal.show();
            }

            // Reset lecture info when modal is closed
            document.getElementById('addTicketModal').addEventListener('hidden.bs.modal', function() {
                document.getElementById('selectedLectureId').value = '';
                document.getElementById('selectedLectureName').innerText = '';
                document.getElementById('lectureInfo').classList.add('d-none');
                document.querySelector('#addTicketModal form').reset();
            });
        </script>
    @endpush
@endsection
