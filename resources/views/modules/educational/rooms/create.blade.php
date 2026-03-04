@extends('core::layouts.master')
@section('title', __('educational::messages.add_room'))

@section('content')
@include('modules.educational.shared.alerts')

<div class="row justify-content-center mt-4">
    <div class="col-xl-10">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-soft-info border-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm me-3">
                        <div class="avatar-title bg-info text-white rounded-circle fs-4">
                            <i class="ri-door-open-line"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0 fw-bold">{{ __('educational::messages.add_room') }}</h5>
                        <p class="text-muted mb-0 small">إضافة قاعة أو معمل جديد وتحديد طاقته الاستيعابية ونوعه.</p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('educational.rooms.index') }}" class="btn btn-soft-secondary btn-sm rounded-pill px-3">
                            <i class="ri-arrow-left-line align-middle me-1"></i> {{ __('educational::messages.back_to_list') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('educational.rooms.store') }}" method="POST">
                    @csrf

                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.floor') }} <span class="text-danger">*</span></label>
                                <select name="floor_id" class="form-select py-2 @error('floor_id') is-invalid @enderror" required>
                                    <option value="">{{ __('educational::messages.select_floor') ?? '-- اختر الدور --' }}</option>
                                    @foreach($buildings as $building)
                                        <optgroup label="{{ $building->name }} ({{ $building->campus->name ?? '' }})">
                                            @foreach($building->floors as $floor)
                                                <option value="{{ $floor->id }}" {{ old('floor_id') == $floor->id ? 'selected' : '' }}>{{ __('educational::messages.floor') }} {{ $floor->floor_number }} {{ $floor->name ? '('.$floor->name.')' : '' }}</option>
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
                                    <input type="text" name="name" class="form-control py-2 @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="مثال: معمل الحاسب الآلي 101" required>
                                </div>
                                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.room_code') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-qr-code-line text-muted"></i></span>
                                    <input type="text" name="code" class="form-control py-2 @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="مثال: L101" required>
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
                                        <option value="{{ $type->slug }}" {{ old('room_type') == $type->slug ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @if($room_types->isEmpty())
                                    <div class="text-warning small mt-1">
                                        <i class="ri-alert-line me-1"></i> لا توجد أنواع قاعات.
                                        <a href="{{ route('educational.room_types.create') }}" target="_blank">أضف نوعاً الآن</a>
                                    </div>
                                @endif
                                @error('room_type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.room_capacity') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-group-line text-muted"></i></span>
                                    <input type="number" name="capacity" class="form-control py-2 @error('capacity') is-invalid @enderror" value="{{ old('capacity') }}" placeholder="مثال: 30" required min="1">
                                </div>
                                @error('capacity') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.status') }} <span class="text-danger">*</span></label>
                                <select name="room_status" class="form-select py-2 @error('room_status') is-invalid @enderror" required>
                                    <option value="active" {{ old('room_status', 'active') == 'active' ? 'selected' : '' }}>{{ __('educational::messages.status_active') }}</option>
                                    <option value="maintenance" {{ old('room_status') == 'maintenance' ? 'selected' : '' }}>{{ __('educational::messages.maintenance') ?? 'صيانة' }}</option>
                                    <option value="disabled" {{ old('room_status') == 'disabled' ? 'selected' : '' }}>{{ __('educational::messages.disabled') ?? 'معطل' }}</option>
                                </select>
                                @error('room_status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-12 mt-4 border-top pt-4">
                            <div class="hstack gap-2 justify-content-end">
                                <a href="{{ route('educational.rooms.index') }}" class="btn btn-soft-secondary px-4">{{ __('educational::messages.cancel') }}</a>
                                <button type="submit" class="btn btn-info px-5 shadow-sm fw-bold">
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
