@extends('core::layouts.master')
@section('title', 'تعديل النموذج: ' . $form->title)

@push('styles')
<style>
    .question-card { transition: box-shadow .2s, transform .2s; }
    .question-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }
    .question-card.sortable-ghost { opacity: .4; transform: scale(.98); }
    .drag-handle { cursor: grab; color: #adb5bd; }
    .drag-handle:active { cursor: grabbing; }
    .choice-tag { display: inline-flex; align-items: center; gap: 6px; background: #f3f6f9;
                  border: 1px solid #e0e6ed; border-radius: 20px; padding: 4px 12px; font-size: 13px; }
</style>
@endpush

@section('content')
@include('modules.educational.shared.alerts')

<div class="row justify-content-center">
    <div class="col-lg-10">

        {{-- ─── Page Header ──────────────────────────── --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-sm">
                    <div class="avatar-title bg-warning-subtle text-warning rounded-circle fs-20">
                        <i class="ri-edit-2-line"></i>
                    </div>
                </div>
                <div>
                    <h4 class="mb-0 fw-semibold">تعديل: {{ $form->title }}</h4>
                    <p class="text-muted mb-0 small">
                        <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill">مسودة</span>
                        · آخر تعديل {{ $form->updated_at->diffForHumans() }}
                    </p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('educational.evaluations.forms.show', $form) }}" class="btn btn-outline-info btn-sm">
                    <i class="ri-eye-line me-1"></i> معاينة
                </a>
                @can('publish', $form)
                    <button type="button" class="btn btn-success btn-sm" onclick="confirmPublish()">
                        <i class="ri-send-plane-fill me-1"></i> نشر النموذج
                    </button>
                    <form id="publish-form" action="{{ route('educational.evaluations.forms.publish', $form) }}" method="POST" class="d-none">@csrf</form>
                @endcan
            </div>
        </div>

        {{-- ─── Section 1: Metadata ──────────────────── --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent border-0 pt-4 pb-0">
                <h5 class="mb-0 fs-15 fw-semibold">
                    <i class="ri-information-line text-primary me-2"></i>معلومات النموذج
                </h5>
            </div>
            <div class="card-body pt-3">
                <form id="metaForm" action="{{ route('educational.evaluations.forms.update', $form) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-medium">عنوان النموذج <span class="text-danger">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $form->title) }}"
                               class="form-control @error('title') is-invalid @enderror" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">نوع النموذج والمستهدف</label>
                            <select name="form_type_id" class="form-select @error('form_type_id') is-invalid @enderror">
                                @foreach($formTypes as $typeOption)
                                    <option value="{{ $typeOption->id }}" {{ old('form_type_id', $form->form_type_id) == $typeOption->id ? 'selected' : '' }}>
                                        {{ $typeOption->name }} ({{ \Modules\Educational\Domain\Models\EvaluationType::TARGET_TYPES[$typeOption->target_type] ?? $typeOption->target_type }})
                                    </option>
                                @endforeach
                            </select>
                            @error('form_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-medium">الوصف</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $form->description) }}</textarea>
                    </div>
                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="ri-save-line me-1"></i> حفظ المعلومات
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ─── Section 2: Questions ─────────────────── --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fs-15 fw-semibold">
                    <i class="ri-question-line text-primary me-2"></i>الأسئلة
                    <span class="badge bg-primary-subtle text-primary ms-1 fs-12" id="question-counter">{{ $form->questions->count() }}</span>
                </h5>
                <button type="button" class="btn btn-soft-primary btn-sm" id="addQuestionBtn">
                    <i class="ri-add-line me-1"></i> إضافة سؤال
                </button>
            </div>
            <div class="card-body pt-3">
                <div id="questions-container" class="d-flex flex-column gap-3">

                    {{-- Existing saved questions --}}
                    @foreach($form->questions as $question)
                    <div class="question-card card border shadow-sm"
                         id="sq-{{ $question->id }}"
                         data-question-id="{{ $question->id }}">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="drag-handle pt-1 fs-20 text-muted"><i class="ri-drag-move-2-line"></i></div>
                                <div class="flex-grow-1">
                                    <div class="row g-2">
                                        <div class="col-md-7">
                                            <label class="form-label small fw-medium mb-1">نص السؤال</label>
                                            <input type="text" class="form-control form-control-sm sq-text"
                                                   value="{{ $question->question_text }}" data-id="{{ $question->id }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-medium mb-1">النوع</label>
                                            <select class="form-select form-select-sm sq-type"
                                                    data-id="{{ $question->id }}" data-idx="sq-{{ $question->id }}">
                                                @foreach(\Modules\Educational\Domain\Models\EvaluationQuestion::TYPES as $k => $v)
                                                    <option value="{{ $k }}" {{ $question->type == $k ? 'selected' : '' }}>{{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end pb-1">
                                            <div class="form-check">
                                                <input class="form-check-input sq-required" type="checkbox"
                                                       data-id="{{ $question->id }}"
                                                       id="req-sq-{{ $question->id }}"
                                                       {{ $question->is_required ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="req-sq-{{ $question->id }}">إجباري</label>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Multiple choice options for existing --}}
                                    <div class="mt-2 sq-options-area" id="opts-sq-{{ $question->id }}"
                                         style="{{ $question->type === 'multiple_choice' ? 'display:block' : 'display:none' }}">
                                        <label class="form-label small fw-medium mb-2">الخيارات</label>
                                        <div class="choices-list d-flex flex-wrap gap-2 mb-2" id="choices-sq-{{ $question->id }}">
                                            @foreach($question->getOptionsArray() as $opt)
                                                <span class="choice-tag" data-val="{{ $opt }}">
                                                    {{ $opt }}
                                                    <button type="button" class="btn-close" style="font-size:10px"
                                                            onclick="removeChoice(this, 'sq-{{ $question->id }}')"></button>
                                                </span>
                                            @endforeach
                                        </div>
                                        <input type="hidden" id="options-json-sq-{{ $question->id }}"
                                               value="{{ json_encode($question->options ?? []) }}">
                                        <div class="input-group input-group-sm" style="max-width:360px">
                                            <input type="text" class="form-control choice-input"
                                                   id="choice-input-sq-{{ $question->id }}"
                                                   placeholder="خيار جديد">
                                            <button type="button" class="btn btn-outline-secondary"
                                                    onclick="addExistingChoice('sq-{{ $question->id }}')">إضافة</button>
                                        </div>
                                    </div>

                                </div>
                                <div class="d-flex flex-column gap-2 flex-shrink-0 mt-1">
                                    <button type="button" class="btn btn-soft-primary btn-sm px-2"
                                            onclick="saveQuestion({{ $question->id }}, 'sq-{{ $question->id }}')">
                                        <i class="ri-save-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-ghost-danger btn-sm px-2"
                                            onclick="deleteQuestion({{ $question->id }}, 'sq-{{ $question->id }}')">
                                        <i class="ri-delete-bin-5-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($form->questions->isEmpty())
                <div class="text-center py-5 text-muted" id="empty-hint">
                    <i class="ri-question-line fs-32 mb-2 d-block"></i>
                    <p class="mb-0 small">لا توجد أسئلة بعد. اضغط "إضافة سؤال".</p>
                </div>
                @endif
            </div>
        </div>

        <div class="d-flex justify-content-between mb-5">
            <a href="{{ route('educational.evaluations.forms.index') }}" class="btn btn-ghost-secondary">
                <i class="ri-arrow-right-line me-1"></i> العودة للقائمة
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    const STORE_URL    = "{{ route('educational.evaluations.questions.store', $form) }}";
    const UPDATE_BASE  = "{{ url('educational/evaluations/questions') }}/";
    const DELETE_BASE  = "{{ url('educational/evaluations/questions') }}/";
    const REORDER_URL  = "{{ route('educational.evaluations.questions.reorder', $form) }}";
    const CSRF         = document.querySelector('meta[name="csrf-token"]').content;

    // ─── New question temp counter ───────
    let tempIdx = 1000;

    const container = document.getElementById('questions-container');

    // ─── Sortable + reorder ──────────────
    new Sortable(container, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        onEnd() {
            triggerReorder();
        },
    });

    async function triggerReorder() {
        const allCards = [...container.querySelectorAll('.question-card')];
        const hasNew = allCards.some(el => el.id.startsWith('new-'));
        
        if (hasNew) {
            console.log('Skipping reorder: Some questions are not saved yet.');
            return;
        }

        const order = allCards.map(el => el.dataset.questionId).filter(id => !!id);
        if (order.length === 0) return;

        try {
            const res = await fetch(REORDER_URL, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json' 
                },
                body: JSON.stringify({ order }),
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Failed to reorder');
            
            // Optional: Show toast for reorder success
            if (window.Toast) {
                window.Toast.fire({ icon: 'success', title: 'تم تحديث الترتيب' });
            }
        } catch (err) {
            console.error('Reorder error:', err);
            alert('حدث خطأ أثناء حفظ الترتيب: ' + err.message);
        }
    }

    // ─── Add new question ────────────────
    document.getElementById('addQuestionBtn').addEventListener('click', () => {
        const idx = 'new-' + (tempIdx++);
        const card = document.createElement('div');
        card.className = 'question-card card border shadow-sm';
        card.id = idx;
        card.innerHTML = buildNewCard(idx);
        container.appendChild(card);
        card.querySelector('.qtype-select').addEventListener('change', e => {
            document.getElementById(`opts-${idx}`).style.display =
                e.target.value === 'multiple_choice' ? 'block' : 'none';
        });
        document.getElementById('empty-hint')?.remove();
        updateCounter();
    });

    function buildNewCard(idx) {
        const types = @json(array_keys(\Modules\Educational\Domain\Models\EvaluationQuestion::TYPES));
        const labels = @json(\Modules\Educational\Domain\Models\EvaluationQuestion::TYPES);
        const opts = types.map(k => `<option value="${k}">${labels[k]}</option>`).join('');
        return `
        <div class="card-body p-3">
        <div class="d-flex align-items-start gap-3">
            <div class="drag-handle pt-1 fs-20 text-muted"><i class="ri-drag-move-2-line"></i></div>
            <div class="flex-grow-1">
                <div class="row g-2">
                    <div class="col-md-7">
                        <input type="text" class="form-control form-control-sm new-q-text"
                               placeholder="نص السؤال..." id="text-${idx}">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm qtype-select" id="type-${idx}">
                            ${opts}
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="req-${idx}" checked>
                            <label class="form-check-label small" for="req-${idx}">إجباري</label>
                        </div>
                    </div>
                </div>
                <div class="mt-2" id="opts-${idx}" style="display:none">
                    <div class="choices-list d-flex flex-wrap gap-2 mb-2" id="choices-${idx}"></div>
                    <input type="hidden" id="options-json-${idx}" value="">
                    <div class="input-group input-group-sm" style="max-width:360px">
                        <input type="text" class="form-control choice-input" id="choice-input-${idx}" placeholder="خيار جديد">
                        <button type="button" class="btn btn-outline-secondary" onclick="addChoice('${idx}')">إضافة</button>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-column gap-2 flex-shrink-0 mt-1">
                <button type="button" class="btn btn-soft-success btn-sm px-2" onclick="saveNewQuestion('${idx}')">
                    <i class="ri-save-line"></i>
                </button>
                <button type="button" class="btn btn-ghost-danger btn-sm px-2"
                        onclick="document.getElementById('${idx}').remove(); updateCounter()">
                    <i class="ri-delete-bin-5-line"></i>
                </button>
            </div>
        </div>
        </div>`;
    }

    // ─── Save new question via AJAX ──────
    async function saveNewQuestion(idx) {
        const text      = document.getElementById(`text-${idx}`)?.value?.trim();
        const type      = document.getElementById(`type-${idx}`)?.value;
        const isRequired= document.getElementById(`req-${idx}`)?.checked ? 1 : 0;
        const options   = document.getElementById(`options-json-${idx}`)?.value;

        if (!text) { alert('يرجى كتابة نص السؤال'); return; }

        const body = { question_text: text, type, is_required: isRequired };
        if (type === 'multiple_choice') body.options = JSON.parse(options || '[]');

        const res = await fetch(STORE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(body),
        });
        const data = await res.json();
        if (res.ok) {
            // Replace temp card with a proper saved card
            const el = document.getElementById(idx);
            el.id = `sq-${data.question.id}`;
            el.dataset.questionId = data.question.id;
            el.innerHTML = buildSavedCardHTML(data.question);
            attachSavedTypeListener(data.question.id);
            updateCounter();
            
            // Trigger a reorder since we just added a question that might not be at the natural end
            triggerReorder();
        }
    }


    // ─── Save existing question ──────────
    async function saveQuestion(id, cardId) {
        const card      = document.getElementById(cardId);
        const text      = card.querySelector('.sq-text')?.value?.trim();
        const type      = card.querySelector('.sq-type')?.value;
        const isRequired= card.querySelector('.sq-required')?.checked ? 1 : 0;
        const options   = document.getElementById(`options-json-${cardId}`)?.value;

        const body = { question_text: text, type, is_required: isRequired };
        if (type === 'multiple_choice') body.options = JSON.parse(options || '[]');
        body._method = 'PUT';

        const res = await fetch(UPDATE_BASE + id, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(body),
        });
        if (res.ok) {
            const btn = document.querySelector(`#${cardId} .btn-soft-primary`);
            btn.innerHTML = '<i class="ri-check-line"></i>';
            setTimeout(() => { btn.innerHTML = '<i class="ri-save-line"></i>'; }, 1500);
        }
    }

    // ─── Delete question ─────────────────
    async function deleteQuestion(id, cardId) {
        if (!confirm('حذف هذا السؤال؟')) return;
        const res = await fetch(DELETE_BASE + id, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ _method: 'DELETE' }),
        });
        if (res.ok) {
            document.getElementById(cardId)?.remove();
            updateCounter();
        }
    }

    // ─── Multiple choice helpers ─────────
    function addChoice(idx) {
        const input = document.getElementById(`choice-input-${idx}`);
        const val   = input.value.trim();
        if (!val) return;
        const list  = document.getElementById(`choices-${idx}`);
        const tag   = document.createElement('span');
        tag.className = 'choice-tag';
        tag.dataset.val = val;
        tag.innerHTML = `${val} <button type="button" class="btn-close" style="font-size:10px" onclick="removeChoice(this,'${idx}')"></button>`;
        list.appendChild(tag);
        input.value = '';
        syncChoices(idx);
    }

    function addExistingChoice(idx) { addChoice(idx); }

    function removeChoice(btn, idx) { btn.closest('.choice-tag').remove(); syncChoices(idx); }

    function syncChoices(idx) {
        const vals = [...document.querySelectorAll(`#choices-${idx} .choice-tag`)].map(t => t.dataset.val);
        document.getElementById(`options-json-${idx}`).value = JSON.stringify(vals);
    }

    function attachSavedTypeListener(id) {
        const sel = document.querySelector(`#sq-${id} .sq-type`);
        if (sel) sel.addEventListener('change', e => {
            document.getElementById(`opts-sq-${id}`).style.display = e.target.value === 'multiple_choice' ? 'block' : 'none';
        });
    }

    function updateCounter() {
        document.getElementById('question-counter').textContent =
            container.querySelectorAll('.question-card').length;
    }

    function buildSavedCardHTML(q) {
        const types = @json(\Modules\Educational\Domain\Models\EvaluationQuestion::TYPES);
        const options = q.options || [];
        const isMc = q.type === 'multiple_choice';
        
        let typeOpts = '';
        for(const [k, v] of Object.entries(types)) {
            typeOpts += `<option value="${k}" ${q.type === k ? 'selected' : ''}>${v}</option>`;
        }

        let choicesHtml = '';
        options.forEach(opt => {
            choicesHtml += `
                <span class="choice-tag" data-val="${opt}">
                    ${opt}
                    <button type="button" class="btn-close" style="font-size:10px"
                            onclick="removeChoice(this, 'sq-${q.id}')"></button>
                </span>`;
        });

        return `
        <div class="card-body p-3">
            <div class="d-flex align-items-start gap-3">
                <div class="drag-handle pt-1 fs-20 text-muted"><i class="ri-drag-move-2-line"></i></div>
                <div class="flex-grow-1">
                    <div class="row g-2">
                        <div class="col-md-7">
                            <label class="form-label small fw-medium mb-1">نص السؤال</label>
                            <input type="text" class="form-control form-control-sm sq-text"
                                   value="${q.question_text}" data-id="${q.id}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-medium mb-1">النوع</label>
                            <select class="form-select form-select-sm sq-type"
                                    data-id="${q.id}" data-idx="sq-${q.id}">
                                ${typeOpts}
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end pb-1">
                            <div class="form-check">
                                <input class="form-check-input sq-required" type="checkbox"
                                       data-id="${q.id}"
                                       id="req-sq-${q.id}"
                                       ${q.is_required ? 'checked' : ''}>
                                <label class="form-check-label small" for="req-sq-${q.id}">إجباري</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 sq-options-area" id="opts-sq-${q.id}"
                         style="${isMc ? 'display:block' : 'display:none'}">
                        <label class="form-label small fw-medium mb-2">الخيارات</label>
                        <div class="choices-list d-flex flex-wrap gap-2 mb-2" id="choices-sq-${q.id}">
                            ${choicesHtml}
                        </div>
                        <input type="hidden" id="options-json-sq-${q.id}"
                               value='${JSON.stringify(options)}'>
                        <div class="input-group input-group-sm" style="max-width:360px">
                            <input type="text" class="form-control choice-input"
                                   id="choice-input-sq-${q.id}"
                                   placeholder="خيار جديد">
                            <button type="button" class="btn btn-outline-secondary"
                                    onclick="addExistingChoice('sq-${q.id}')">إضافة</button>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-2 flex-shrink-0 mt-1">
                    <button type="button" class="btn btn-soft-primary btn-sm px-2"
                            onclick="saveQuestion(${q.id}, 'sq-${q.id}')">
                        <i class="ri-save-line"></i>
                    </button>
                    <button type="button" class="btn btn-ghost-danger btn-sm px-2"
                            onclick="deleteQuestion(${q.id}, 'sq-${q.id}')">
                        <i class="ri-delete-bin-5-line"></i>
                    </button>
                </div>
            </div>
        </div>`;
    }

    // Publish confirmation
    function confirmPublish() {
        if (confirm('بعد النشر لن يمكن تعديل النموذج. هل أنت متأكد؟')) {
            document.getElementById('publish-form').submit();
        }
    }

    // Attach type listeners to existing saved questions on load
    document.querySelectorAll('[data-question-id]').forEach(el => {
        const id = el.dataset.questionId;
        attachSavedTypeListener(id);
    });
</script>
@endpush
