@extends('core::layouts.master')
@section('title', __('educational::messages.room_types'))

@push('styles')
    <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@include('modules.educational.shared.alerts')

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0" id="roomTypesList">
            <div class="card-header border-0 pb-0">
                <div class="row align-items-center gy-3">
                    <div class="col-sm">
                        <h5 class="card-title mb-0 d-flex align-items-center">
                            <div class="avatar-xs me-3">
                                <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-16 shadow-sm">
                                    <i class="ri-function-line"></i>
                                </div>
                            </div>
                            {{ __('educational::messages.room_types') }}
                        </h5>
                    </div>
                    <div class="col-sm-auto">
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="{{ route('educational.rooms.index') }}" class="btn btn-soft-secondary shadow-sm">
                                <i class="ri-arrow-go-back-line align-bottom me-1"></i> {{ __('educational::messages.back_to_rooms') ?? 'العودة للقاعات' }}
                            </a>
                            <a href="{{ route('educational.room_types.create') }}" class="btn btn-primary add-btn shadow-sm">
                                <i class="ri-add-line align-bottom me-1"></i> {{ __('educational::messages.add_room_type') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body mt-3">
                <div class="table-responsive table-card">
                    <table class="table table-hover table-centered align-middle table-nowrap mb-0" id="roomTypesTable">
                        <thead class="text-muted table-light">
                            <tr>
                                <th scope="col" style="width: 50px;">#</th>
                                <th scope="col">{{ __('educational::messages.room_type_name') }}</th>
                                <th scope="col">{{ __('educational::messages.room_type_slug') }}</th>
                                <th scope="col">{{ __('educational::messages.room_type_color') }}</th>
                                <th scope="col">{{ __('educational::messages.rooms_count') }}</th>
                                <th scope="col">{{ __('educational::messages.status') }}</th>
                                <th scope="col" style="width: 130px;">{{ __('educational::messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="list form-check-all">
                            @forelse($types as $type)
                            <tr>
                                <td>
                                    <span class="fw-medium text-muted">{{ $loop->iteration }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-xs">
                                            <div class="avatar-title bg-{{ $type->color }}-subtle text-{{ $type->color }} rounded fs-16">
                                                <i class="{{ $type->icon ?? 'ri-door-open-line' }}"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold">{{ $type->name }}</h6>
                                            @if($type->description)
                                                <small class="text-muted">{{ Str::limit($type->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="fs-13 bg-light px-2 py-1 rounded border">{{ $type->slug }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $type->color }} fs-12 px-3 py-1 rounded-pill">
                                        {{ ucfirst($type->color) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fs-12 px-2 py-1">
                                        <i class="ri-door-open-line align-bottom me-1"></i>
                                        {{ $type->rooms()->count() }}
                                    </span>
                                </td>
                                <td>
                                    @if($type->pendingApprovalRequest())
                                        <span class="badge bg-warning text-dark fs-12 px-3 py-2 rounded-pill shadow-sm"
                                              data-bs-toggle="tooltip"
                                              title="بانتظار موافقة الإدارة على عملية الحذف">
                                            <i class="ri-time-line align-middle me-1 fs-14"></i>
                                            {{ __('educational::messages.pending_approval') ?? 'بانتظار الموافقة' }}
                                        </span>
                                    @elseif($type->is_active)
                                        <span class="badge border border-success text-success fs-12 px-3 py-1 rounded-pill bg-success-subtle shadow-sm">
                                            <i class="ri-checkbox-circle-fill align-middle me-1 fs-14"></i> {{ __('educational::messages.status_active') }}
                                        </span>
                                    @else
                                        <span class="badge border border-danger text-danger fs-12 px-3 py-1 rounded-pill bg-danger-subtle shadow-sm">
                                            <i class="ri-close-circle-fill align-middle me-1 fs-14"></i> {{ __('educational::messages.status_inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <ul class="list-inline hstack gap-2 mb-0">
                                        <li class="list-inline-item"
                                            data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top"
                                            title="{{ __('educational::messages.edit') }}">
                                            <a href="{{ route('educational.room_types.edit', $type->id) }}"
                                               class="text-primary d-inline-block p-2 rounded bg-primary-subtle fs-14">
                                                <i class="ri-pencil-fill"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item"
                                            data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top"
                                            title="{{ $type->pendingApprovalRequest() ? 'بانتظار الموافقة على الحذف' : ($type->rooms()->count() > 0 ? __('educational::messages.room_type_has_rooms') : __('educational::messages.delete')) }}">
                                            @if($type->pendingApprovalRequest() || $type->rooms()->count() > 0)
                                                <button type="button" class="btn text-muted d-inline-block p-2 rounded bg-light fs-14 border-0" disabled>
                                                    <i class="ri-delete-bin-5-fill"></i>
                                                </button>
                                            @else
                                                <button type="button"
                                                        class="btn text-danger d-inline-block p-2 rounded bg-danger-subtle fs-14 border-0"
                                                        onclick="confirmDeleteType({{ $type->id }})">
                                                    <i class="ri-delete-bin-5-fill"></i>
                                                </button>
                                                <form id="delete-type-form-{{ $type->id }}"
                                                      action="{{ route('educational.room_types.destroy', $type->id) }}"
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
                                    <div class="text-center p-5">
                                        <div class="avatar-md mx-auto mb-3">
                                            <div class="avatar-title bg-light text-primary rounded-circle fs-24">
                                                <i class="ri-function-line"></i>
                                            </div>
                                        </div>
                                        <h5 class="mt-2">{{ __('educational::messages.no_room_types_found') ?? 'لا توجد أنواع قاعات مسجلة' }}</h5>
                                        <p class="text-muted mb-3">ابدأ بإضافة أول نوع للقاعات.</p>
                                        <a href="{{ route('educational.room_types.create') }}" class="btn btn-primary">
                                            <i class="ri-add-line align-bottom me-1"></i> {{ __('educational::messages.add_room_type') }}
                                        </a>
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
    function confirmDeleteType(id) {
        Swal.fire({
            title: '{{ __("educational::messages.delete_room_type_title") ?? "حذف نوع القاعة؟" }}',
            text: 'سيتم إرسال طلب موافقة للإدارة لحذف هذا النوع. لن يُحذف فوراً.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f06548',
            cancelButtonColor: '#878a99',
            confirmButtonText: '{{ __("educational::messages.confirm") ?? "نعم، حذف" }}',
            cancelButtonText: '{{ __("educational::messages.cancel") ?? "إلغاء" }}',
            customClass: {
                confirmButton: 'btn btn-danger w-xs me-2 mt-2',
                cancelButton: 'btn btn-ghost-dark w-xs mt-2'
            },
            buttonsStyling: false,
            showCloseButton: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-type-form-' + id).submit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endpush
@endsection
