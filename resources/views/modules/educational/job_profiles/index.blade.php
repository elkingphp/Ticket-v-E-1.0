@extends('core::layouts.master')
@section('title', __('educational::messages.job_profiles'))

@section('content')

    @include('modules.educational.shared.alerts')

    <div class="row mb-4 align-items-center g-3">
        <div class="col-sm">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 avatar-sm me-3">
                    <div class="avatar-title bg-soft-info text-info rounded-circle fs-2">
                        <i class="ri-briefcase-line"></i>
                    </div>
                </div>
                <div>
                    <h4 class="mb-1 fw-bold">{{ __('educational::messages.job_profiles') }}</h4>
                    <p class="text-muted mb-0 small">إدارة الملفات الوظيفية والمسارات التخصصية للمتدربين والموظفين.</p>
                </div>
            </div>
        </div>
        <div class="col-sm-auto">
            <div class="d-flex flex-wrap align-items-center gap-2">

                <a href="{{ route('educational.tracks.index') }}" class="btn btn-soft-secondary btn-icon shadow-sm"
                    title="استعراض التخصصات">
                    <i class="ri-route-line"></i>
                </a>
                <a href="{{ route('educational.job_profiles.create') }}" class="btn btn-info shadow-sm px-4">
                    <i class="ri-add-line align-middle me-1 fw-bold"></i> {{ __('educational::messages.add_job_profile') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm bg-light-subtle">
                <div class="card-body p-3">
                    <form action="{{ route('educational.job_profiles.index') }}" method="GET"
                        class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <div class="search-box">
                                <input type="text" name="search" class="form-control bg-white border-0"
                                    placeholder="بحث باسم الملف أو الكود..." value="{{ request('search') }}">
                                <i class="ri-search-line search-icon text-muted ms-2"></i>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="track_id" class="form-select bg-white border-0">
                                <option value="">كل التخصصات</option>
                                @foreach ($tracks as $track)
                                    <option value="{{ $track->id }}"
                                        {{ request('track_id') == $track->id ? 'selected' : '' }}>{{ $track->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select bg-white border-0">
                                <option value="">كل الحالات</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 shadow-sm">تطبيق الفلترة</button>
                        </div>
                        @if (request()->anyFilled(['search', 'track_id', 'status']))
                            <div class="col-md-2">
                                <a href="{{ route('educational.job_profiles.index') }}"
                                    class="btn btn-soft-danger w-100">إعادة تعيين</a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse($profiles as $profile)
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm card-animate profile-card overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <span
                                    class="badge {{ $profile->status == 'active' ? 'badge-soft-success' : 'badge-soft-danger' }} fs-11 px-2 py-1">
                                    {{ $profile->status == 'active' ? 'نشط' : 'غير نشط' }}
                                </span>
                            </div>
                            <div class="dropdown flex-shrink-0">
                                <a href="javascript:void(0);" class="text-muted" data-bs-toggle="dropdown">
                                    <i class="ri-more-2-fill fs-18"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                    <a class="dropdown-item py-2"
                                        href="{{ route('educational.job_profiles.edit', $profile->id) }}"><i
                                            class="ri-pencil-line me-2 text-muted"></i>
                                        {{ __('educational::messages.edit') }}</a>
                                    <div class="dropdown-divider"></div>
                                    <a href="javascript:void(0);" class="dropdown-item py-2 text-danger"
                                        onclick="confirmDeleteProfile({{ $profile->id }})">
                                        <i class="ri-delete-bin-line me-2"></i> {{ __('educational::messages.delete') }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mb-4">
                            <div class="avatar-md mx-auto mb-3">
                                <div
                                    class="avatar-title bg-soft-info text-info rounded-3 fs-24 shadow-sm border border-info border-opacity-10">
                                    <i class="ri-briefcase-line"></i>
                                </div>
                            </div>
                            <h5 class="mb-1 text-truncate fw-bold">
                                <a href="{{ route('educational.job_profiles.edit', $profile->id) }}"
                                    class="text-dark">{{ $profile->name }}</a>
                            </h5>
                            <div class="text-muted small mb-0">Code: <span
                                    class="badge badge-soft-primary px-2">{{ $profile->code ?? 'N/A' }}</span></div>
                        </div>

                        <div class="mt-4 border-top border-top-dashed pt-3">
                            <div class="mb-3">
                                <label class="fs-11 text-muted mb-1 text-uppercase fw-bold letter-spacing-1">التخصص
                                    المرتبط</label>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 avatar-xxs me-2">
                                        <div class="avatar-title bg-light text-primary rounded-circle">
                                            <i class="ri-route-line fs-10"></i>
                                        </div>
                                    </div>
                                    <p class="mb-0 fw-semibold text-dark fs-13">{{ $profile->track->name ?? '-' }}</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between">
                                <div class="avatar-group">
                                    @foreach ($profile->responsibles->take(4) as $resp)
                                        <div class="avatar-group-item">
                                            <div class="avatar-xs" title="{{ $resp->full_name }}">
                                                <div
                                                    class="avatar-title rounded-circle bg-soft-primary text-primary border border-2 border-white fw-bold fs-10">
                                                    {{ substr($resp->first_name, 0, 1) }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if ($profile->responsibles->count() > 4)
                                        <div class="avatar-group-item">
                                            <div class="avatar-xs">
                                                <div
                                                    class="avatar-title rounded-circle bg-light text-muted border border-2 border-white fs-8 fw-bold">
                                                    +{{ $profile->responsibles->count() - 4 }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <span class="text-muted fs-11 fw-medium">{{ $profile->responsibles->count() }} مسؤول</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light-subtle p-0 border-top-0 overflow-hidden">
                        <a href="{{ route('educational.job_profiles.edit', $profile->id) }}"
                            class="btn btn-ghost-info w-100 rounded-0 py-2 fs-12 fw-semibold">
                            استعراض وتعديل <i class="ri-arrow-right-line align-middle ms-1"></i>
                        </a>
                    </div>
                </div>

                <form id="delete-profile-form-{{ $profile->id }}"
                    action="{{ route('educational.job_profiles.destroy', $profile->id) }}" method="POST"
                    class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="avatar-xl mx-auto mb-4 bg-soft-info bg-opacity-10 rounded-circle p-3">
                    <i class="ri-briefcase-line fs-1 text-info opacity-50"></i>
                </div>
                <h5 class="fw-bold text-dark">لا توجد ملفات وظيفية مطابقة</h5>
                <p class="text-muted">جرب تغيير معايير البحث أو البدء بإضافة ملف وظيفي جديد.</p>
                <a href="{{ route('educational.job_profiles.create') }}"
                    class="btn btn-primary mt-3 px-4 rounded-pill shadow">
                    <i class="ri-add-line align-middle me-1"></i> إضافة ملف وظيفي
                </a>
            </div>
        @endforelse
    </div>

    @if ($profiles->hasPages())
        <div class="row mt-4 mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    {{ $profiles->links() }}
                </div>
            </div>
        </div>
    @endif

    @push('styles')
        <style>
            .profile-card {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .profile-card:hover {
                transform: translateY(-7px);
                box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08) !important;
            }

            .avatar-group-item {
                margin-left: -10px;
            }

            .avatar-group-item:first-child {
                margin-left: 0;
            }

            .search-box {
                position: relative;
            }

            .search-box .search-icon {
                position: absolute;
                left: 13px;
                top: 50%;
                transform: translateY(-50%);
            }

            [dir="rtl"] .search-box .search-icon {
                left: auto;
                right: 13px;
            }

            [dir="rtl"] .search-box input {
                padding-left: 12px;
                padding-right: 48px;
            }

            .letter-spacing-1 {
                letter-spacing: 0.5px;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function confirmDeleteProfile(id) {
                Swal.fire({
                    title: '{{ __('educational::messages.delete_job_profile_title') }}',
                    text: '{{ __('educational::messages.delete_job_profile_text') }}',
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
                        document.getElementById('delete-profile-form-' + id).submit();
                    }
                });
            }
        </script>
    @endpush

@endsection
