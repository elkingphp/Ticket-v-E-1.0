@extends('core::layouts.master')
@section('title', __('educational::messages.instructors'))

@section('content')

    @include('modules.educational.shared.alerts')

    @php
        $totalCount = $stats['total'];
        $activeCount = $stats['active'];
        $suspendedCount = $stats['suspended'];
        $pendingCount = $stats['pending'];
    @endphp

    <!-- Hero / Stats Section -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate border-0 shadow-sm overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-bold text-muted mb-0 fs-12">
                                {{ __('educational::messages.total_lecturers') }}</p>
                            <h4 class="mt-2 text-primary fw-bold mb-0"><span class="counter-value"
                                    data-target="{{ $totalCount }}">{{ $totalCount }}</span></h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-primary text-primary rounded-3 fs-3">
                                <i class="ri-team-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="progress progress-sm rounded-0">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 100%" aria-valuenow="100"
                        aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate border-0 shadow-sm overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-bold text-muted mb-0 fs-12">
                                {{ __('educational::messages.active_lecturers') }}</p>
                            <h4 class="mt-2 text-success fw-bold mb-0"><span class="counter-value"
                                    data-target="{{ $activeCount }}">{{ $activeCount }}</span></h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-success text-success rounded-3 fs-3">
                                <i class="ri-user-heart-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="progress progress-sm rounded-0">
                    <div class="progress-bar bg-success" role="progressbar"
                        style="width: {{ $totalCount > 0 ? ($activeCount / $totalCount) * 100 : 0 }}%"
                        aria-valuenow="{{ $activeCount }}" aria-valuemin="0" aria-valuemax="{{ $totalCount }}"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate border-0 shadow-sm overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-bold text-muted mb-0 fs-12">
                                {{ __('educational::messages.pending_approvals') }}</p>
                            <h4 class="mt-2 text-warning fw-bold mb-0"><span class="counter-value"
                                    data-target="{{ $pendingCount }}">{{ $pendingCount }}</span></h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-warning text-warning rounded-3 fs-3">
                                <i class="ri-shield-user-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="progress progress-sm rounded-0">
                    <div class="progress-bar bg-warning" role="progressbar"
                        style="width: {{ $totalCount > 0 ? ($pendingCount / $totalCount) * 100 : 0 }}%"
                        aria-valuenow="{{ $pendingCount }}" aria-valuemin="0" aria-valuemax="{{ $totalCount }}"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate border-0 shadow-sm overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-bold text-muted mb-0 fs-12">
                                {{ __('educational::messages.suspended_lecturers') }}</p>
                            <h4 class="mt-2 text-danger fw-bold mb-0"><span class="counter-value"
                                    data-target="{{ $suspendedCount }}">{{ $suspendedCount }}</span></h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-danger text-danger rounded-3 fs-3">
                                <i class="ri-user-unfollow-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="progress progress-sm rounded-0">
                    <div class="progress-bar bg-danger" role="progressbar"
                        style="width: {{ $totalCount > 0 ? ($suspendedCount / $totalCount) * 100 : 0 }}%"
                        aria-valuenow="{{ $suspendedCount }}" aria-valuemin="0" aria-valuemax="{{ $totalCount }}"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row align-items-center g-3 mb-4">
        <div class="col-md">
            <h4 class="mb-1 fw-bold">{{ __('educational::messages.lecturers_management') }}</h4>
            <p class="text-muted mb-0 small">{{ __('educational::messages.manage_instructors_desc') }}</p>
        </div>
        <div class="col-md-auto ms-auto">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <form action="{{ route('educational.instructors.index') }}" method="GET" class="search-box">
                    <input type="text" name="search" class="form-control bg-white border-0 shadow-sm"
                        id="lecturerSearch" placeholder="{{ __('educational::messages.search') }}"
                        value="{{ request('search') }}">
                    <i class="ri-search-line search-icon text-muted ms-2"></i>
                </form>

                <div class="dropdown">
                    <button class="btn btn-soft-info btn-icon shadow-sm position-relative" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-filter-3-line"></i>
                        @if (request()->anyFilled(['track_id', 'company_id', 'session_type_id']))
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                style="padding: 4px;"> </span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-4" style="min-width: 350px;">
                        <h6 class="dropdown-header px-0 mb-3 text-uppercase fw-bold border-bottom pb-2">تفاصيل التصفية</h6>
                        <form action="{{ route('educational.instructors.index') }}" method="GET">
                            @if (request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fs-12 text-muted">التخصص / المسار</label>
                                    <select name="track_id" class="form-select form-select-sm">
                                        <option value="">الكل</option>
                                        @foreach ($tracks as $track)
                                            <option value="{{ $track->id }}"
                                                {{ request('track_id') == $track->id ? 'selected' : '' }}>
                                                {{ $track->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fs-12 text-muted">شركة التدريب</label>
                                    <select name="company_id" class="form-select form-select-sm">
                                        <option value="">الكل</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}"
                                                {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fs-12 text-muted">أنواع المحاضرات</label>
                                    <select name="session_type_id" class="form-select form-select-sm">
                                        <option value="">الكل</option>
                                        @foreach ($sessionTypes as $st)
                                            <option value="{{ $st->id }}"
                                                {{ request('session_type_id') == $st->id ? 'selected' : '' }}>
                                                {{ $st->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 mt-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">تطبيق</button>
                                    @if (request()->anyFilled(['track_id', 'company_id', 'session_type_id']))
                                        <a href="{{ route('educational.instructors.index') }}"
                                            class="btn btn-soft-danger btn-sm px-3">إلغاء</a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="dropdown">
                    <button class="btn btn-soft-info btn-icon shadow-sm" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="ri-more-fill"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                        <li>
                            <a class="dropdown-item"
                                href="{{ route('educational.instructors.export', request()->all()) }}">
                                <i class="ri-file-download-line me-2 text-muted"></i>
                                {{ __('educational::messages.export_instructors') ?? 'تصدير المحاضرين' }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal"
                                data-bs-target="#importInstructorsModal">
                                <i class="ri-file-upload-line me-2 text-muted"></i>
                                {{ __('educational::messages.import_instructors') ?? 'استيراد محاضرين' }}
                            </a>
                        </li>
                    </ul>
                </div>

                <a href="{{ route('educational.instructors.create') }}" class="btn btn-primary shadow-sm px-4">
                    <i class="ri-add-line align-middle me-1 fw-bold"></i> {{ __('educational::messages.add_instructor') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importInstructorsModal" tabindex="-1" aria-labelledby="importInstructorsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-soft-info p-3">
                    <h5 class="modal-title fw-bold text-info" id="importInstructorsModalLabel">
                        <i class="ri-file-upload-line me-2"></i>
                        {{ __('educational::messages.import_instructors') ?? 'استيراد محاضرين' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('educational.instructors.import') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 bg-soft-info text-info mb-4">
                            <i class="ri-information-line me-2 fs-16 align-middle"></i>
                            {{ __('educational::messages.import_note') ?? 'يرجى تحميل نموذج الملف وتعبئته بالبيانات المطلوبة قبل الرفع لضمان صحة الاستيراد.' }}
                        </div>

                        <div class="mb-4 text-center">
                            <a href="{{ route('educational.instructors.template') }}"
                                class="btn btn-soft-secondary btn-sm rounded-pill px-3">
                                <i class="ri-download-2-line me-1"></i>
                                {{ __('educational::messages.download_template') ?? 'تحميل النموذج (Template)' }}
                            </a>
                        </div>

                        <div class="mb-3">
                            <label for="import_file"
                                class="form-label fw-bold">{{ __('educational::messages.select_file') ?? 'اختر ملف (CSV)' }}</label>
                            <input type="file" class="form-control shadow-none border-dashed p-3" id="import_file"
                                name="import_file" accept=".csv" required>
                            <div class="form-text mt-2 text-muted small">
                                <i class="ri-error-warning-line me-1"></i> يدعم الملفات بصيغة CSV فقط حالياً.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-ghost-danger"
                            data-bs-dismiss="modal">{{ __('educational::messages.cancel') }}</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="ri-check-line me-1"></i>
                            {{ __('educational::messages.upload_file') ?? 'رفع الملف' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Grid Layout: Gallery Style -->
    <div class="row" id="lecturer-grid">
        @forelse($instructors as $instructor)
            @php
                $user = $instructor->user;
                $initials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));

                $statusBadge = [
                    'active' => 'badge-soft-success',
                    'inactive' => 'badge-soft-warning',
                    'suspended' => 'badge-soft-danger',
                ];

                $typeClass = [
                    'full_time' => 'text-primary bg-soft-primary',
                    'part_time' => 'text-info bg-soft-info',
                    'contractor' => 'text-secondary bg-soft-secondary',
                ];
            @endphp
            <div class="col-xl-3 col-lg-4 col-sm-6 lecturer-item">
                <div class="card text-center card-animate border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="dropdown float-end">
                            <a href="javascript:void(0);" class="text-muted" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="ri-more-2-fill fs-18"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                <a class="dropdown-item py-2"
                                    href="{{ route('educational.instructors.show', $instructor->id) }}"><i
                                        class="ri-eye-line me-2 text-muted"></i>
                                    {{ __('educational::messages.show') }}</a>
                                <a class="dropdown-item py-2"
                                    href="{{ route('educational.instructors.edit', $instructor->id) }}"><i
                                        class="ri-pencil-line me-2 text-muted"></i>
                                    {{ __('educational::messages.edit') }}</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item py-2 text-danger" href="javascript:void(0);"
                                    onclick="confirmDeleteLecturer({{ $instructor->id }})"><i
                                        class="ri-delete-bin-line me-2"></i> {{ __('educational::messages.delete') }}</a>
                            </div>
                        </div>

                        <div class="mx-auto avatar-md mb-3">
                            <div
                                class="avatar-title bg-soft-primary text-primary rounded-circle border border-2 border-white shadow fw-bold fs-20">
                                {{ $initials }}
                            </div>
                        </div>

                        <h5 class="mb-1 text-truncate">
                            <a href="{{ route('educational.instructors.show', $instructor->id) }}"
                                class="text-dark hover-primary">{{ $instructor->arabic_name ?? $user->full_name }}</a>
                        </h5>
                        <p class="text-muted mb-3 fs-12">@ {{ $user->username }}</p>

                        <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                            <span
                                class="badge {{ $statusBadge[$instructor->status] ?? 'badge-soft-secondary' }} px-3 rounded-pill fs-11">
                                {{ __('educational::messages.status_' . $instructor->status) }}
                            </span>
                            <span
                                class="badge {{ $typeClass[$instructor->employment_type] ?? 'bg-soft-light' }} px-2 fs-11">
                                {{ __('educational::messages.' . $instructor->employment_type) }}
                            </span>
                        </div>

                        <div class="mb-4">
                            @foreach ($instructor->sessionTypes->take(3) as $st)
                                <span class="badge badge-soft-primary px-2 fs-10"
                                    title="{{ $st->name }}">{{ $st->name }}</span>
                            @endforeach
                            @if ($instructor->sessionTypes->count() > 3)
                                <span
                                    class="badge badge-soft-info px-2 fs-10">+{{ $instructor->sessionTypes->count() - 3 }}</span>
                            @endif
                        </div>

                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div class="p-2 border border-dashed rounded bg-light">
                                    <h6 class="mb-1 fs-14 fw-bold text-primary">
                                        {{ $instructor->companies->unique('id')->count() }}</h6>
                                    <p class="text-muted mb-0 fs-10 uppercase">شركات</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 border border-dashed rounded bg-light">
                                    <h6 class="mb-1 fs-14 fw-bold text-warning">{{ $instructor->companies->count() }}</h6>
                                    <p class="text-muted mb-0 fs-10 uppercase">مسارات</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-1 hstack gap-2 justify-content-center">
                            <div class="avatar-xs">
                                <a href="mailto:{{ $user->email }}"
                                    class="avatar-title bg-soft-info text-info rounded-circle fs-14"
                                    title="{{ $user->email }}">
                                    <i class="ri-mail-line"></i>
                                </a>
                            </div>
                            @if ($user->phone)
                                <div class="avatar-xs">
                                    <a href="tel:{{ $user->phone }}"
                                        class="avatar-title bg-soft-success text-success rounded-circle fs-14"
                                        title="{{ $user->phone }}">
                                        <i class="ri-phone-line"></i>
                                    </a>
                                </div>
                            @endif
                            <div class="avatar-xs">
                                <a href="{{ route('educational.instructors.show', $instructor->id) }}"
                                    class="avatar-title bg-soft-primary text-primary rounded-circle fs-14">
                                    <i class="ri-user-search-line"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm py-5 text-center">
                    <div class="avatar-xl mx-auto mb-4 bg-soft-light rounded-circle p-3">
                        <i class="ri-user-voice-line fs-1 text-muted"></i>
                    </div>
                    <h5 class="fw-bold text-muted">{{ __('educational::messages.no_instructors_found') }}</h5>
                    <p class="text-muted mx-auto" style="max-width: 300px;">
                        {{ __('educational::messages.no_instructors_description') }}</p>
                    <a href="{{ route('educational.instructors.create') }}"
                        class="btn btn-primary mt-3 px-4 rounded-pill shadow">
                        <i class="ri-add-line align-middle me-1"></i> {{ __('educational::messages.add_instructor') }}
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    @if ($instructors->hasPages())
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-none bg-transparent">
                    <div class="card-body p-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="text-muted mb-0 fs-13">Showing <b>{{ $instructors->firstItem() }}</b> to
                                <b>{{ $instructors->lastItem() }}</b> of <b>{{ $instructors->total() }}</b> results
                            </p>
                            {{ $instructors->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('styles')
        <style>
            .search-box {
                min-width: 320px;
                position: relative;
            }

            .search-box .search-icon {
                position: absolute;
                left: 13px;
                top: 50%;
                transform: translateY(-50%);
            }

            .search-box .form-control {
                border-radius: 10px;
                font-size: 13px;
                transition: all 0.3s ease;
            }

            .search-box .form-control:focus {
                background-color: #fff !important;
                box-shadow: 0 0 0 1px var(--vz-primary) !important;
            }

            [dir="rtl"] .search-box .search-icon {
                left: auto;
                right: 13px;
            }

            [dir="rtl"] .search-box .form-control {
                padding-left: 12px;
                padding-right: 48px;
            }

            .card-animate {
                transition: all 0.3s ease-in-out;
            }

            .card-animate:hover {
                transform: translateY(-8px);
                box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
            }

            .hover-primary:hover {
                color: var(--vz-primary) !important;
            }

            .uppercase {
                text-transform: uppercase;
            }

            .fs-10 {
                font-size: 10px;
            }

            .status-pulse {
                animation: pulse-sm 2s infinite;
            }

            @keyframes pulse-sm {
                0% {
                    transform: scale(0.95);
                    opacity: 1;
                }

                70% {
                    transform: scale(1.2);
                    opacity: 0.7;
                }

                100% {
                    transform: scale(0.95);
                    opacity: 1;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // Server-side Search
            document.getElementById('lecturerSearch').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    this.form.submit();
                }
            });

            function confirmDeleteLecturer(id) {
                Swal.fire({
                    title: '{{ __('educational::messages.delete_instructor_title') }}',
                    text: '{{ __('educational::messages.delete_instructor_text') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ __('educational::messages.yes_delete') }}',
                    cancelButtonText: '{{ __('educational::messages.cancel') }}',
                    customClass: {
                        confirmButton: 'btn btn-danger w-xs me-2',
                        cancelButton: 'btn btn-light w-xs'
                    },
                    buttonsStyling: false,
                    showCloseButton: true,
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-lecturer-form-' + id).submit();
                    }
                });
            }
        </script>
    @endpush

    <!-- Hidden forms for delete -->
    @foreach ($instructors as $instructor)
        <form id="delete-lecturer-form-{{ $instructor->id }}"
            action="{{ route('educational.instructors.destroy', $instructor->id) }}" method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

@endsection
