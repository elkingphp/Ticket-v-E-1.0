@extends('core::layouts.master')
@section('title', __('educational::messages.add_track'))

@section('content')

    @include('modules.educational.shared.alerts')

    <div class="row justify-content-center mt-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-soft-primary border-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 avatar-sm me-3">
                            <div class="avatar-title bg-primary text-white rounded-circle fs-4">
                                <i class="ri-add-circle-line"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0 fw-bold">{{ __('educational::messages.add_track') }}</h5>
                            <p class="text-muted mb-0 small">إضافة تخصص أكاديمي جديد وتحديد فريق العمل المسؤول.</p>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('educational.tracks.index') }}"
                                class="btn btn-soft-secondary btn-sm rounded-pill px-3">
                                <i class="ri-arrow-left-line align-middle me-1"></i>
                                {{ __('educational::messages.back_to_list') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('educational.tracks.store') }}" method="POST">
                        @csrf

                        <div class="row g-4">
                            <div class="col-md-8">
                                <div class="form-group mb-0">
                                    <label class="form-label fw-bold">{{ __('educational::messages.track_name') }} <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i
                                                class="ri-route-line text-muted"></i></span>
                                        <input type="text" name="name"
                                            class="form-control border-start-0 py-2 @error('name') is-invalid @enderror"
                                            value="{{ old('name') }}" placeholder="مثال: هندسة البرمجيات" required>
                                    </div>
                                    @error('name')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label class="form-label fw-bold">{{ __('educational::messages.track_code') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i
                                                class="ri-barcode-line text-muted"></i></span>
                                        <input type="text" name="code"
                                            class="form-control border-start-0 py-2 @error('code') is-invalid @enderror"
                                            value="{{ old('code') }}" placeholder="مثال: ENG-001">
                                    </div>
                                    @error('code')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card bg-light border-0 shadow-none mb-0">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <h6 class="fw-bold mb-0 text-dark"><i
                                                    class="ri-team-line me-2 text-primary"></i> المسؤولين / الأطباء</h6>
                                        </div>
                                        <p class="text-muted small mb-3">اختر الأفراد المسؤولين عن متابعة هذا التخصص
                                            الأكاديمي.</p>

                                        <select name="responsibles[]" class="form-control" data-choices
                                            data-choices-removeItem multiple>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ in_array($user->id, old('responsibles', [])) ? 'selected' : '' }}>
                                                    {{ $user->full_name }} ({{ $user->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('responsibles')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mt-4 border-top pt-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="form-check form-switch form-switch-lg">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                            id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark ms-2"
                                            for="is_active">{{ __('educational::messages.active') }}</label>
                                    </div>

                                    <div class="hstack gap-2">
                                        <a href="{{ route('educational.tracks.index') }}"
                                            class="btn btn-soft-secondary px-4">{{ __('educational::messages.cancel') }}</a>
                                        <button type="submit" class="btn btn-primary px-5 shadow-sm fw-bold">
                                            <i class="ri-save-line align-bottom me-1"></i> حفظ التخصص
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
        .card-header.bg-soft-primary {
            background-color: rgba(64, 81, 137, 0.05);
        }

        .form-switch-lg .form-check-input {
            width: 3rem;
            height: 1.5rem;
            cursor: pointer;
        }
    </style>
@endpush
