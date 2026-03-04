@extends('core::layouts.master')
@section('title', __('educational::messages.tracks'))

@section('content')

    @include('modules.educational.shared.alerts')

    <div class="row mb-4 align-items-center g-3">
        <div class="col-sm">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 avatar-sm me-3">
                    <div class="avatar-title bg-soft-primary text-primary rounded-circle fs-2">
                        <i class="ri-route-line"></i>
                    </div>
                </div>
                <div>
                    <h4 class="mb-1 fw-bold">{{ __('educational::messages.tracks') }}</h4>
                    <p class="text-muted mb-0 small">إدارة المسارات الأكاديمية والتخصصات التدريبية وتعيين المسؤولين.</p>
                </div>
            </div>
        </div>
        <div class="col-sm-auto">
            <div class="d-flex flex-wrap align-items-center gap-2">

                <a href="{{ route('educational.tracks.create') }}" class="btn btn-primary shadow-sm px-4">
                    <i class="ri-add-line align-middle me-1 fw-bold"></i> {{ __('educational::messages.add_track') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm bg-light-subtle">
                <div class="card-body p-3">
                    <form action="{{ route('educational.tracks.index') }}" method="GET"
                        class="row g-3 align-items-center">
                        <div class="col-md-5">
                            <div class="search-box">
                                <input type="text" name="search" class="form-control bg-white border-0"
                                    placeholder="بحث باسم التخصص أو الكود..." value="{{ request('search') }}">
                                <i class="ri-search-line search-icon text-muted ms-2"></i>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select bg-white border-0">
                                <option value="">كل الحالات</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-info w-100">فلترة</button>
                        </div>
                        @if (request()->anyFilled(['search', 'status']))
                            <div class="col-md-2">
                                <a href="{{ route('educational.tracks.index') }}"
                                    class="btn btn-soft-danger w-100">إلغاء</a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse($tracks as $track)
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm card-animate track-card overflow-hidden">
                    <div class="card-body p-0">
                        <div class="p-4">
                            <div class="d-flex align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <span
                                        class="badge {{ $track->is_active ? 'badge-soft-success' : 'badge-soft-danger' }} mb-2">
                                        {{ $track->is_active ? 'نشط' : 'غير نشط' }}
                                    </span>
                                    <h5 class="mb-1 text-truncate">
                                        <a href="{{ route('educational.tracks.edit', $track->id) }}"
                                            class="text-dark fw-bold">{{ $track->name }}</a>
                                    </h5>
                                    <div class="text-muted small">Code: <code
                                            class="text-primary">{{ $track->code ?? 'N/A' }}</code></div>
                                </div>
                                <div class="dropdown flex-shrink-0">
                                    <a href="javascript:void(0);" class="text-muted" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-fill fs-18"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                        <a class="dropdown-item py-2"
                                            href="{{ route('educational.tracks.edit', $track->id) }}"><i
                                                class="ri-pencil-line me-2 text-muted"></i>
                                            {{ __('educational::messages.edit') }}</a>
                                        <div class="dropdown-divider"></div>
                                        <a href="javascript:void(0);" class="dropdown-item py-2 text-danger"
                                            onclick="confirmDeleteTrack({{ $track->id }})">
                                            <i class="ri-delete-bin-line me-2"></i>
                                            {{ __('educational::messages.delete') }}
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="avatar-group">
                                    @foreach ($track->responsibles->take(3) as $resp)
                                        <div class="avatar-group-item">
                                            <div class="avatar-xs" title="{{ $resp->full_name }}">
                                                <div
                                                    class="avatar-title rounded-circle bg-soft-info text-info border border-2 border-white fw-bold">
                                                    {{ substr($resp->first_name, 0, 1) }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if ($track->responsibles->count() > 3)
                                        <div class="avatar-group-item">
                                            <div class="avatar-xs">
                                                <div
                                                    class="avatar-title rounded-circle bg-light text-muted border border-2 border-white fs-10 fw-bold">
                                                    +{{ $track->responsibles->count() - 3 }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-muted small">
                                    <b>{{ $track->responsibles->count() }}</b> مسؤولين
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded-3 d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3 avatar-xs">
                                            <div class="avatar-title bg-soft-warning text-warning rounded-circle">
                                                <i class="ri-briefcase-line text-info"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-bold">{{ $track->job_profiles_count }}</h6>
                                            <p class="text-muted mb-0 small">{{ __('educational::messages.job_profiles') }}
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <a href="{{ route('educational.job_profiles.index', ['track_id' => $track->id]) }}"
                                                class="btn btn-soft-primary btn-sm rounded-pill px-3">
                                                استعراض
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-soft-primary p-2 text-center border-top border-light">
                            <a href="{{ route('educational.tracks.edit', $track->id) }}"
                                class="text-primary small fw-semibold">تعديل التفاصيل <i
                                    class="ri-arrow-right-line align-middle ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 mt-4">
                <div class="avatar-xl mx-auto mb-4 bg-soft-light rounded-circle p-3">
                    <i class="ri-route-line fs-1 text-muted"></i>
                </div>
                <h5 class="fw-bold text-muted">لا يوجد تخصصات حالياً</h5>
                <p class="text-muted mx-auto" style="max-width: 400px;">ابدأ بإضافة أول تخصص أكاديمي أو تقني للنظام وتوزيع
                    المسؤوليات.</p>
                <a href="{{ route('educational.tracks.create') }}" class="btn btn-primary mt-3 px-4 rounded-pill shadow">
                    <i class="ri-add-line align-middle me-1"></i> {{ __('educational::messages.add_track') }}
                </a>
            </div>
        @endforelse
    </div>

    @push('styles')
        <style>
            .track-card {
                transition: all 0.3s ease-in-out;
            }

            .track-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
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
        </style>
    @endpush

    @push('scripts')
        <script>
            function confirmDeleteTrack(id) {
                Swal.fire({
                    title: '{{ __('educational::messages.delete_track_title') }}',
                    text: '{{ __('educational::messages.delete_track_text') }}',
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
                        document.getElementById('delete-track-form-' + id).submit();
                    }
                });
            }
        </script>
    @endpush

    @foreach ($tracks as $track)
        <form id="delete-track-form-{{ $track->id }}" action="{{ route('educational.tracks.destroy', $track->id) }}"
            method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

@endsection
