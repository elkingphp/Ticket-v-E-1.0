@extends('core::layouts.master')
@section('title', __('educational::messages.edit_company'))

@section('content')

@include('modules.educational.shared.alerts')

<form action="{{ route('educational.companies.update', $company->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="row">
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body text-center p-4">
                            <div class="mb-4">
                                <h5 class="fs-15 fw-bold mb-3">{{ __('educational::messages.company.logo') }}</h5>
                                <div class="position-relative d-inline-block">
                                    <div class="avatar-xl">
                                        <div class="avatar-title bg-light rounded-circle border-dashed border-2 p-1">
                                            <img id="logo-preview" 
                                                 src="{{ $company->logo_path ?? asset('assets/images/users/multi-user.jpg') }}" 
                                                 class="rounded-circle img-fluid shadow-sm" 
                                                 style="width: 110px; height: 110px; object-fit: cover;">
                                        </div>
                                    </div>
                                    <div class="avatar-xs p-0 rounded-circle profile-photo-edit position-absolute bottom-0 end-0">
                                        <input id="logo-input" name="logo" type="file" class="profile-img-file-input" style="display:none" accept="image/*">
                                        <label for="logo-input" class="profile-photo-edit avatar-xs">
                                            <span class="avatar-title rounded-circle bg-primary text-white cursor-pointer shadow">
                                                <i class="ri-camera-fill"></i>
                                            </span>
                                        </label>
                                    </div>
                                    @if($company->logo_path)
                                    <div class="position-absolute top-0 end-0">
                                        <button type="button" class="btn btn-danger btn-icon btn-sm rounded-circle shadow-sm" id="remove-logo-btn" title="Remove Logo">
                                            <i class="ri-close-line"></i>
                                        </button>
                                        <input type="hidden" name="remove_logo" id="remove-logo-input" value="0">
                                    </div>
                                    @endif
                                </div>
                                <div class="mt-3 text-muted small">
                                    {{ __('educational::messages.logo_specs') ?? 'JPG أو PNG. الحجم الأقصى 1 ميجا.' }}
                                </div>
                            </div>

                            <hr class="my-4 border-light">

                            <div class="text-start">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ __('educational::messages.company.website') }}</label>
                                    <div class="input-group input-group-merge border-light shadow-sm">
                                        <span class="input-group-text bg-light border-0"><i class="ri-global-line text-primary"></i></span>
                                        <input type="url" name="website" class="form-control border-0" placeholder="https://example.com" value="{{ old('website', $company->website) }}">
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-semibold">{{ __('educational::messages.status') }}</label>
                                    <select name="status" class="form-select border-light shadow-sm" required>
                                        <option value="active" {{ old('status', $company->status) == 'active' ? 'selected' : '' }}>{{ __('educational::messages.status_active') }}</option>
                                        <option value="inactive" {{ old('status', $company->status) == 'inactive' ? 'selected' : '' }}>{{ __('educational::messages.status_inactive') }}</option>
                                        <option value="suspended" {{ old('status', $company->status) == 'suspended' ? 'selected' : '' }}>{{ __('educational::messages.status_suspended') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-soft-info border-0">
                            <h5 class="card-title mb-0 fs-14 fw-bold"><i class="ri-map-pin-line me-1 align-bottom"></i> {{ __('educational::messages.company.address') }}</h5>
                        </div>
                        <div class="card-body">
                            <textarea name="address" class="form-control bg-light border-0" rows="4" placeholder="{{ __('educational::messages.company.address_placeholder') ?? 'أدخل العنوان التفصيلي للشركة هنا...' }}">{{ old('address', $company->address) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header border-0 d-flex align-items-center justify-content-between p-3 bg-white">
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs me-2">
                                    <div class="avatar-title rounded bg-soft-primary text-primary">
                                        <i class="ri-information-line fs-18"></i>
                                    </div>
                                </div>
                                <h5 class="card-title mb-0 fw-bold">{{ __('educational::messages.edit_company') }}: {{ $company->name }}</h5>
                            </div>
                            <a href="{{ route('educational.companies.index') }}" class="btn btn-link link-primary p-0 shadow-none">
                                <i class="ri-arrow-left-line align-bottom me-1"></i> {{ __('educational::messages.back_to_list') }}
                            </a>
                        </div>
                        <div class="card-body border-top border-light p-4">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">{{ __('educational::messages.company_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control bg-light border-0" value="{{ old('name', $company->name) }}" placeholder="اسم الشركة كاملاً" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('educational::messages.registration_number') }}</label>
                                    <input type="text" name="registration_number" class="form-control bg-light border-0" value="{{ old('registration_number', $company->registration_number) }}" placeholder="رقم السجل التجاري">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('educational::messages.email') }}</label>
                                    <input type="email" name="contact_email" class="form-control bg-light border-0" value="{{ old('contact_email', $company->contact_email) }}" placeholder="example@company.com">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header border-0 p-3 bg-white">
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs me-2">
                                    <div class="avatar-title rounded bg-soft-success text-success">
                                        <i class="ri-list-check fs-18"></i>
                                    </div>
                                </div>
                                <h5 class="card-title mb-0 fw-bold">{{ __('educational::messages.company.tracks_and_profiles') }}</h5>
                            </div>
                        </div>
                        <div class="card-body p-0 border-top border-light">
                            <div class="accordion accordion-flush" id="tracksAccordion">
                                @forelse($tracks as $track)
                                    @php 
                                        $selectedCountInTrack = $track->jobProfiles->whereIn('id', $selectedProfiles)->count();
                                    @endphp
                                    <div class="accordion-item border-0 border-bottom">
                                        <h2 class="accordion-header" id="heading-{{ $track->id }}">
                                            <button class="accordion-button {{ $selectedCountInTrack > 0 ? '' : 'collapsed' }} py-3 px-4 bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $track->id }}">
                                                <div class="d-flex align-items-center w-100">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar-xs">
                                                            <div class="avatar-title rounded-circle {{ $selectedCountInTrack > 0 ? 'bg-success text-white' : 'bg-soft-primary text-primary' }} fw-bold">
                                                                {{ mb_substr($track->name, 0, 1) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="fs-14 mb-0 fw-bold">{{ $track->name }}</h6>
                                                        <small class="text-muted">{{ $selectedCountInTrack }} / {{ $track->jobProfiles->count() }} {{ __('educational::messages.job_profiles') }}</small>
                                                    </div>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse-{{ $track->id }}" class="accordion-collapse collapse {{ $selectedCountInTrack > 0 ? 'show' : '' }}" data-bs-parent="#tracksAccordion">
                                            <div class="accordion-body bg-light bg-opacity-40 p-4">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="text-muted small fw-semibold">{{ __('educational::messages.job_profiles') }}</span>
                                                    <button type="button" class="btn btn-sm btn-link link-primary p-0 text-decoration-none select-all-track" data-track-id="{{ $track->id }}">
                                                        {{ __('educational::messages.select_all') }}
                                                    </button>
                                                </div>
                                                <div class="row g-2 track-profiles-{{ $track->id }}">
                                                    @foreach($track->jobProfiles as $profile)
                                                        @php $isSelected = in_array($profile->id, $selectedProfiles); @endphp
                                                        <div class="col-md-6">
                                                            <div class="form-check card-radio p-0">
                                                                <input class="form-check-input d-none" type="checkbox" name="job_profiles[]" value="{{ $profile->id }}" id="prof-{{ $profile->id }}" {{ $isSelected ? 'checked' : '' }}>
                                                                <label class="form-check-label d-flex align-items-center p-3 border rounded-3 cursor-pointer bg-white shadow-sm transition-all" for="prof-{{ $profile->id }}">
                                                                    <div class="flex-grow-1">
                                                                        <span class="fs-13 fw-medium">{{ $profile->name }}</span>
                                                                        <div class="small text-muted mt-1">Code: <code class="text-primary">{{ $profile->code ?? 'N/A' }}</code></div>
                                                                    </div>
                                                                    <div class="flex-shrink-0 ms-2">
                                                                        <div class="avatar-xs">
                                                                            <div class="avatar-title rounded-circle bg-soft-success text-success fs-18 checked-icon shadow-sm" style="{{ $isSelected ? '' : 'display:none' }}">
                                                                                <i class="ri-checkbox-circle-fill"></i>
                                                                            </div>
                                                                            <div class="avatar-title rounded-circle bg-light text-muted fs-18 unchecked-icon" style="{{ $isSelected ? 'display:none' : '' }}">
                                                                                <i class="ri-checkbox-blank-circle-line"></i>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-5 text-center text-muted">
                                        <i class="ri-search-line fs-32 d-block mb-2"></i>
                                        {{ __('educational::messages.no_tracks_found') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 hstack gap-2 justify-content-end">
                        <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm fw-bold">
                            {{ __('educational::messages.save') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('styles')
<style>
    .cursor-pointer { cursor: pointer; }
    .transition-all { transition: all 0.2s ease-in-out; }
    .card-radio label:hover {
        border-color: var(--vz-primary) !important;
        transform: translateY(-2px);
    }
    .card-radio input:checked + label {
        border-color: var(--vz-success) !important;
        background-color: rgba(43, 181, 125, 0.03) !important;
        box-shadow: 0 5px 15px rgba(43, 181, 125, 0.1) !important;
    }
    .card-radio input:checked + label .checked-icon {
        display: flex !important;
    }
    .card-radio input:checked + label .unchecked-icon {
        display: none !important;
    }
    .accordion-button:not(.collapsed) {
        background-color: rgba(64, 81, 137, 0.05);
        color: var(--vz-primary);
    }
</style>
@endpush

@push('scripts')
<script>
    // Logo Preview & Removal Logic
    const logoInput = document.getElementById('logo-input');
    const logoPreview = document.getElementById('logo-preview');
    const removeBtn = document.getElementById('remove-logo-btn');
    const removeInput = document.getElementById('remove-logo-input');

    if (logoInput) {
        logoInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoPreview.src = e.target.result;
                    if (removeInput) removeInput.value = '0';
                    if (removeBtn) removeBtn.parentElement.style.display = 'block';
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            logoPreview.src = "{{ asset('assets/images/users/multi-user.jpg') }}";
            removeInput.value = '1';
            logoInput.value = '';
            removeBtn.parentElement.style.display = 'none';
        });
    }

    // Select All Track Logic
    document.querySelectorAll('.select-all-track').forEach(btn => {
        btn.addEventListener('click', function() {
            const trackId = this.dataset.trackId;
            const container = document.querySelector('.track-profiles-' + trackId);
            const checkboxes = container.querySelectorAll('input[type="checkbox"]');
            
            // Check if all are already checked
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
                // Manually trigger icons update if needed or rely on CSS
                const label = cb.nextElementSibling;
                if (label) {
                    const checkedIcon = label.querySelector('.checked-icon');
                    const uncheckedIcon = label.querySelector('.unchecked-icon');
                    if (checkedIcon) checkedIcon.style.display = !allChecked ? 'flex' : 'none';
                    if (uncheckedIcon) uncheckedIcon.style.display = !allChecked ? 'none' : 'flex';
                }
            });

            this.textContent = allChecked ? "{{ __('educational::messages.select_all') }}" : "{{ __('educational::messages.unselect_all') }}";
        });
    });
</script>
@endpush
@endsection
