@extends('core::layouts.master')
@section('title', __('educational::messages.campuses'))

@push('styles')
    <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@include('modules.educational.shared.alerts')

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0" id="campusesList">
            <div class="card-header border-0 pb-0">
                <div class="row align-items-center gy-3">
                    <div class="col-sm">
                        <h5 class="card-title mb-0 d-flex align-items-center">
                            <div class="avatar-xs me-3">
                                <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-16 shadow-sm">
                                    <i class="ri-building-4-line"></i>
                                </div>
                            </div>
                            {{ __('educational::messages.campuses') }}
                        </h5>
                    </div>
                    <div class="col-sm-auto">
                        <div class="d-flex gap-1 flex-wrap">
                            <button type="button" class="btn btn-soft-info shadow-sm" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                                <i class="ri-filter-2-line align-bottom me-1"></i> {{ __('educational::messages.filter') ?? 'تصفية' }}
                            </button>
                            <a href="{{ route('educational.campuses.create') }}" class="btn btn-primary add-btn shadow-sm">
                                <i class="ri-add-line align-bottom me-1"></i> {{ __('educational::messages.add_campus') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="collapse {{ request()->hasAny(['status', 'search']) ? 'show' : '' }}" id="filterCollapse">
                <div class="card-body bg-light bg-opacity-50 border-bottom border-light shadow-none p-4">
                    <form action="{{ route('educational.campuses.index') }}" method="GET">
                        <div class="row g-4 align-items-end">
                            <div class="col-lg-5 col-md-7">
                                <label for="search" class="form-label fw-semibold text-muted mb-2">
                                    <i class="ri-search-line align-bottom me-1"></i> {{ __('educational::messages.search') ?? 'بحث' }}
                                </label>
                                <div class="input-group border-0 shadow-sm">
                                    <span class="input-group-text bg-white border-0"><i class="ri-search-line text-muted"></i></span>
                                    <input type="text" name="search" class="form-control border-0 fs-14" value="{{ request('search') }}" placeholder="{{ __('educational::messages.search_placeholder') ?? 'بحث بالاسم أو الكود أو العنوان...' }}">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-5">
                                <label class="form-label fw-semibold text-muted mb-2 d-block">
                                    <i class="ri-shield-check-line align-bottom me-1"></i> {{ __('educational::messages.status') }}
                                </label>
                                <div class="d-flex align-items-center gap-3 p-2 border rounded shadow-sm bg-white" style="height: 41px;">
                                    <div class="form-check form-radio-primary mb-0">
                                        <input class="form-check-input" type="radio" name="status" id="statusAll" value="" {{ request('status') === '' || request('status') === null ? 'checked' : '' }}>
                                        <label class="form-check-label mb-0 fs-13" for="statusAll">
                                            {{ __('educational::messages.view_all') ?? 'عرض الكل' }}
                                        </label>
                                    </div>
                                    <div class="form-check form-radio-success mb-0">
                                        <input class="form-check-input" type="radio" name="status" id="statusActive" value="active" {{ request('status') === 'active' ? 'checked' : '' }}>
                                        <label class="form-check-label mb-0 fs-13" for="statusActive">
                                            {{ __('educational::messages.status_active') }}
                                        </label>
                                    </div>
                                    <div class="form-check form-radio-danger mb-0">
                                        <input class="form-check-input" type="radio" name="status" id="statusInactive" value="inactive" {{ request('status') === 'inactive' ? 'checked' : '' }}>
                                        <label class="form-check-label mb-0 fs-13" for="statusInactive">
                                            {{ __('educational::messages.status_inactive') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100 shadow-sm fw-medium">
                                        <i class="ri-search-line me-1 align-bottom"></i> {{ __('educational::messages.search') ?? 'بحث' }}
                                    </button>
                                    <a href="{{ route('educational.campuses.index') }}" class="btn btn-light w-100 shadow-sm fw-medium text-dark">
                                        <i class="ri-refresh-line me-1 align-bottom text-muted"></i> {{ __('educational::messages.reset') ?? 'إلغاء' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body mt-3">
                <div class="table-responsive table-card">
                    <table class="table table-hover table-centered align-middle table-nowrap mb-0" id="campusesTable">
                        <thead class="text-muted table-light">
                            <tr>
                                <th scope="col" style="width: 50px;">#</th>
                                <th scope="col">{{ __('educational::messages.campus_name') }}</th>
                                <th scope="col">{{ __('educational::messages.campus_code') }}</th>
                                <th scope="col">{{ __('educational::messages.campus_address') }}</th>
                                <th scope="col">{{ __('educational::messages.status') }}</th>
                                <th scope="col" colspan="2" style="width: 150px;">{{ __('educational::messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="list form-check-all">
                            @forelse($campuses as $campus)
                            <tr>
                                <td>
                                    <a href="#" class="fw-medium link-primary">#{{ $campus->id }}</a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs me-2">
                                            <div class="avatar-title bg-primary-subtle text-primary rounded fs-16 shadow-none">
                                                <i class="ri-building-4-line"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="fs-14 mb-0 fw-bold text-dark">{{ $campus->name }}</h5>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark fs-12 border px-2 py-1">
                                        <i class="ri-qr-code-line align-bottom me-1 text-muted"></i> {{ $campus->code }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted fs-13">
                                        <i class="ri-map-pin-line align-bottom me-1"></i> {{ $campus->address ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    @if($campus->pendingApprovalRequest())
                                        <div class="d-flex align-items-center justify-content-center">
                                            <span class="badge bg-warning text-dark fs-12 px-3 py-2 rounded-pill shadow-sm"
                                                  data-bs-toggle="tooltip"
                                                  title="{{ __('educational::messages.pending_deletion_approval') ?? 'بانتظار موافقة الإدارة على عملية الحذف' }}">
                                                <i class="ri-time-line align-middle me-1 fs-14"></i>
                                                {{ __('educational::messages.pending_approval') ?? 'بانتظار الموافقة' }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center">
                                            @if($campus->status == 'active')
                                                <span class="badge border border-success text-success fs-12 px-3 py-1 rounded-pill bg-success-subtle shadow-sm">
                                                    <i class="ri-checkbox-circle-fill align-middle me-1 fs-14"></i> {{ __('educational::messages.status_active') }}
                                                </span>
                                            @else
                                                <span class="badge border border-danger text-danger fs-12 px-3 py-1 rounded-pill bg-danger-subtle shadow-sm">
                                                    <i class="ri-close-circle-fill align-middle me-1 fs-14"></i> {{ __('educational::messages.status_inactive') }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td colspan="2">
                                    <ul class="list-inline hstack gap-2 mb-0">
                                        <li class="list-inline-item"
                                            data-bs-toggle="tooltip" data-bs-trigger="hover"
                                            data-bs-placement="top"
                                            title="{{ __('educational::messages.edit') }}">
                                            <a href="{{ route('educational.campuses.edit', $campus->id) }}"
                                               class="text-primary d-inline-block edit-item-btn p-2 rounded bg-primary-subtle fs-14">
                                                <i class="ri-pencil-fill"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item"
                                            data-bs-toggle="tooltip" data-bs-trigger="hover"
                                            data-bs-placement="top"
                                            title="{{ __('educational::messages.delete') }}">
                                            @if($campus->pendingApprovalRequest())
                                                <button type="button" class="btn text-muted d-inline-block p-2 rounded bg-light fs-14 border-0" disabled>
                                                    <i class="ri-delete-bin-5-fill"></i>
                                                </button>
                                            @else
                                                <button type="button"
                                                        class="btn text-danger d-inline-block p-2 rounded bg-danger-subtle fs-14 border-0"
                                                        onclick="confirmDeleteCampus({{ $campus->id }})">
                                                    <i class="ri-delete-bin-5-fill"></i>
                                                </button>
                                                <form id="delete-campus-form-{{ $campus->id }}"
                                                      action="{{ route('educational.campuses.destroy', $campus->id) }}"
                                                      method="POST" class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @endif
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7">
                                    <div class="text-center p-4">
                                        <div class="avatar-md mx-auto mb-3">
                                            <div class="avatar-title bg-light text-primary rounded-circle fs-24">
                                                <i class="ri-building-4-line"></i>
                                            </div>
                                        </div>
                                        <h5 class="mt-2">{{ __('educational::messages.no_campuses_found') ?? 'لا توجد فروع مسجلة' }}</h5>
                                        <p class="text-muted mb-0">ابدأ بإضافة أول فرع أو مقر للمركز التدريبي.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    function confirmDeleteCampus(id) {
        Swal.fire({
            title: '{{ __("educational::messages.delete_campus_title") ?? "حذف الفرع؟" }}',
            text: '{{ __("educational::messages.delete_campus_text") ?? "سيتم إنشاء طلب الإدارة لحذف الفرع وكافة المباني والقاعات التابعة له!" }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f06548',
            cancelButtonColor: '#878a99',
            confirmButtonText: '{{ __("educational::messages.confirm") ?? "نعم، تأكيد الحذف" }}',
            cancelButtonText: '{{ __("educational::messages.cancel") ?? "إلغاء" }}',
            customClass: {
                confirmButton: 'btn btn-danger w-xs me-2 mt-2',
                cancelButton: 'btn btn-ghost-dark w-xs mt-2'
            },
            buttonsStyling: false,
            showCloseButton: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-campus-form-' + id).submit();
            }
        });
    }

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endpush
@endsection
