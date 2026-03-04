@extends('core::layouts.master')
@section('title', __('educational::messages.training_companies'))

@section('content')

@include('modules.educational.shared.alerts')

<div class="row mb-4 align-items-center">
    <div class="col">
        <div class="d-flex align-items-center">
            <div class="avatar-sm flex-shrink-0">
                <span class="avatar-title bg-soft-primary text-primary rounded-3 fs-2">
                    <i class="ri-building-line"></i>
                </span>
            </div>
            <div class="flex-grow-1 ms-3">
                <h4 class="mb-0 fw-bold">{{ __('educational::messages.training_companies') }}</h4>
                <p class="text-muted mb-0 small">{{ __('educational::messages.manage_and_track_entities') ?? 'إدارة وتتبع سجلات شركات التدريب المعتمدة' }}</p>
            </div>
        </div>
    </div>
    <div class="col-auto">
        <div class="hstack gap-2">
            <div class="search-box">
                <input type="text" class="form-control bg-white border-light shadow-sm" id="companySearchGrid" placeholder="{{ __('educational::messages.search') ?? 'بحث عن شركة...' }}">
                <i class="ri-search-line search-icon text-muted"></i>
            </div>
            <a href="{{ route('educational.companies.create') }}" class="btn btn-primary btn-label waves-effect waves-light shadow-sm">
                <i class="ri-add-line label-icon align-middle fs-16 me-2"></i> {{ __('educational::messages.add_company') }}
            </a>
        </div>
    </div>
</div>

<div class="row" id="company-grid">
    @forelse($companies as $company)
        <div class="col-xl-3 col-lg-4 col-md-6 company-item">
            <div class="card shadow-sm border-0 company-card h-100 overflow-hidden transition-all">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start mb-3">
                        <div class="flex-grow-1">
                            @php
                                $statusClasses = [
                                    'active' => 'badge-soft-success',
                                    'inactive' => 'badge-soft-warning',
                                    'suspended' => 'badge-soft-danger'
                                ];
                            @endphp
                            <span class="badge {{ $statusClasses[$company->status] ?? 'badge-soft-secondary' }} px-2 py-1 fs-11">
                                {{ __('educational::messages.status_'.$company->status) }}
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="dropdown">
                                <button class="btn btn-ghost-secondary btn-sm btn-icon dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-2-fill"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                    <li><a class="dropdown-item py-2" href="{{ route('educational.companies.edit', $company->id) }}"><i class="ri-pencil-fill me-2 text-muted"></i> {{ __('educational::messages.edit') }}</a></li>
                                    <li><hr class="dropdown-divider border-light"></li>
                                    <li><button class="dropdown-item py-2 text-danger" onclick="confirmDeleteCompany({{ $company->id }})"><i class="ri-delete-bin-fill me-2"></i> {{ __('educational::messages.delete') }}</button></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mb-4">
                        <div class="avatar-lg mx-auto bg-light rounded-3 p-2 border border-dashed mb-3 overflow-hidden d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <img src="{{ $company->logo_path ?? asset('assets/images/users/multi-user.jpg') }}" 
                                 alt="" class="img-fluid rounded-2" style="max-height: 100%; object-fit: contain;">
                        </div>
                        <h5 class="fs-16 mb-1 fw-bold text-truncate" title="{{ $company->name }}">{{ $company->name }}</h5>
                        <p class="text-muted small mb-0"><i class="ri-fingerprint-line me-1"></i> {{ $company->registration_number ?? '---' }}</p>
                    </div>

                    <div class="border-top border-light pt-3">
                        <div class="row text-center g-2">
                            <div class="col-6 border-end border-light">
                                <h6 class="mb-1 fw-bold">{{ $company->tracks->count() }}</h6>
                                <p class="text-muted small mb-0">{{ __('educational::messages.tracks') }}</p>
                            </div>
                            <div class="col-6">
                                <h6 class="mb-1 fw-bold">{{ $company->jobProfiles->count() }}</h6>
                                <p class="text-muted small mb-0">{{ __('educational::messages.job_profiles_count') ?? 'ملفات' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light bg-opacity-50 p-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-muted small text-truncate mb-0"><i class="ri-mail-line text-primary me-1"></i> {{ $company->contact_email ?? '-' }}</p>
                        </div>
                        @if($company->website)
                            <div class="flex-shrink-0">
                                <a href="{{ $company->website }}" target="_blank" class="btn btn-sm btn-soft-info p-1 px-2">
                                    <i class="ri-global-line"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <form id="delete-company-form-{{ $company->id }}" action="{{ route('educational.companies.destroy', $company->id) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <div class="empty-state">
                <div class="avatar-xl mx-auto mb-3">
                    <div class="avatar-title bg-soft-light text-primary rounded-circle fs-1">
                        <i class="ri-building-line"></i>
                    </div>
                </div>
                <h5 class="fw-bold">{{ __('educational::messages.no_companies_found') }}</h5>
                <p class="text-muted">{{ __('educational::messages.no_companies_description') }}</p>
                <a href="{{ route('educational.companies.create') }}" class="btn btn-primary mt-2">
                    <i class="ri-add-line align-bottom me-1"></i> {{ __('educational::messages.add_company') }}
                </a>
            </div>
        </div>
    @endforelse
</div>
@endsection

@push('styles')
<style>
    .company-card {
        transition: all 0.3s ease-in-out;
    }
    .company-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
    }
    .search-box { min-width: 280px; position: relative; }
    .search-box .search-icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); }
    .search-box .form-control { padding-left: 38px; border-radius: 8px; }
    [dir="rtl"] .search-box .search-icon { left: auto; right: 13px; }
    [dir="rtl"] .search-box .form-control { padding-left: 12px; padding-right: 38px; }
    .transition-all {
        transition: all 0.3s ease-in-out;
    }
</style>
@endpush

@push('scripts')
<script>
    // Grid search functionality
    const searchInput = document.getElementById('companySearchGrid');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            let value = this.value.toLowerCase();
            let items = document.querySelectorAll(".company-item");
            items.forEach(item => {
                item.style.display = (item.innerText.toLowerCase().indexOf(value) > -1) ? "" : "none";
            });
        });
    }

    function confirmDeleteCompany(id) {
        Swal.fire({
            title: '{{ __("educational::messages.delete_company_title") }}',
            text: '{{ __("educational::messages.delete_company_text") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '{{ __("educational::messages.yes_delete") }}',
            cancelButtonText: '{{ __("educational::messages.cancel") }}',
            customClass: {
                confirmButton: 'btn btn-danger w-xs me-2',
                cancelButton: 'btn btn-light w-xs'
            },
            buttonsStyling: false,
            showCloseButton: true,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-company-form-' + id).submit();
            }
        });
    }
</script>
@endpush
