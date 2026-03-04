@extends('core::layouts.master')
@section('title', __('educational::messages.edit_schedule'))

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
                            <h5 class="card-title mb-0 fw-bold">
                                {{ __('educational::messages.edit_schedule') ?? 'تعديل جدول تدريبي' }}: <span
                                    class="text-dark">{{ $template->name ?? '#' . $template->id }}</span></h5>
                            <p class="text-muted mb-0 small">تعديل توقيتات وبيانات الجدول المتكرر.</p>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('educational.schedules.index') }}"
                                class="btn btn-soft-secondary btn-sm rounded-pill px-3">
                                <i class="ri-arrow-left-line align-middle me-1"></i>
                                {{ __('educational::messages.back_to_list') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('educational.schedules.update', $template->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            {{-- Template Name --}}
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label
                                        class="form-label fw-bold">{{ __('educational::messages.template_name') ?? 'اسم النموذج' }}
                                        <small class="text-muted fw-normal">(اختياري)</small></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i
                                                class="ri-edit-2-line text-muted"></i></span>
                                        <input type="text" name="name"
                                            class="form-control py-2 @error('name') is-invalid @enderror"
                                            value="{{ old('name', $template->name) }}"
                                            placeholder="{{ __('educational::messages.template_name_placeholder') ?? 'مثال: جدول الفترة الصباحية' }}">
                                    </div>
                                    @error('name')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label
                                        class="form-label fw-bold">{{ __('educational::messages.program') ?? 'البرنامج' }}
                                        <span class="text-danger">*</span></label>
                                    <select name="program_id" id="program_id"
                                        class="form-select py-2 @error('program_id') is-invalid @enderror" required
                                        onchange="loadGroups(this.value)">
                                        <option value="">
                                            {{ __('educational::messages.select_program') ?? 'اختر البرنامج التدريبي' }}
                                        </option>
                                        @foreach ($programs as $program)
                                            <option value="{{ $program->id }}"
                                                {{ old('program_id', $template->program_id) == $program->id ? 'selected' : '' }}>
                                                {{ $program->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('program_id')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label fw-bold">{{ __('educational::messages.group') ?? 'المجموعة' }}
                                        <span class="text-danger">*</span></label>
                                    <select name="group_id" id="group_id"
                                        class="form-select py-2 @error('group_id') is-invalid @enderror" required>
                                        <option value="">
                                            {{ __('educational::messages.select_group') ?? 'اختر المجموعة' }}</option>
                                        @foreach ($groups as $group)
                                            <option value="{{ $group->id }}"
                                                {{ old('group_id', $template->group_id) == $group->id ? 'selected' : '' }}>
                                                {{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('group_id')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- ─── Instructor & Session ──────────────────── --}}
                            <div class="col-12">
                                <div class="card bg-light border-0 shadow-none mb-0">
                                    <div class="card-body p-3">
                                        <h6 class="fw-bold mb-3 text-dark"><i
                                                class="ri-user-star-line me-2 text-warning"></i> بيانات المُدرب والدرس</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label
                                                    class="form-label fw-bold">{{ __('educational::messages.instructor') ?? 'المُدرب' }}
                                                    <span class="text-danger">*</span></label>
                                                <select name="instructor_profile_id"
                                                    class="form-select py-2 @error('instructor_profile_id') is-invalid @enderror"
                                                    required>
                                                    <option value="">
                                                        {{ __('educational::messages.select_instructor') ?? 'اختر المدرب' }}
                                                    </option>
                                                    @foreach ($instructors as $instructor)
                                                        <option value="{{ $instructor->id }}"
                                                            {{ old('instructor_profile_id', $template->instructor_profile_id) == $instructor->id ? 'selected' : '' }}>
                                                            {{ $instructor->user->full_name ?? 'ID: ' . $instructor->id }}
                                                            {{ $instructor->arabic_name ? '- ' . $instructor->arabic_name : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('instructor_profile_id')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label
                                                    class="form-label fw-bold">{{ __('educational::messages.session_type') ?? 'نوع الجلسة' }}
                                                    <span class="text-danger">*</span></label>
                                                <select name="session_type_id"
                                                    class="form-select py-2 @error('session_type_id') is-invalid @enderror"
                                                    required>
                                                    <option value="">
                                                        {{ __('educational::messages.select_session_type') ?? 'اختر نوع الجلسة' }}
                                                    </option>
                                                    @foreach ($sessionTypes as $type)
                                                        <option value="{{ $type->id }}"
                                                            {{ old('session_type_id', $template->session_type_id) == $type->id ? 'selected' : '' }}>
                                                            {{ $type->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('session_type_id')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label
                                                    class="form-label fw-bold">{{ __('educational::messages.subject') ?? 'المادة / الموضوع' }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white border-end-0"><i
                                                            class="ri-book-open-line text-muted"></i></span>
                                                    <input type="text" name="subject"
                                                        class="form-control py-2 @error('subject') is-invalid @enderror"
                                                        value="{{ old('subject', $template->subject) }}"
                                                        placeholder="{{ __('educational::messages.subject_placeholder') ?? 'اسم المادة' }}">
                                                </div>
                                                @error('subject')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label
                                                    class="form-label fw-bold">{{ __('educational::messages.room') ?? 'القاعة / المعمل' }}
                                                    <span class="text-danger">*</span></label>
                                                <select name="room_id"
                                                    class="form-select py-2 @error('room_id') is-invalid @enderror"
                                                    required>
                                                    <option value="">
                                                        {{ __('educational::messages.select_room') ?? 'اختر القاعة' }}
                                                    </option>
                                                    @foreach ($rooms as $room)
                                                        <option value="{{ $room->id }}"
                                                            {{ old('room_id', $template->room_id) == $room->id ? 'selected' : '' }}>
                                                            {{ $room->name }}
                                                            ({{ $room->floor->building->campus->name ?? '' }})</option>
                                                    @endforeach
                                                </select>
                                                @error('room_id')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ─── Timing & Recurrence ──────────────────── --}}
                            <div class="col-12">
                                <div class="card bg-light border-0 shadow-none mb-0">
                                    <div class="card-body p-3">
                                        <h6 class="fw-bold mb-3 text-dark"><i class="ri-time-line me-2 text-warning"></i>
                                            توقيت ومعدل التكرار</h6>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label
                                                    class="form-label fw-bold">{{ __('educational::messages.recurrence_type') ?? 'التكرار' }}
                                                    <span class="text-danger">*</span></label>
                                                <select name="recurrence_type"
                                                    class="form-select py-2 @error('recurrence_type') is-invalid @enderror"
                                                    required>
                                                    <option value="weekly"
                                                        {{ old('recurrence_type', $template->recurrence_type) == 'weekly' ? 'selected' : '' }}>
                                                        {{ __('educational::messages.weekly') ?? 'أسبوعيا' }}</option>
                                                    <option value="biweekly_even"
                                                        {{ old('recurrence_type', $template->recurrence_type) == 'biweekly_even' ? 'selected' : '' }}>
                                                        {{ __('educational::messages.biweekly_even') ?? 'كل أسبوعين (زوجي)' }}
                                                    </option>
                                                    <option value="biweekly_odd"
                                                        {{ old('recurrence_type', $template->recurrence_type) == 'biweekly_odd' ? 'selected' : '' }}>
                                                        {{ __('educational::messages.biweekly_odd') ?? 'كل أسبوعين (فردي)' }}
                                                    </option>
                                                </select>
                                                @error('recurrence_type')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-4">
                                                <label
                                                    class="form-label fw-bold">{{ __('educational::messages.day_of_week') ?? 'اليوم' }}
                                                    <span class="text-danger">*</span></label>
                                                <select name="day_of_week"
                                                    class="form-select py-2 @error('day_of_week') is-invalid @enderror"
                                                    required>
                                                    <option value="0"
                                                        {{ old('day_of_week', $template->day_of_week) == '0' ? 'selected' : '' }}>
                                                        {{ __('educational::messages.sunday') ?? 'الأحد' }}</option>
                                                    <option value="1"
                                                        {{ old('day_of_week', $template->day_of_week) == '1' ? 'selected' : '' }}>
                                                        {{ __('educational::messages.monday') ?? 'الإثنين' }}</option>
                                                    <option value="2"
                                                        {{ old('day_of_week', $template->day_of_week) == '2' ? 'selected' : '' }}>
                                                        {{ __('educational::messages.tuesday') ?? 'الثلاثاء' }}</option>
                                                    <option value="3"
                                                        {{ old('day_of_week', $template->day_of_week) == '3' ? 'selected' : '' }}>
                                                        {{ __('educational::messages.wednesday') ?? 'الأربعاء' }}</option>
                                                    <option value="4"
                                                        {{ old('day_of_week', $template->day_of_week) == '4' ? 'selected' : '' }}>
                                                        {{ __('educational::messages.thursday') ?? 'الخميس' }}</option>
                                                    <option value="5"
                                                        {{ old('day_of_week', $template->day_of_week) == '5' ? 'selected' : '' }}>
                                                        {{ __('educational::messages.friday') ?? 'الجمعة' }}</option>
                                                    <option value="6"
                                                        {{ old('day_of_week', $template->day_of_week) == '6' ? 'selected' : '' }}>
                                                        {{ __('educational::messages.saturday') ?? 'السبت' }}</option>
                                                </select>
                                                @error('day_of_week')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">من - إلى <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="time" name="start_time"
                                                        class="form-control py-2 @error('start_time') is-invalid @enderror"
                                                        value="{{ old('start_time', substr($template->start_time, 0, 5)) }}"
                                                        required>
                                                    <span class="input-group-text bg-white"><i
                                                            class="ri-arrow-right-line text-muted"></i></span>
                                                    <input type="time" name="end_time"
                                                        class="form-control py-2 @error('end_time') is-invalid @enderror"
                                                        value="{{ old('end_time', substr($template->end_time, 0, 5)) }}"
                                                        required>
                                                </div>
                                                @error('start_time')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                                @error('end_time')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label
                                                    class="form-label fw-bold">{{ __('educational::messages.effective_from') ?? 'سارِ من تاريخ' }}
                                                    <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white border-end-0"><i
                                                            class="ri-calendar-event-fill text-muted"></i></span>
                                                    <input type="date" name="effective_from"
                                                        class="form-control py-2 @error('effective_from') is-invalid @enderror"
                                                        value="{{ old('effective_from', $template->effective_from ? \Carbon\Carbon::parse($template->effective_from)->format('Y-m-d') : '') }}"
                                                        required>
                                                </div>
                                                @error('effective_from')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label
                                                    class="form-label fw-bold">{{ __('educational::messages.effective_until') ?? 'سارِ حتى تاريخ' }}
                                                    <small class="text-muted fw-normal">(اختياري)</small></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white border-end-0"><i
                                                            class="ri-calendar-event-line text-muted"></i></span>
                                                    <input type="date" name="effective_until"
                                                        class="form-control py-2 @error('effective_until') is-invalid @enderror"
                                                        value="{{ old('effective_until', $template->effective_until ? \Carbon\Carbon::parse($template->effective_until)->format('Y-m-d') : '') }}">
                                                </div>
                                                @error('effective_until')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ─── Auto Evaluation ──────────────────────── --}}
                            <div class="col-12">
                                <div class="card bg-light border-0 shadow-none mb-0">
                                    <div class="card-body p-3">
                                        <h6 class="fw-bold mb-1 text-dark"><i
                                                class="ri-survey-line me-2 text-warning"></i> التقييم التلقائي <small
                                                class="text-muted fw-normal">(اختياري)</small></h6>
                                        <p class="text-muted small mb-3"><i class="ri-information-line me-1"></i> سيتم
                                            تعيين هذا النموذج تلقائياً لكل محاضرة يتم إنشاؤها من هذا الجدول مستقبلاً. لن
                                            تتأثر المحاضرات المنشأة مسبقاً.</p>
                                        <select name="evaluation_form_id"
                                            class="form-select py-2 @error('evaluation_form_id') is-invalid @enderror">
                                            <option value="">— بدون تقييم تلقائي —</option>
                                            @foreach ($evaluationForms as $form)
                                                <option value="{{ $form->id }}"
                                                    {{ old('evaluation_form_id', $template->evaluation_form_id) == $form->id ? 'selected' : '' }}>
                                                    {{ $form->title }}
                                                    ({{ $form->questions_count ?? $form->questions()->count() }} سؤال)
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('evaluation_form_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                                    <div class="form-check form-switch form-switch-lg mb-0">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input ms-2" type="checkbox" role="switch"
                                            name="is_active" id="is_active" value="1"
                                            {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold"
                                            for="is_active">{{ __('educational::messages.status_active') ?? 'الجدول نشط ويعمل به' }}</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 border-top pt-4">
                                <div class="hstack gap-2 justify-content-end">
                                    <a href="{{ route('educational.schedules.index') }}"
                                        class="btn btn-soft-secondary px-4">{{ __('educational::messages.cancel') ?? 'إلغاء' }}</a>
                                    <button type="submit" class="btn btn-warning px-5 shadow-sm fw-bold">
                                        <i class="ri-save-line align-bottom me-1"></i>
                                        {{ __('educational::messages.save') ?? 'حفظ التغييرات' }}
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
        function loadGroups(programId) {
            const groupSelect = document.getElementById('group_id');
            groupSelect.innerHTML = '<option value="">جارٍ التحميل...</option>';

            if (!programId) {
                groupSelect.innerHTML = '<option value="">اختر البرنامج أولاً</option>';
                return;
            }

            fetch(`{{ url('educational/api/programs') }}/${programId}/groups`)
                .then(response => response.json())
                .then(data => {
                    groupSelect.innerHTML = '<option value="">اختر المجموعة</option>';
                    data.forEach(group => {
                        const option = document.createElement('option');
                        option.value = group.id;
                        option.textContent = group.name;
                        groupSelect.appendChild(option);
                    });
                })
                .catch(() => {
                    groupSelect.innerHTML = '<option value="">خطأ في تحميل المجموعات</option>';
                });
        }
    </script>
@endpush
