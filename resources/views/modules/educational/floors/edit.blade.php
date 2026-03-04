@extends('core::layouts.master')
@section('title', __('educational::messages.edit_floor'))

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
                        <h5 class="card-title mb-0 fw-bold">{{ __('educational::messages.edit_floor') }}: <span class="text-dark">{{ __('educational::messages.floor') }} {{ $floor->floor_number }}</span></h5>
                        <p class="text-muted mb-0 small">تعديل بيانات الدور / الطابق.</p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('educational.floors.index') }}" class="btn btn-soft-secondary btn-sm rounded-pill px-3">
                            <i class="ri-arrow-left-line align-middle me-1"></i> {{ __('educational::messages.back_to_list') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('educational.floors.update', $floor->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.building') }} <span class="text-danger">*</span></label>
                                <select name="building_id" class="form-select py-2 @error('building_id') is-invalid @enderror" required>
                                    @foreach($buildings as $building)
                                        <option value="{{ $building->id }}" {{ (old('building_id') ?? $floor->building_id) == $building->id ? 'selected' : '' }}>{{ $building->name }} ({{ $building->campus->name ?? '' }})</option>
                                    @endforeach
                                </select>
                                @error('building_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.floor_number') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-stack-line text-muted"></i></span>
                                    <input type="text" name="floor_number" class="form-control py-2 @error('floor_number') is-invalid @enderror" value="{{ old('floor_number', $floor->floor_number) }}" required>
                                </div>
                                @error('floor_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.note') }} <small class="text-muted fw-normal">(اختياري)</small></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-text text-muted"></i></span>
                                    <input type="text" name="name" class="form-control py-2 @error('name') is-invalid @enderror" value="{{ old('name', $floor->name) }}">
                                </div>
                                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.status') }} <span class="text-danger">*</span></label>
                                <select name="status" class="form-select py-2 @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $floor->status) == 'active' ? 'selected' : '' }}>{{ __('educational::messages.status_active') }}</option>
                                    <option value="inactive" {{ old('status', $floor->status) == 'inactive' ? 'selected' : '' }}>{{ __('educational::messages.status_inactive') }}</option>
                                </select>
                                @error('status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-12 mt-4 border-top pt-4">
                            <div class="hstack gap-2 justify-content-end">
                                <a href="{{ route('educational.floors.index') }}" class="btn btn-soft-secondary px-4">{{ __('educational::messages.cancel') }}</a>
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
