@extends('core::layouts.master')
@section('title', 'إنشاء نموذج رقابة جديد')

@push('styles')
<style>
    .question-card { transition: box-shadow .2s, transform .2s; }
    .question-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }
    .question-card.sortable-ghost { opacity: .4; transform: scale(.98); }
    .drag-handle { cursor: grab; color: #adb5bd; }
    .drag-handle:active { cursor: grabbing; }
    .type-options-area { display: none; }
    .choice-tag { display: inline-flex; align-items: center; gap: 6px; background: #f3f6f9;
                  border: 1px solid #e0e6ed; border-radius: 20px; padding: 4px 12px; font-size: 13px; }
</style>
@endpush

@section('content')
@include('modules.educational.shared.alerts')

<div class="row justify-content-center">
    <div class="col-lg-10">

        {{-- ─── Page Header ─────────────────────────── --}}
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="avatar-sm">
                <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-20 shadow-sm">
                    <i class="ri-survey-line"></i>
                </div>
            </div>
            <div>
                <h4 class="mb-0 fw-semibold">إنشاء نموذج رقابة جديد</h4>
                <p class="text-muted mb-0 small">سيُحفظ كمسودة — يمكنك إضافة الأسئلة ثم نشره لاحقاً</p>
            </div>
        </div>

        <form id="mainForm" action="{{ route('educational.evaluations.forms.store') }}" method="POST">
            @csrf

            {{-- ─── Section 1: Form Metadata ────────── --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <h5 class="mb-0 fs-15 fw-semibold">
                        <i class="ri-information-line text-primary me-2"></i>معلومات النموذج
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-medium">عنوان النموذج <span class="text-danger">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}"
                               class="form-control @error('title') is-invalid @enderror"
                               placeholder="مثال: نموذج تقييم المحاضرة اليومية" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">نوع النموذج والمستهدف <span class="text-danger">*</span></label>
                            <select name="form_type_id" class="form-select @error('form_type_id') is-invalid @enderror" required>
                                <option value="" disabled selected>-- اختر النوع --</option>
                                @foreach($formTypes as $typeOption)
                                    <option value="{{ $typeOption->id }}" {{ old('form_type_id') == $typeOption->id ? 'selected' : '' }}>
                                        {{ $typeOption->name }} ({{ \Modules\Educational\Domain\Models\EvaluationType::TARGET_TYPES[$typeOption->target_type] ?? $typeOption->target_type }})
                                    </option>
                                @endforeach
                            </select>
                            @error('form_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-medium">الوصف <span class="text-muted small">(اختياري)</span></label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="شرح مختصر عن الهدف من هذا النموذج...">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ─── Section 2: Questions Builder ───── --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent border-0 pt-4 pb-0 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fs-15 fw-semibold">
                        <i class="ri-question-line text-primary me-2"></i>الأسئلة
                        <span class="badge bg-primary-subtle text-primary ms-2 fs-12" id="question-counter">0</span>
                    </h5>
                    <button type="button" class="btn btn-soft-primary btn-sm" id="addQuestionBtn">
                        <i class="ri-add-line me-1"></i> إضافة سؤال
                    </button>
                </div>
                <div class="card-body pt-3">
                    <div id="questions-container" class="d-flex flex-column gap-3">
                        {{-- Questions are rendered here by JS --}}
                    </div>
                    <div id="empty-questions-hint" class="text-center py-5 text-muted">
                        <i class="ri-question-line fs-32 mb-2 d-block"></i>
                        <p class="mb-0 small">لا توجد أسئلة بعد. اضغط "إضافة سؤال" لبناء النموذج.</p>
                        <p class="small text-muted">يمكنك حفظ النموذج الآن وإضافة الأسئلة لاحقاً من صفحة التعديل.</p>
                    </div>
                </div>
            </div>

            {{-- ─── Footer Actions ──────────────────── --}}
            <div class="d-flex justify-content-between align-items-center gap-3 mb-5">
                <a href="{{ route('educational.evaluations.forms.index') }}" class="btn btn-ghost-secondary">
                    <i class="ri-arrow-right-line me-1"></i> إلغاء
                </a>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4" id="saveBtn">
                        <i class="ri-save-line me-1"></i> حفظ كمسودة
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
let questionIndex = 0;
const container   = document.getElementById('questions-container');
const emptyHint   = document.getElementById('empty-questions-hint');
const counter     = document.getElementById('question-counter');

const QUESTION_TYPES = {
    rating_1_to_5:   { label: '⭐ تقييم 1–5',       hint: 'المتدرب سيختار من 1 إلى 5 نجوم' },
    text:            { label: '📝 نص حر',           hint: 'المتدرب سيكتب إجابة نصية' },
    boolean:         { label: '✅ نعم / لا',          hint: 'المتدرب سيختار نعم أو لا' },
    multiple_choice: { label: '🔘 اختيار متعدد',     hint: 'المتدرب سيختار إجابة واحدة من قائمة' },
};

function updateCounter() {
    const count = container.querySelectorAll('.question-card').length;
    counter.textContent  = count;
    emptyHint.style.display = count === 0 ? 'block' : 'none';
}

function buildQuestionCard(idx) {
    const id = 'q-' + idx;
    return `
    <div class="question-card card border shadow-sm" id="${id}" data-idx="${idx}">
        <div class="card-body p-3">
            <div class="d-flex align-items-start gap-3">
                <div class="drag-handle pt-1 fs-20"><i class="ri-drag-move-2-line"></i></div>
                <div class="flex-grow-1">

                    <div class="row g-3 align-items-start">
                        {{-- Question text --}}
                        <div class="col-md-7">
                            <label class="form-label small fw-medium mb-1">نص السؤال <span class="text-danger">*</span></label>
                            <input type="text" name="questions[${idx}][question_text]"
                                   class="form-control form-control-sm"
                                   placeholder="اكتب سؤالك هنا..." required>
                        </div>

                        {{-- Question type --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-medium mb-1">النوع</label>
                            <select name="questions[${idx}][type]" class="form-select form-select-sm qtype-select" data-idx="${idx}">
                                ${Object.entries(QUESTION_TYPES).map(([k,v]) => `<option value="${k}">${v.label}</option>`).join('')}
                            </select>
                        </div>

                        {{-- Required checkbox --}}
                        <div class="col-md-2 d-flex flex-column justify-content-end pb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="questions[${idx}][is_required]" value="1" id="req-${idx}" checked>
                                <label class="form-check-label small" for="req-${idx}">إجباري</label>
                            </div>
                        </div>
                    </div>

                    {{-- Multiple choice options area --}}
                    <div class="type-options-area mt-3" id="opts-${idx}">
                        <label class="form-label small fw-medium mb-2">
                            خيارات الإجابة <span class="text-muted">(أضف على الأقل خيارين)</span>
                        </label>
                        <div class="choices-list d-flex flex-wrap gap-2 mb-2" id="choices-${idx}"></div>
                        <div class="input-group input-group-sm" style="max-width:360px">
                            <input type="text" class="form-control choice-input" id="choice-input-${idx}"
                                   placeholder="أدخل خياراً ثم اضغط إضافة">
                            <button type="button" class="btn btn-outline-secondary"
                                    onclick="addChoice(${idx})">إضافة</button>
                        </div>
                        <input type="hidden" name="questions[${idx}][options]" id="options-json-${idx}" value="">
                    </div>

                </div>
                {{-- Remove button --}}
                <button type="button" class="btn btn-ghost-danger btn-sm mt-1 flex-shrink-0"
                        onclick="removeQuestion('${id}')">
                    <i class="ri-delete-bin-5-line fs-16"></i>
                </button>
            </div>
        </div>
    </div>`;
}

// Add question
document.getElementById('addQuestionBtn').addEventListener('click', () => {
    const idx = questionIndex++;
    container.insertAdjacentHTML('beforeend', buildQuestionCard(idx));

    // Attach type change listener
    const sel = container.querySelector(`#q-${idx} .qtype-select`);
    sel.addEventListener('change', () => toggleTypeOptions(idx, sel.value));

    updateCounter();
});

function toggleTypeOptions(idx, type) {
    const area = document.getElementById(`opts-${idx}`);
    area.style.display = type === 'multiple_choice' ? 'block' : 'none';
}

function addChoice(idx) {
    const input    = document.getElementById(`choice-input-${idx}`);
    const val      = input.value.trim();
    if (!val) return;

    const list     = document.getElementById(`choices-${idx}`);
    const tag      = document.createElement('span');
    tag.className  = 'choice-tag';
    tag.innerHTML  = `${val} <button type="button" class="btn-close" style="font-size:10px" aria-label="حذف"></button>`;
    tag.querySelector('.btn-close').onclick = () => { tag.remove(); syncChoices(idx); };
    list.appendChild(tag);
    input.value = '';
    syncChoices(idx);
}

function syncChoices(idx) {
    const choices = [...document.querySelectorAll(`#choices-${idx} .choice-tag`)]
        .map(t => t.firstChild.textContent.trim());
    document.getElementById(`options-json-${idx}`).value = JSON.stringify(choices);
}

function removeQuestion(id) {
    document.getElementById(id)?.remove();
    updateCounter();
}

// Sortable
new Sortable(container, {
    handle: '.drag-handle',
    animation: 150,
    ghostClass: 'sortable-ghost',
});

// Enter key in choice input
document.addEventListener('keydown', e => {
    if (e.target.classList.contains('choice-input') && e.key === 'Enter') {
        e.preventDefault();
        const idx = e.target.id.replace('choice-input-', '');
        addChoice(idx);
    }
});
</script>
@endpush
