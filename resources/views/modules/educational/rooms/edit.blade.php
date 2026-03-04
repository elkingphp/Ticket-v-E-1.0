@extends('core::layouts.master')
@section('title', __('educational::messages.edit_room'))

@section('content')
@include('modules.educational.shared.alerts')

<div class="row justify-content-center mt-4">
    <div class="col-xl-10">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-soft-warning border-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm me-3">
                        <div class="avatar-title bg-warning text-white rounded-circle fs-4">
                            <i class="ri-pencil-line"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0 fw-bold">{{ __('educational::messages.edit_room') }}: <span class="text-dark">{{ $room->name }}</span></h5>
                        <p class="text-muted mb-0 small">تعديل بيانات القاعة ومعطياتها.</p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('educational.rooms.index') }}" class="btn btn-soft-secondary btn-sm rounded-pill px-3">
                            <i class="ri-arrow-left-line align-middle me-1"></i> {{ __('educational::messages.back_to_list') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('educational.rooms.update', $room->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.floor') }} <span class="text-danger">*</span></label>
                                <select name="floor_id" class="form-select py-2 @error('floor_id') is-invalid @enderror" required>
                                    <option value="">{{ __('educational::messages.select_floor') ?? '-- اختر الدور --' }}</option>
                                    @foreach($buildings as $building)
                                        <optgroup label="{{ $building->name }} ({{ $building->campus->name ?? '' }})">
                                            @foreach($building->floors as $floor)
                                                <option value="{{ $floor->id }}" {{ (old('floor_id') ?? $room->floor_id) == $floor->id ? 'selected' : '' }}>{{ __('educational::messages.floor') }} {{ $floor->floor_number }} {{ $floor->name ? '('.$floor->name.')' : '' }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('floor_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.room_name') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-door-open-line text-muted"></i></span>
                                    <input type="text" name="name" class="form-control py-2 @error('name') is-invalid @enderror" value="{{ old('name', $room->name) }}" required>
                                </div>
                                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.room_code') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-qr-code-line text-muted"></i></span>
                                    <input type="text" name="code" class="form-control py-2 @error('code') is-invalid @enderror" value="{{ old('code', $room->code) }}" required>
                                </div>
                                @error('code') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.room_type') }} <span class="text-danger">*</span></label>
                                <select name="room_type" class="form-select py-2 @error('room_type') is-invalid @enderror" required>
                                    <option value="">-- اختر نوع القاعة --</option>
                                    @foreach($room_types as $type)
                                        <option value="{{ $type->slug }}" {{ (old('room_type') ?? $room->room_type) == $type->slug ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('room_type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.room_capacity') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-group-line text-muted"></i></span>
                                    <input type="number" name="capacity" class="form-control py-2 @error('capacity') is-invalid @enderror" value="{{ old('capacity', $room->capacity) }}" required min="1">
                                </div>
                                @error('capacity') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.status') }} <span class="text-danger">*</span></label>
                                <select name="room_status" class="form-select py-2 @error('room_status') is-invalid @enderror" required>
                                    <option value="active" {{ old('room_status', $room->room_status) == 'active' ? 'selected' : '' }}>{{ __('educational::messages.status_active') }}</option>
                                    <option value="maintenance" {{ old('room_status', $room->room_status) == 'maintenance' ? 'selected' : '' }}>{{ __('educational::messages.maintenance') ?? 'صيانة' }}</option>
                                    <option value="disabled" {{ old('room_status', $room->room_status) == 'disabled' ? 'selected' : '' }}>{{ __('educational::messages.disabled') ?? 'معطل' }}</option>
                                </select>
                                @error('room_status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-12 mt-4 border-top pt-4">
                            <div class="hstack gap-2 justify-content-end">
                                <a href="{{ route('educational.rooms.index') }}" class="btn btn-soft-secondary px-4">{{ __('educational::messages.cancel') }}</a>
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
