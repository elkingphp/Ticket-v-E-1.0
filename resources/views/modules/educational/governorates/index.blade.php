@extends('core::layouts.master')
@section('title', __('educational::messages.governorates'))
@push('styles')
    <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endpush
@section('content')

@include('modules.educational.shared.alerts')

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0" id="governoratesList">
            <div class="card-header border-0 pb-0">
                <div class="row align-items-center gy-3">
                    <div class="col-sm">
                        <h5 class="card-title mb-0 d-flex align-items-center">
                            <div class="avatar-xs me-3">
                                <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-16 shadow-sm">
                                    <i class="ri-map-pin-user-line"></i>
                                </div>
                            </div>
                            {{ __('educational::messages.governorates') }}
                        </h5>
                    </div>
                    <div class="col-sm-auto">
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="{{ route('educational.governorates.create') }}" class="btn btn-primary add-btn shadow-sm">
                                <i class="ri-add-line align-bottom me-1"></i> {{ __('educational::messages.add_governorate') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body mt-3">
                <div class="table-responsive table-card">
                    <table class="table table-hover table-centered align-middle table-nowrap mb-0" id="governoratesTable">
                        <thead class="text-muted table-light">
                            <tr>
                                <th scope="col" style="width: 50px;">#</th>
                                <th scope="col">{{ __('educational::messages.governorate_name_ar') }}</th>
                                <th scope="col">{{ __('educational::messages.governorate_name_en') }}</th>
                                <th scope="col">{{ __('educational::messages.status') }}</th>
                                <th scope="col" style="width: 150px;">{{ __('educational::messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="list form-check-all">
                            @forelse($governorates as $governorate)
                            <tr>
                                <td>
                                    <a href="#" class="fw-medium link-primary">#{{ $governorate->id }}</a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h5 class="fs-14 mb-1 fw-bold text-dark">{{ $governorate->name_ar }}</h5>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="text-muted">{{ $governorate->name_en ?? '-' }}</span></td>
                                <td>
                                    @if($governorate->status == 'active')
                                        <span class="badge bg-success-subtle text-success fs-12 px-2 py-1"><i class="ri-checkbox-circle-line align-bottom me-1"></i> {{ __('educational::messages.status_active') }}</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger fs-12 px-2 py-1"><i class="ri-close-circle-line align-bottom me-1"></i> {{ __('educational::messages.status_inactive') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <ul class="list-inline hstack gap-2 mb-0">
                                        <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="{{ __('educational::messages.edit') }}">
                                            <a href="{{ route('educational.governorates.edit', $governorate->id) }}" class="text-primary d-inline-block edit-item-btn p-2 rounded bg-primary-subtle fs-14">
                                                <i class="ri-pencil-fill"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="{{ __('educational::messages.delete') }}">
                                            <a class="text-danger d-inline-block remove-item-btn p-2 rounded bg-danger-subtle fs-14" href="javascript:void(0);" onclick="confirmDelete({{ $governorate->id }})">
                                                <i class="ri-delete-bin-5-fill"></i>
                                            </a>
                                            <form id="delete-form-{{ $governorate->id }}" action="{{ route('educational.governorates.destroy', $governorate->id) }}" method="POST" class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5">
                                    <div class="text-center py-5">
                                        <div class="avatar-md mx-auto mb-4">
                                            <div class="avatar-title bg-light text-primary rounded-circle fs-24 shadow-sm">
                                                <i class="ri-map-pin-user-line"></i>
                                            </div>
                                        </div>
                                        <h5 class="mt-2 text-dark">{{ __('educational::messages.no_governorates_found') ?? 'لا توجد محافظات' }}</h5>
                                        <p class="text-muted mb-0">لم يتم إضافة أي محافظات إلى النظام بعد.</p>
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
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    function confirmDelete(id) {
        Swal.fire({
            title: "{{ __('educational::messages.delete_governorate_title') ?? 'حذف المحافظة؟' }}",
            text: "{{ __('educational::messages.delete_governorate_text') ?? 'هل أنت متأكد من رغبتك في حذف هذه المحافظة؟ لا يمكن التراجع عن هذا الإجراء.' }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f06548',
            cancelButtonColor: '#878a99',
            confirmButtonText: "{{ __('educational::messages.yes_delete') ?? 'نعم، حذف!' }}",
            cancelButtonText: "{{ __('educational::messages.cancel') ?? 'إلغاء' }}",
            customClass: {
                confirmButton: 'btn btn-danger w-xs me-2 mt-2',
                cancelButton: 'btn btn-ghost-dark w-xs mt-2'
            },
            buttonsStyling: false,
            showCloseButton: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endpush

