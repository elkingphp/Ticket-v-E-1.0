@extends('core::layouts.master')
@section('title', __('educational::messages.add_job_profile'))

@section('content')

@include('modules.educational.shared.alerts')

<div class="row justify-content-center mt-4">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-soft-info border-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm me-3">
                        <div class="avatar-title bg-info text-white rounded-circle fs-4">
                            <i class="ri-add-line"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0 fw-bold">{{ __('educational::messages.add_job_profile') }}</h5>
                        <p class="text-muted mb-0 small">إضافة ملف وظيفي جديد وربطه بالتخصص المناسب.</p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('educational.job_profiles.index') }}" class="btn btn-soft-secondary btn-sm rounded-pill px-3">
                            <i class="ri-arrow-left-line align-middle me-1"></i> {{ __('educational::messages.back_to_list') }}
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-4">
                <form action="{{ route('educational.job_profiles.store') }}" method="POST">
                    @csrf
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.tracks') }} <span class="text-danger">*</span></label>
                                <select name="track_id" class="form-select py-2 @error('track_id') is-invalid @enderror" required>
                                    <option value="">{{ __('educational::messages.company.select_track') }}</option>
                                    @foreach($tracks as $track)
                                        <option value="{{ $track->id }}" {{ old('track_id') == $track->id ? 'selected' : '' }}>{{ $track->name }}</option>
                                    @endforeach
                                </select>
                                @error('track_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.status') }} <span class="text-danger">*</span></label>
                                <select name="status" class="form-select py-2 @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>{{ __('educational::messages.status_active') }}</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>{{ __('educational::messages.status_inactive') }}</option>
                                </select>
                                @error('status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.job_profile_name') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-briefcase-line text-muted"></i></span>
                                    <input type="text" name="name" class="form-control border-left-0 py-2 @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="مثال: مطور ويب متكامل" required>
                                </div>
                                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.job_profile_code') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-barcode-line text-muted"></i></span>
                                    <input type="text" name="code" class="form-control border-left-0 py-2 @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="مثال: JP-WEB">
                                </div>
                                @error('code') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card bg-light border-0 shadow-none mb-0">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3 text-dark"><i class="ri-team-line me-2 text-info"></i> المسؤولين عن هذا الملف</h6>
                                    <p class="text-muted small mb-3">حدد الأفراد الذين يمكنهم إسناد هذا الملف الوظيفي للمتدربين.</p>
                                    
                                    <select name="responsibles[]" class="form-control" data-choices data-choices-removeItem multiple>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ in_array($user->id, old('responsibles', [])) ? 'selected' : '' }}>
                                                {{ $user->full_name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('responsibles') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4 border-top pt-4">
                            <div class="hstack gap-2 justify-content-end">
                                <a href="{{ route('educational.job_profiles.index') }}" class="btn btn-soft-secondary px-4">{{ __('educational::messages.cancel') }}</a>
                                <button type="submit" class="btn btn-info px-5 shadow-sm fw-bold">
                                    <i class="ri-save-line align-bottom me-1"></i> حفظ الملف الوظيفي
                                </button>
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
    .choices__inner { border-radius: 8px !important; border: 1px solid #e9ebec !important; background-color: #fff !important; }
</style>
@endpush
