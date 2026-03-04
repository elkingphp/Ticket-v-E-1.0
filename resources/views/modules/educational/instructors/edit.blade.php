@extends('core::layouts.master')
@section('title', __('educational::messages.edit_instructor'))

@section('content')

    @include('modules.educational.shared.alerts')

    <div class="row justify-content-center">
        <div class="col-xl-10">
            <form action="{{ route('educational.instructors.update', $instructor->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header border-0 d-flex align-items-center justify-content-between bg-white py-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-xs me-2">
                                <div class="avatar-title rounded bg-soft-info text-info">
                                    <i class="ri-user-settings-line fs-18"></i>
                                </div>
                            </div>
                            <h5 class="card-title mb-0 fw-bold">{{ __('educational::messages.edit_instructor') }}</h5>
                        </div>
                        <a href="{{ route('educational.instructors.index') }}"
                            class="btn btn-link link-primary p-0 shadow-none fw-medium">
                            <i class="ri-arrow-left-line align-bottom me-1"></i>
                            {{ __('educational::messages.back_to_list') }}
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Account & Academic Details -->
                    <div class="col-lg-12">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header border-0 bg-soft-light py-2">
                                <h6 class="card-title mb-0 fs-13 fw-bold text-uppercase"><i
                                        class="ri-account-circle-line me-1"></i>
                                    {{ __('educational::messages.lecturer_info') }} ({{ $instructor->user->full_name }})
                                </h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">{{ __('educational::messages.first_name') }}
                                            <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" class="form-control bg-light border-0"
                                            value="{{ old('first_name', $instructor->user->first_name) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">{{ __('educational::messages.last_name') }}
                                            <span class="text-danger">*</span></label>
                                        <input type="text" name="last_name" class="form-control bg-light border-0"
                                            value="{{ old('last_name', $instructor->user->last_name) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">{{ __('educational::messages.username') }}
                                            <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0 text-muted">@</span>
                                            <input type="text" name="username" class="form-control bg-light border-0"
                                                value="{{ old('username', $instructor->user->username) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label
                                            class="form-label fw-semibold">{{ __('educational::messages.password') }}</label>
                                        <input type="password" name="password" class="form-control bg-light border-0"
                                            placeholder="اتركه فارغاً إذا كنت لا تريد تغييره">
                                    </div>

                                    <div class="col-md-4">
                                        <label
                                            class="form-label fw-semibold">{{ __('educational::messages.arabic_name') }}</label>
                                        <input type="text" name="arabic_name" class="form-control bg-light border-0"
                                            value="{{ old('arabic_name', $instructor->arabic_name) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label
                                            class="form-label fw-semibold">{{ __('educational::messages.english_name') }}</label>
                                        <input type="text" name="english_name" class="form-control bg-light border-0"
                                            value="{{ old('english_name', $instructor->english_name) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label
                                            class="form-label fw-semibold">{{ __('educational::messages.gender') }}</label>
                                        <select name="gender" class="form-select bg-light border-0">
                                            <option value="">{{ __('educational::messages.gender') }}</option>
                                            <option value="male"
                                                {{ old('gender', $instructor->gender) == 'male' ? 'selected' : '' }}>
                                                {{ __('educational::messages.male') }}</option>
                                            <option value="female"
                                                {{ old('gender', $instructor->gender) == 'female' ? 'selected' : '' }}>
                                                {{ __('educational::messages.female') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact & Personal Details -->
                    <div class="col-lg-7">
                        <div class="card shadow-sm border-0 mb-4 h-100">
                            <div class="card-header border-0 bg-soft-light py-2">
                                <h6 class="card-title mb-0 fs-13 fw-bold text-uppercase"><i
                                        class="ri-contacts-line me-1"></i> {{ __('educational::messages.contact_info') }} &
                                    {{ __('educational::messages.personal_info') }}</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('educational::messages.email') }} <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i
                                                    class="ri-mail-line"></i></span>
                                            <input type="email" name="email" class="form-control bg-light border-0"
                                                value="{{ old('email', $instructor->user->email) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="form-label fw-semibold">{{ __('educational::messages.phone') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i
                                                    class="ri-phone-line"></i></span>
                                            <input type="text" name="phone" class="form-control bg-light border-0"
                                                value="{{ old('phone', $instructor->user->phone) }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label
                                            class="form-label fw-semibold">{{ __('educational::messages.national_id') }}</label>
                                        <input type="text" name="national_id" class="form-control bg-light border-0"
                                            value="{{ old('national_id', $instructor->getSensitiveData('national_id')) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="form-label fw-semibold">{{ __('educational::messages.passport_number') }}</label>
                                        <input type="text" name="passport_number"
                                            class="form-control bg-light border-0"
                                            value="{{ old('passport_number', $instructor->getSensitiveData('passport_number')) }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label
                                            class="form-label fw-semibold">{{ __('educational::messages.date_of_birth') }}</label>
                                        <input type="date" name="date_of_birth" class="form-control bg-light border-0"
                                            value="{{ old('date_of_birth', optional($instructor->date_of_birth)->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="form-label fw-semibold">{{ __('educational::messages.governorate') }}</label>
                                        <select name="governorate_id" class="form-select bg-light border-0">
                                            <option value="">{{ __('educational::messages.governorate') }}</option>
                                            @foreach ($governorates as $gov)
                                                <option value="{{ $gov->id }}"
                                                    {{ old('governorate_id', $instructor->governorate_id) == $gov->id ? 'selected' : '' }}>
                                                    {{ $gov->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <label
                                            class="form-label fw-semibold">{{ __('educational::messages.address') }}</label>
                                        <textarea name="address" class="form-control bg-light border-0" rows="2">{{ old('address', $instructor->address) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employment & Academic Details -->
                    <div class="col-lg-5">
                        <div class="card shadow-sm border-0 mb-4 h-100">
                            <div class="card-header border-0 bg-soft-light py-2">
                                <h6 class="card-title mb-0 fs-13 fw-bold text-uppercase"><i
                                        class="ri-briefcase-line me-1"></i>
                                    {{ __('educational::messages.employment_info') }} &
                                    {{ __('educational::messages.academic_info') }}</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label
                                            class="form-label fw-semibold">{{ __('educational::messages.employment_type') }}
                                            <span class="text-danger">*</span></label>
                                        <select name="employment_type" class="form-select bg-light border-0" required>
                                            <option value="contractor"
                                                {{ old('employment_type', $instructor->employment_type) == 'contractor' ? 'selected' : '' }}>
                                                {{ __('educational::messages.contractor') }}</option>
                                            <option value="full_time"
                                                {{ old('employment_type', $instructor->employment_type) == 'full_time' ? 'selected' : '' }}>
                                                {{ __('educational::messages.full_time') }}</option>
                                            <option value="part_time"
                                                {{ old('employment_type', $instructor->employment_type) == 'part_time' ? 'selected' : '' }}>
                                                {{ __('educational::messages.part_time') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">{{ __('educational::messages.status') }}
                                            <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select bg-light border-0" required>
                                            <option value="active"
                                                {{ old('status', $instructor->status) == 'active' ? 'selected' : '' }}>
                                                {{ __('educational::messages.status_active') }}</option>
                                            <option value="inactive"
                                                {{ old('status', $instructor->status) == 'inactive' ? 'selected' : '' }}>
                                                {{ __('educational::messages.status_inactive') }}</option>
                                            <option value="suspended"
                                                {{ old('status', $instructor->status) == 'suspended' ? 'selected' : '' }}>
                                                {{ __('educational::messages.status_suspended') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label
                                            class="form-label fw-semibold">{{ __('educational::messages.specialization') }}</label>
                                        <select name="track_id" class="form-select bg-light border-0">
                                            <option value="">{{ __('educational::messages.specialization') }}
                                            </option>
                                            @foreach ($tracks as $track)
                                                <option value="{{ $track->id }}"
                                                    {{ old('track_id', $instructor->track_id) == $track->id ? 'selected' : '' }}>
                                                    {{ $track->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">{{ __('educational::messages.note') }}
                                            ({{ __('educational::messages.specialization') }})</label>
                                        <input type="text" name="specialization"
                                            class="form-control bg-light border-0"
                                            value="{{ old('specialization', $instructor->specialization_notes) }}">
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold mb-3">أنواع المحاضرات المدعومة <span
                                                class="text-danger">*</span></label>
                                        <div class="row g-2">
                                            @foreach ($sessionTypes as $st)
                                                @php $isChecked = is_array(old('session_types', $instructorSessionTypes)) && in_array($st->id, old('session_types', $instructorSessionTypes)); @endphp
                                                <div class="col-md-6 mb-1">
                                                    <div class="form-check card-radio p-0 h-100">
                                                        <input class="form-check-input d-none" type="checkbox"
                                                            name="session_types[]" value="{{ $st->id }}"
                                                            id="st-{{ $st->id }}"
                                                            {{ $isChecked ? 'checked' : '' }}>
                                                        <label
                                                            class="form-check-label d-flex align-items-center p-2 border rounded-3 cursor-pointer bg-white shadow-sm transition-all h-100 mb-0"
                                                            for="st-{{ $st->id }}">
                                                            <div class="flex-grow-1">
                                                                <span
                                                                    class="fs-12 fw-semibold d-block text-dark">{{ $st->name }}</span>
                                                            </div>
                                                            <div class="flex-shrink-0 ms-2">
                                                                <div class="avatar-xs" style="width: 20px; height: 20px;">
                                                                    <div class="avatar-title rounded-circle bg-soft-success text-success fs-14 checked-icon shadow-xs"
                                                                        style="{{ $isChecked ? '' : 'display:none' }}">
                                                                        <i class="ri-checkbox-circle-fill"></i>
                                                                    </div>
                                                                    <div class="avatar-title rounded-circle bg-light text-muted fs-14 unchecked-icon"
                                                                        style="{{ $isChecked ? 'display:none' : '' }}">
                                                                        <i class="ri-checkbox-blank-circle-line"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @error('session_types')
                                            <div class="text-danger fs-11 mt-2 text-wrap">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12">
                                        <label
                                            class="form-label fw-semibold">{{ __('educational::messages.bio') }}</label>
                                        <textarea name="bio" class="form-control bg-light border-0" rows="3">{{ old('bio', $instructor->bio) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Training Companies Assignment -->
                    <div class="col-lg-12 mt-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header border-0 bg-soft-info py-3">
                                <h6 class="card-title mb-0 fs-14 fw-bold"><i class="ri-building-line me-1"></i>
                                    {{ __('educational::messages.assignments') }}</h6>
                            </div>
                            <div class="card-body p-0 border-top border-light">
                                <div class="accordion accordion-flush" id="companiesAccordion">
                                    @forelse($companies as $company)
                                        @php
                                            $companyAssignments = $instructorAssignments[$company->id] ?? [];
                                            $isCompanySelected = count($companyAssignments) > 0;
                                        @endphp
                                        <div class="accordion-item border-0 border-bottom">
                                            <h2 class="accordion-header" id="heading-comp-{{ $company->id }}">
                                                <button
                                                    class="accordion-button {{ $isCompanySelected ? '' : 'collapsed' }} py-3 px-4 bg-transparent"
                                                    type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapse-comp-{{ $company->id }}">
                                                    <div class="d-flex align-items-center w-100">
                                                        <div class="flex-shrink-0">
                                                            <div class="avatar-sm">
                                                                <div
                                                                    class="avatar-title rounded {{ $isCompanySelected ? 'bg-soft-success text-success border border-success' : 'bg-soft-primary text-primary' }}">
                                                                    <i class="ri-building-line"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="fs-15 mb-0 fw-semibold">{{ $company->name }}</h6>
                                                            <small
                                                                class="{{ $isCompanySelected ? 'text-success fw-medium' : 'text-muted' }}">{{ count($companyAssignments) }}
                                                                / {{ $company->jobProfiles->count() }}
                                                                {{ __('educational::messages.job_profiles') }}</small>
                                                        </div>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="collapse-comp-{{ $company->id }}"
                                                class="accordion-collapse collapse {{ $isCompanySelected ? 'show' : '' }}"
                                                data-bs-parent="#companiesAccordion">
                                                <div class="accordion-body bg-light bg-opacity-40 p-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <span
                                                            class="text-muted small fw-semibold">{{ __('educational::messages.job_profiles') }}</span>
                                                        <button type="button"
                                                            class="btn btn-sm btn-link link-primary p-0 text-decoration-none select-all-company"
                                                            data-company-id="{{ $company->id }}">
                                                            {{ count($companyAssignments) == $company->jobProfiles->count() ? __('educational::messages.unselect_all') : __('educational::messages.select_all') }}
                                                        </button>
                                                    </div>
                                                    <div class="row g-2 company-profiles-{{ $company->id }}">
                                                        @foreach ($company->jobProfiles as $profile)
                                                            @php $isChecked = in_array($profile->id, $companyAssignments); @endphp
                                                            <div class="col-md-4">
                                                                <div class="form-check card-radio p-0">
                                                                    <input class="form-check-input d-none" type="checkbox"
                                                                        name="company_assignments[{{ $company->id }}][]"
                                                                        value="{{ $profile->id }}"
                                                                        id="comp-{{ $company->id }}-prof-{{ $profile->id }}"
                                                                        {{ $isChecked ? 'checked' : '' }}>
                                                                    <label
                                                                        class="form-check-label d-flex align-items-center p-3 border rounded-3 cursor-pointer bg-white shadow-sm transition-all"
                                                                        for="comp-{{ $company->id }}-prof-{{ $profile->id }}">
                                                                        <div class="flex-grow-1">
                                                                            <span
                                                                                class="fs-13 fw-medium">{{ $profile->name }}</span>
                                                                            <div class="small text-muted mt-1">Code: <code
                                                                                    class="text-primary">{{ $profile->code }}</code>
                                                                            </div>
                                                                        </div>
                                                                        <div class="flex-shrink-0 ms-2">
                                                                            <div class="avatar-xs">
                                                                                <div class="avatar-title rounded-circle bg-soft-success text-success fs-18 checked-icon shadow-sm"
                                                                                    style="{{ $isChecked ? '' : 'display:none' }}">
                                                                                    <i class="ri-checkbox-circle-fill"></i>
                                                                                </div>
                                                                                <div class="avatar-title rounded-circle bg-light text-muted fs-18 unchecked-icon"
                                                                                    style="{{ $isChecked ? 'display:none' : '' }}">
                                                                                    <i
                                                                                        class="ri-checkbox-blank-circle-line"></i>
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
                                            <i class="ri-building-line fs-32 d-block mb-2"></i>
                                            {{ __('educational::messages.no_companies_found') }}
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12 py-4 hstack gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm fw-bold">
                            <i class="ri-save-line align-bottom me-1"></i> {{ __('educational::messages.save') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .cursor-pointer {
            cursor: pointer;
        }

        .transition-all {
            transition: all 0.2s ease-in-out;
        }

        .card-radio label:hover {
            border-color: var(--vz-primary) !important;
            transform: translateY(-2px);
        }

        .card-radio input:checked+label {
            border-color: var(--vz-success) !important;
            background-color: rgba(var(--vz-success-rgb), 0.03) !important;
            box-shadow: 0 5px 15px rgba(var(--vz-success-rgb), 0.1) !important;
        }

        .card-radio input:checked+label .checked-icon {
            display: flex !important;
        }

        .card-radio input:checked+label .unchecked-icon {
            display: none !important;
        }

        .accordion-button:not(.collapsed) {
            background-color: rgba(var(--vz-primary-rgb), 0.05);
            color: var(--vz-primary);
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Select All Logic
        document.querySelectorAll('.select-all-company').forEach(btn => {
            btn.addEventListener('click', function() {
                const companyId = this.dataset.companyId;
                const container = document.querySelector('.company-profiles-' + companyId);
                const checkboxes = container.querySelectorAll('input[type="checkbox"]');

                const allChecked = Array.from(checkboxes).every(cb => cb.checked);

                checkboxes.forEach(cb => {
                    cb.checked = !allChecked;
                    const label = cb.nextElementSibling;
                    if (label) {
                        const checkedIcon = label.querySelector('.checked-icon');
                        const uncheckedIcon = label.querySelector('.unchecked-icon');
                        if (checkedIcon) checkedIcon.style.display = !allChecked ? 'flex' : 'none';
                        if (uncheckedIcon) uncheckedIcon.style.display = !allChecked ? 'none' :
                            'flex';
                    }
                });

                this.textContent = allChecked ? "{{ __('educational::messages.select_all') }}" :
                    "{{ __('educational::messages.unselect_all') }}";
            });
        });

        // Session Types Icon Toggle Logic
        document.querySelectorAll('input[name="session_types[]"]').forEach(cb => {
            cb.addEventListener('change', function() {
                const label = this.nextElementSibling;
                if (label) {
                    const checkedIcon = label.querySelector('.checked-icon');
                    const uncheckedIcon = label.querySelector('.unchecked-icon');
                    if (checkedIcon) checkedIcon.style.display = this.checked ? 'flex' : 'none';
                    if (uncheckedIcon) uncheckedIcon.style.display = this.checked ? 'none' : 'flex';
                }
            });
        });
    </script>
@endpush
