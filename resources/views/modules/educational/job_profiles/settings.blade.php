@extends('core::layouts.master')
@section('title', __('educational::messages.job_profile_settings'))

@section('content')

@include('modules.educational.shared.alerts')

<div class="row justify-content-center mt-4">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-soft-primary border-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm me-3">
                        <div class="avatar-title bg-primary text-white rounded-circle fs-4">
                            <i class="ri-settings-4-line"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0 fw-bold">{{ __('educational::messages.job_profile_settings') }}</h5>
                        <p class="text-muted mb-0 small">تخصيص الخيارات الإدارية وطريقة عرض قائمة الملفات الوظيفية.</p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('educational.job_profiles.index') }}" class="btn btn-soft-secondary btn-sm rounded-pill px-3">
                            <i class="ri-arrow-left-line align-middle me-1"></i> {{ __('educational::messages.back_to_list') }}
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-4">
                <form action="{{ route('educational.job_profiles.saveSettings') }}" method="POST">
                    @csrf
                    
                    <div class="row g-4">
                        {{-- ─── ROLES SETTINGS ─── --}}
                        <div class="col-12">
                            <h5 class="fw-bold mb-3 d-flex align-items-center">
                                <i class="ri-shield-user-line text-primary me-2 fs-20"></i> 
                                الأدوار والمسؤولين
                            </h5>
                            <p class="text-muted mb-4 small">
                                <i class="ri-information-line me-1"></i> 
                                حدد الأدوار/المجموعات التي يمكن لموظفيها تولي مسؤولية الملفات الوظيفية. هذا سيقلل قائمة المستخدمين المتاحين في شاشات الإدخال والتحرير.
                            </p>
                            
                            <div class="row g-3">
                                @foreach($roles as $role)
                                    <div class="col-xl-4 col-md-6">
                                        <label class="card border border-2 shadow-none mb-0 cursor-pointer role-card transition-all h-100 w-100 {{ (is_array($selectedRoles) && in_array($role->id, $selectedRoles)) ? 'border-primary bg-soft-primary' : 'border-light bg-light' }}" for="role_{{ $role->id }}">
                                            <div class="card-body p-3 d-flex align-items-center">
                                                <div class="form-check form-check-primary me-3 mb-0">
                                                    <input class="form-check-input role-checkbox fs-5" type="checkbox" name="roles[]" id="role_{{ $role->id }}" value="{{ $role->id }}" {{ (is_array($selectedRoles) && in_array($role->id, $selectedRoles)) ? 'checked' : '' }}>
                                                </div>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <h6 class="mb-0 fw-bold text-dark">{{ $role->name }}</h6>
                                                    <span class="text-muted small">تحديد كمسؤول محتمل</span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- ─── PAGINATION SETTINGS ─── --}}
                        <div class="col-12 mt-4 border-top pt-4">
                            <h5 class="fw-bold mb-3 d-flex align-items-center">
                                <i class="ri-layout-grid-line text-info me-2 fs-20"></i> 
                                إعدادات العرض والقوائم
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold text-muted small">عدد العناصر لكل صفحة</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-light"><i class="ri-numbers-line"></i></span>
                                            <select name="per_page" class="form-select border-light bg-light">
                                                @foreach([8, 12, 16, 24, 48, 100] as $count)
                                                    <option value="{{ $count }}" {{ $perPage == $count ? 'selected' : '' }}>{{ $count }} ملف لكل صفحة</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <p class="text-muted small mt-2">يتحكم هذا الرقم في عدد البطاقات التي تظهر في شاشة استعراض الملفات الوظيفية.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mt-4 border-top pt-4">
                            <div class="d-flex justify-content-end">
                                <div class="hstack gap-2">
                                    <a href="{{ route('educational.job_profiles.index') }}" class="btn btn-soft-secondary px-4">{{ __('educational::messages.cancel') }}</a>
                                    <button type="submit" class="btn btn-primary px-5 shadow-sm fw-bold">
                                        <i class="ri-save-line align-bottom me-1"></i> {{ __('educational::messages.save') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .cursor-pointer { cursor: pointer; }
    .transition-all { transition: all 0.2s ease-in-out; }
    .role-card:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.05) !important; border-color: #ced4da !important; }
    .role-card.border-primary { border-color: var(--vz-primary) !important; box-shadow: 0 5px 10px rgba(var(--vz-primary-rgb), 0.1) !important; }
    .role-card.bg-soft-primary { background-color: rgba(var(--vz-primary-rgb), 0.05) !important; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('.role-checkbox');
        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const card = this.closest('.role-card');
                if (this.checked) {
                    card.classList.remove('border-light', 'bg-light');
                    card.classList.add('border-primary', 'bg-soft-primary');
                } else {
                    card.classList.remove('border-primary', 'bg-soft-primary');
                    card.classList.add('border-light', 'bg-light');
                }
            });
        });
    });
</script>
@endpush
