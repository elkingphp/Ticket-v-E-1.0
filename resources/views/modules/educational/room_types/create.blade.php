@extends('core::layouts.master')
@section('title', __('educational::messages.add_room_type'))

@section('content')
@include('modules.educational.shared.alerts')

<div class="row justify-content-center mt-4">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-soft-info border-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm me-3">
                        <div class="avatar-title bg-info text-white rounded-circle fs-4">
                            <i class="ri-function-add-line"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0 fw-bold">{{ __('educational::messages.add_room_type') }}</h5>
                        <p class="text-muted mb-0 small">أضف نوعاً جديداً للقاعات يمكن استخدامه عند إنشاء القاعات والمعامل.</p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('educational.room_types.index') }}" class="btn btn-soft-secondary btn-sm rounded-pill px-3">
                            <i class="ri-arrow-left-line align-middle me-1"></i> {{ __('educational::messages.back_to_list') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('educational.room_types.store') }}" method="POST">
                    @csrf

                    <div class="row g-4">
                        {{-- Name --}}
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="name" class="form-label fw-bold">{{ __('educational::messages.room_type_name') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-text-snippet text-muted"></i></span>
                                    <input type="text" class="form-control py-2 @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}"
                                           placeholder="مثال: مختبر حاسوب">
                                </div>
                                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Slug --}}
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="slug" class="form-label fw-bold">{{ __('educational::messages.room_type_slug') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-key-line text-muted"></i></span>
                                    <input type="text" class="form-control py-2 @error('slug') is-invalid @enderror"
                                           id="slug" name="slug" value="{{ old('slug') }}"
                                           placeholder="مثال: computer_lab" dir="ltr">
                                </div>
                                <div class="text-muted small mt-1">
                                    <i class="ri-information-line me-1"></i> أحرف إنجليزية صغيرة وأرقام وشرطة سفلية فقط (يتم توليده تلقائياً)
                                </div>
                                @error('slug') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Color --}}
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="color" class="form-label fw-bold">{{ __('educational::messages.room_type_color') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-palette-line text-muted"></i></span>
                                    <select class="form-select py-2 @error('color') is-invalid @enderror" id="color" name="color">
                                        @foreach(['primary' => 'أزرق (Primary)', 'success' => 'أخضر (Success)', 'info' => 'سماوي (Info)', 'warning' => 'برتقالي (Warning)', 'danger' => 'أحمر (Danger)', 'secondary' => 'رمادي (Secondary)', 'dark' => 'داكن (Dark)'] as $val => $label)
                                            <option value="{{ $val }}" {{ old('color') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('color') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Icon --}}
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="icon" class="form-label fw-bold">{{ __('educational::messages.room_type_icon') ?? 'الأيقونة' }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0" id="icon-preview">
                                        <i class="ri-door-open-line fs-5 text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control py-2 @error('icon') is-invalid @enderror"
                                           id="icon" name="icon" value="{{ old('icon', 'ri-door-open-line') }}"
                                           placeholder="ri-door-open-line" dir="ltr">
                                </div>
                                <div class="text-muted small mt-1">
                                    <i class="ri-information-line me-1"></i>
                                    اختر أيقونة من <a href="https://remixicon.com" target="_blank" class="text-info">Remix Icons</a>
                                </div>
                                @error('icon') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Sort Order --}}
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="sort_order" class="form-label fw-bold">{{ __('educational::messages.sort_order') ?? 'الترتيب' }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ri-sort-asc text-muted"></i></span>
                                    <input type="number" class="form-control py-2 @error('sort_order') is-invalid @enderror"
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                                </div>
                                @error('sort_order') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Is Active --}}
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold d-block">{{ __('educational::messages.status') }}</label>
                                <div class="d-flex align-items-center gap-3 p-2 bg-light rounded">
                                    <div class="form-check form-switch form-switch-lg mb-0">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-medium ms-2" for="is_active">
                                            {{ __('educational::messages.status_active') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label for="description" class="form-label fw-bold">{{ __('educational::messages.description') ?? 'الوصف' }}</label>
                                <textarea class="form-control py-2 @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="3"
                                          placeholder="وصف اختياري لنوع القاعة...">{{ old('description') }}</textarea>
                                @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-12 mt-4 border-top pt-4">
                            <div class="hstack gap-2 justify-content-end">
                                <a href="{{ route('educational.room_types.index') }}" class="btn btn-soft-secondary px-4">{{ __('educational::messages.cancel') ?? 'إلغاء' }}</a>
                                <button type="submit" class="btn btn-info px-5 shadow-sm fw-bold">
                                    <i class="ri-save-line align-bottom me-1"></i> {{ __('educational::messages.save') ?? 'حفظ' }}
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
    // Live icon preview
    document.getElementById('icon').addEventListener('input', function() {
        const preview = document.getElementById('icon-preview');
        preview.innerHTML = `<i class="${this.value} fs-5 text-info"></i>`;
    });

    // Auto-generate slug from name
    document.getElementById('name').addEventListener('input', function() {
        const slugField = document.getElementById('slug');
        if (!slugField.dataset.modified) {
            slugField.value = this.value
                .toLowerCase()
                .replace(/\s+/g, '_')
                .replace(/[^a-z0-9_]/g, '')
                .replace(/__+/g, '_');
        }
    });
    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.modified = 'true';
    });
</script>
@endpush
