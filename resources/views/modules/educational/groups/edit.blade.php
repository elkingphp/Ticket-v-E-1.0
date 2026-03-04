@extends('core::layouts.master')
@section('title', __('educational::messages.edit_group'))

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
                        <h5 class="card-title mb-0 fw-bold">{{ __('educational::messages.edit_group') }}: <span class="text-dark">{{ $group->name }}</span></h5>
                        <p class="text-muted mb-0 small">تعديل بيانات المجموعة التدريبية.</p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('educational.groups.index') }}" class="btn btn-soft-secondary btn-sm rounded-pill px-3">
                            <i class="ri-arrow-left-line align-middle me-1"></i> {{ __('educational::messages.back_to_list') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('educational.groups.update', $group->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.group_name') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-group-line text-muted"></i></span>
                                    <input type="text" name="name" class="form-control py-2 @error('name') is-invalid @enderror" value="{{ old('name', $group->name) }}" required>
                                </div>
                                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.term') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-calendar-event-line text-muted"></i></span>
                                    <input type="text" name="term" class="form-control py-2 @error('term') is-invalid @enderror" value="{{ old('term', $group->term) }}">
                                </div>
                                @error('term') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.capacity') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-team-line text-muted"></i></span>
                                    <input type="number" name="capacity" class="form-control py-2 @error('capacity') is-invalid @enderror" value="{{ old('capacity', $group->capacity) }}" min="1" required>
                                </div>
                                @error('capacity') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.job_profile') }}</label>
                                <select name="job_profile_id" class="form-select py-2 @error('job_profile_id') is-invalid @enderror">
                                    <option value="">اختر التخصص / الملف الوظيفي</option>
                                    @foreach($tracks as $track)
                                        <optgroup label="{{ $track->name }}">
                                            @foreach($track->jobProfiles as $jp)
                                                <option value="{{ $jp->id }}" {{ old('job_profile_id', $group->job_profile_id) == $jp->id ? 'selected' : '' }}>{{ $jp->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('job_profile_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.program') }} <span class="text-danger">*</span></label>
                                <select name="program_id" class="form-select py-2 @error('program_id') is-invalid @enderror" required>
                                    <option value="">اختر البرنامج التدريبي</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}" {{ old('program_id', $group->program_id) == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                                    @endforeach
                                </select>
                                @error('program_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.status') }} <span class="text-danger">*</span></label>
                                <select name="status" id="group_status" class="form-select py-2 @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $group->status) == 'active' ? 'selected' : '' }}>{{ __('educational::messages.status_active') }}</option>
                                    <option value="completed" {{ old('status', $group->status) == 'completed' ? 'selected' : '' }}>{{ __('educational::messages.status_completed') ?? 'مكتملة' }}</option>
                                    <option value="cancelled" {{ old('status', $group->status) == 'cancelled' ? 'selected' : '' }}>{{ __('educational::messages.status_cancelled') }}</option>
                                    <option value="transferred" {{ old('status', $group->status) == 'transferred' ? 'selected' : '' }}>{{ __('educational::messages.status_transferred') }}</option>
                                </select>
                                @error('status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-12" id="cancellation_reason_container" style="display: none;">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.remarks') }} <span class="text-danger">*</span></label>
                                <textarea name="cancellation_reason" class="form-control py-2 @error('cancellation_reason') is-invalid @enderror" rows="3" placeholder="توضيح سبب الإلغاء...">{{ old('cancellation_reason', $group->cancellation_reason) }}</textarea>
                                @error('cancellation_reason') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-12" id="transferred_group_container" style="display: none;">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">{{ __('educational::messages.transferred_to') }} <span class="text-danger">*</span></label>
                                <select name="transferred_to_group_id" class="form-select py-2 @error('transferred_to_group_id') is-invalid @enderror">
                                    <option value="">اختر المجموعة...</option>
                                    @foreach($allGroups as $g)
                                        <option value="{{ $g->id }}" {{ old('transferred_to_group_id', $group->transferred_to_group_id) == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                                    @endforeach
                                </select>
                                @error('transferred_to_group_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-12 mt-4 border-top pt-4">
                            <div class="hstack gap-2 justify-content-end">
                                <a href="{{ route('educational.groups.index') }}" class="btn btn-soft-secondary px-4">{{ __('educational::messages.cancel') }}</a>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelect = document.getElementById('group_status');
        const cancelContainer = document.getElementById('cancellation_reason_container');
        const transferContainer = document.getElementById('transferred_group_container');

        function toggleFields() {
            const status = statusSelect.value;
            cancelContainer.style.display = (status === 'cancelled') ? 'block' : 'none';
            transferContainer.style.display = (status === 'transferred') ? 'block' : 'none';
            cancelContainer.querySelector('textarea').required = (status === 'cancelled');
            transferContainer.querySelector('select').required = (status === 'transferred');
        }

        statusSelect.addEventListener('change', toggleFields);
        toggleFields();
    });
</script>
@endpush
