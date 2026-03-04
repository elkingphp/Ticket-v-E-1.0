@extends('core::layouts.master')
@section('title', __('educational::messages.add_program'))

@section('content')

@include('modules.educational.shared.alerts')

<div class="row justify-content-center mt-4">
    <div class="col-xl-9">
        <form action="{{ route('educational.programs.store') }}" method="POST">
            @csrf
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-soft-primary border-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 avatar-sm me-3">
                            <div class="avatar-title bg-primary text-white rounded-circle fs-4">
                                <i class="ri-rocket-line"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0 fw-bold">{{ __('educational::messages.add_program') }}</h5>
                            <p class="text-muted mb-0 small">إنشاء برنامج تدريبي جديد وتحديد الفروع المتاحة له.</p>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('educational.programs.index') }}" class="btn btn-soft-secondary btn-sm rounded-pill px-3">
                                <i class="ri-arrow-left-line align-middle me-1"></i> {{ __('educational::messages.back_to_list') }}
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Basic Info Section -->
                        <div class="col-12">
                            <div class="d-flex align-items-center mb-1">
                                <i class="ri-information-line fs-18 text-primary me-2"></i>
                                <h6 class="mb-0 fw-bold border-bottom border-primary border-2 pb-1">المعلومات الأساسية</h6>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="form-label fw-bold">{{ __('educational::messages.program_name') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-graduation-cap-line text-muted"></i></span>
                                    <input type="text" name="name" class="form-control border-left-0 py-2 @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="مثال: برنامج الأمن السيبراني المتقدم" required>
                                </div>
                                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label fw-bold">{{ __('educational::messages.program_code') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-barcode-line text-muted"></i></span>
                                    <input type="text" name="code" class="form-control border-left-0 py-2 @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="مثال: PRG-CYBER" required>
                                </div>
                                @error('code') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label fw-bold text-muted small mb-1">الفروع والمقار المتاحة للبرنامج</label>
                                <select name="campuses[]" class="form-control" data-choices data-choices-removeItem multiple>
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}" {{ in_array($campus->id, old('campuses', [])) ? 'selected' : '' }}>{{ $campus->name }}</option>
                                    @endforeach
                                </select>
                                @error('campuses') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label fw-bold">{{ __('educational::messages.status') }} <span class="text-danger">*</span></label>
                                <select name="status" class="form-select py-2 @error('status') is-invalid @enderror" required>
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>{{ __('educational::messages.status_draft') }}</option>
                                    <option value="published" {{ old('status', 'published') == 'published' ? 'selected' : '' }}>{{ __('educational::messages.status_published') }}</option>
                                    <option value="running" {{ old('status') == 'running' ? 'selected' : '' }}>{{ __('educational::messages.status_running') }}</option>
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>{{ __('educational::messages.status_completed') }}</option>
                                </select>
                                @error('status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Schedule Section -->
                        <div class="col-12 mt-4 pt-2">
                            <div class="d-flex align-items-center mb-1">
                                <i class="ri-calendar-todo-line fs-18 text-primary me-2"></i>
                                <h6 class="mb-0 fw-bold border-bottom border-primary border-2 pb-1">الفترة الزمنية</h6>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label fw-bold">{{ __('educational::messages.starts_at') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-calendar-event-line text-muted"></i></span>
                                    <input type="date" name="starts_at" class="form-control border-left-0 py-2 @error('starts_at') is-invalid @enderror" value="{{ old('starts_at') }}">
                                </div>
                                @error('starts_at') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label fw-bold">{{ __('educational::messages.ends_at') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-calendar-check-line text-muted"></i></span>
                                    <input type="date" name="ends_at" class="form-control border-left-0 py-2 @error('ends_at') is-invalid @enderror" value="{{ old('ends_at') }}">
                                </div>
                                @error('ends_at') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-light p-4">
                    <div class="hstack gap-2 justify-content-end">
                        <a href="{{ route('educational.programs.index') }}" class="btn btn-soft-secondary px-4">{{ __('educational::messages.cancel') }}</a>
                        <button type="submit" class="btn btn-primary px-5 shadow-sm fw-bold">
                            <i class="ri-save-line align-bottom me-1"></i> حفظ البرنامج التدريبي
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
    .choices__inner { border-radius: 8px !important; border: 1px solid #e9ebec !important; background-color: #fff !important; }
</style>
@endpush
