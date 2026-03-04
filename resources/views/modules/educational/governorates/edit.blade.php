@extends('core::layouts.master')
@section('title', __('educational::messages.edit_governorate'))

@section('content')
@include('modules.educational.shared.alerts')

<div class="row justify-content-center mt-4">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-soft-warning border-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm me-3">
                        <div class="avatar-title bg-warning text-white rounded-circle fs-4">
                            <i class="ri-pencil-line"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0 fw-bold">{{ __('educational::messages.edit_governorate') }}: <span class="text-dark">{{ $governorate->name_ar }}</span></h5>
                        <p class="text-muted mb-0 small">تعديل بيانات المحافظة.</p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('educational.governorates.index') }}" class="btn btn-soft-secondary btn-sm rounded-pill px-3">
                            <i class="ri-arrow-left-line align-middle me-1"></i> {{ __('educational::messages.back_to_list') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('educational.governorates.update', $governorate->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.governorate_name_ar') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-map-pin-line text-muted"></i></span>
                                    <input type="text" name="name_ar" class="form-control py-2 @error('name_ar') is-invalid @enderror" value="{{ old('name_ar', $governorate->name_ar) }}" required>
                                </div>
                                @error('name_ar') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.governorate_name_en') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-earth-line text-muted"></i></span>
                                    <input type="text" name="name_en" class="form-control py-2 @error('name_en') is-invalid @enderror" value="{{ old('name_en', $governorate->name_en) }}">
                                </div>
                                @error('name_en') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.status') }} <span class="text-danger">*</span></label>
                                <select name="status" class="form-select py-2 @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $governorate->status) == 'active' ? 'selected' : '' }}>{{ __('educational::messages.status_active') }}</option>
                                    <option value="inactive" {{ old('status', $governorate->status) == 'inactive' ? 'selected' : '' }}>{{ __('educational::messages.status_inactive') }}</option>
                                </select>
                                @error('status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-12 mt-4 border-top pt-4">
                            <div class="hstack gap-2 justify-content-end">
                                <a href="{{ route('educational.governorates.index') }}" class="btn btn-soft-secondary px-4">{{ __('educational::messages.cancel') }}</a>
                                <button type="submit" class="btn btn-warning px-5 shadow-sm fw-bold">
                                    <i class="ri-save-line align-bottom me-1"></i> {{ __('educational::messages.save') }}
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
