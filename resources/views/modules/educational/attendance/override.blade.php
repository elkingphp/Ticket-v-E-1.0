@extends('core::layouts.master')
@section('title', __('educational::messages.attendance_override'))

@section('content')
    {{-- Breadcrumbs / Page Title --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('educational::messages.attendance_override_management') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a
                                href="javascript: void(0);">{{ __('educational::messages.educational_system') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('educational::messages.attendance_overrides_breadcrumb') }}
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @include('modules.educational.shared.alerts')

    {{-- Filter Section --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-0 mt-2">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <h5 class="card-title mb-0">{{ __('educational::messages.search_and_filter') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="card-body border border-dashed border-end-0 border-start-0">
                    <form action="{{ route('educational.attendance.override.list') }}" method="GET">
                        <div class="row g-3">
                            <div class="col-xxl-2 col-sm-4">
                                <div>
                                    <label class="form-label text-muted">{{ __('educational::messages.date') }}</label>
                                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                                </div>
                            </div>
                            <div class="col-xxl-2 col-sm-4">
                                <div>
                                    <label class="form-label text-muted">{{ __('educational::messages.room') }}</label>
                                    <select class="form-select form-select-sm" name="room_id">
                                        <option value="">{{ __('educational::messages.all_rooms') }}</option>
                                        @foreach ($rooms as $room)
                                            <option value="{{ $room->id }}"
                                                {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                                {{ $room->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-xxl-3 col-sm-4">
                                <div>
                                    <label class="form-label text-muted">{{ __('educational::messages.group') }}</label>
                                    <select class="form-select form-select-sm" name="group_id">
                                        <option value="">{{ __('educational::messages.all_groups') }}</option>
                                        @foreach ($groups as $group)
                                            <option value="{{ $group->id }}"
                                                {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                                {{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-xxl-4 col-sm-8">
                                <div>
                                    <label
                                        class="form-label text-muted">{{ __('educational::messages.search_trainee') }}</label>
                                    <div class="search-box">
                                        <input type="text" name="search" class="form-control form-control-sm"
                                            placeholder="{{ __('educational::messages.military_number_placeholder') }}"
                                            value="{{ request('search') }}">
                                        <button class="btn btn-primary" type="submit"><i
                                                class="ri-search-line"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xxl-1 col-sm-4 d-flex align-items-end">
                                <a href="{{ route('educational.attendance.override.list') }}"
                                    class="btn btn-soft-secondary w-100">
                                    <i class="ri-refresh-line align-bottom me-1"></i>
                                    {{ __('educational::messages.reset') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table Section --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card" id="attendanceList">
                <div class="card-header border-bottom-dashed">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1"><i class="ri-lock-2-line text-danger me-1"></i>
                            {{ __('educational::messages.locked_sheets') }}</h5>
                        <div class="flex-shrink-0">
                            <span
                                class="badge bg-info-subtle text-info">{{ __('educational::messages.total_results', ['total' => $attendances->total()]) }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive table-card mb-1">
                        <table class="table table-nowrap align-middle">
                            <thead class="text-muted table-light">
                                <tr class="text-uppercase">
                                    <th>{{ __('educational::messages.trainee') }}</th>
                                    <th>{{ __('educational::messages.lecture_location') }}</th>
                                    <th class="text-center">{{ __('educational::messages.current_status') }}</th>
                                    <th class="text-center">{{ __('educational::messages.locked_at') }}</th>
                                    <th class="text-center">{{ __('educational::messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $attendance)
                                    @php
                                        $trainee = $attendance->traineeProfile;
                                        $user = $trainee->user ?? null;
                                        $lecture = $attendance->lecture;
                                        $lockTime =
                                            $attendance->locked_at ??
                                            ($lecture ? $lecture->ends_at->addHours($lockHours ?? 24) : null);

                                        $statusConfig = [
                                            'present' => ['color' => 'success', 'label' => 'حاضر'],
                                            'absent' => ['color' => 'danger', 'label' => 'غائب'],
                                            'late' => ['color' => 'warning', 'label' => 'متأخر'],
                                            'excused' => ['color' => 'info', 'label' => 'معتذر'],
                                        ];
                                        $cfg = $statusConfig[$attendance->status] ?? [
                                            'color' => 'secondary',
                                            'label' => $attendance->status,
                                        ];
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 avatar-xs me-2">
                                                    <div
                                                        class="avatar-title bg-soft-primary text-primary rounded-circle fs-13">
                                                        {{ mb_substr($user->full_name ?? 'S', 0, 1) }}
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="fs-14 mb-0"><a href="javascript:void(0);"
                                                            class="text-dark">{{ $user->full_name ?? '—' }}</a></h6>
                                                    <p class="text-muted mb-0 fs-12">م ن:
                                                        {{ $trainee->military_number ?? '—' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 me-2 text-primary fs-18">
                                                    <i class="ri-door-open-line"></i>
                                                </div>
                                                <div>
                                                    <h6 class="fs-13 mb-0">{{ $lecture->room->name ?? '—' }}</h6>
                                                    <p class="text-muted mb-0 fs-12">
                                                        {{ $lecture->starts_at->format('Y-m-d') }} |
                                                        {{ $lecture->starts_at->format('H:i') }} -
                                                        {{ $lecture->ends_at->format('H:i') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-{{ $cfg['color'] }}-subtle text-{{ $cfg['color'] }} text-uppercase">{{ $cfg['label'] }}</span>
                                            @if ($attendance->status == 'late' && $attendance->check_in_time)
                                                <div class="fs-11 text-muted mt-1">
                                                    {{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($lockTime)
                                                <div class="text-muted fs-12">{{ $lockTime->format('Y-m-d') }}</div>
                                                <div class="text-muted fs-11">{{ $lockTime->format('h:i a') }}</div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @can('education.attendance.override')
                                                <button class="btn btn-sm btn-soft-warning" data-bs-toggle="modal"
                                                    data-bs-target="#overrideModal{{ $attendance->id }}">
                                                    <i class="ri-edit-2-line align-bottom me-1"></i> تعديل استثنائي
                                                </button>
                                            @else
                                                <i class="ri-lock-line text-muted"></i>
                                            @endcan
                                        </td>
                                    </tr>

                                    {{-- Velzon Modal --}}
                                    <div class="modal fade" id="overrideModal{{ $attendance->id }}" tabindex="-1"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header bg-light p-3">
                                                    <h5 class="modal-title">طلب تعديل حضور (Override)</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form
                                                    action="{{ route('educational.attendance.override', $attendance->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3 d-flex align-items-center bg-light p-2 rounded">
                                                            <div class="avatar-sm flex-shrink-0 me-3">
                                                                <div
                                                                    class="avatar-title bg-info-subtle text-info rounded-circle fs-16">
                                                                    <i class="ri-user-line"></i>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0">{{ $user->full_name ?? '—' }}</h6>
                                                                <small
                                                                    class="text-muted">{{ $lecture->room->name ?? '—' }} |
                                                                    {{ $lecture->starts_at->format('Y-m-d') }}</small>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">الحالة الجديدة <span
                                                                    class="text-danger">*</span></label>
                                                            <select name="status" class="form-select status-select"
                                                                data-id="{{ $attendance->id }}" required>
                                                                <option value="present"
                                                                    {{ $attendance->status == 'present' ? 'selected' : '' }}>
                                                                    حاضر</option>
                                                                <option value="absent"
                                                                    {{ $attendance->status == 'absent' ? 'selected' : '' }}>
                                                                    غائب</option>
                                                                <option value="late"
                                                                    {{ $attendance->status == 'late' ? 'selected' : '' }}>
                                                                    متأخر</option>
                                                                <option value="excused"
                                                                    {{ $attendance->status == 'excused' ? 'selected' : '' }}>
                                                                    معتذر (عذر رسمي)</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3 d-none time-container-{{ $attendance->id }}">
                                                            <label class="form-label">وقت الحضور الفعلي <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="time" name="check_in_time"
                                                                class="form-control"
                                                                value="{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '' }}">
                                                        </div>

                                                        <div class="mb-0">
                                                            <label class="form-label">المبرر / السبب <span
                                                                    class="text-danger">*</span></label>
                                                            <textarea name="notes" class="form-control" rows="3" placeholder="أدخل تفاصيل مبرر التعديل..." required></textarea>
                                                            <div class="form-text">سيتم إرسال هذا الطلب للاعتماد قبل
                                                                تطبيقه.</div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light"
                                                            data-bs-dismiss="modal">إغلاق</button>
                                                        <button type="submit" class="btn btn-primary">إرسال
                                                            الطلب</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="avatar-lg mx-auto mb-4">
                                                <div class="avatar-title bg-light text-primary rounded-circle fs-24">
                                                    <i class="ri-search-line"></i>
                                                </div>
                                            </div>
                                            <h5 class="mt-2">لا توجد سجلات حالياً</h5>
                                            <p class="text-muted">لم نتمكن من العثور على أي نتائج مطابقة لمعايير البحث.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        {{ $attendances->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle late time input visibility
            const statusSelects = document.querySelectorAll('.status-select');

            function toggleTimeInput(select) {
                const id = select.getAttribute('data-id');
                const container = document.querySelector('.time-container-' + id);
                if (select.value === 'late') {
                    container.classList.remove('d-none');
                } else {
                    container.classList.add('d-none');
                }
            }

            statusSelects.forEach(select => {
                toggleTimeInput(select); // Init
                select.addEventListener('change', function() {
                    toggleTimeInput(this);
                });
            });
        });
    </script>
@endsection
