@extends('core::layouts.master')
@section('title', 'تعبئة نموذج التقييم')

@push('styles')
<style>
    :root { --eval-primary: #405189; --eval-success: #0ab39c; }
    body { background-color: #f3f3f9 !important; }
    
    /* Star Rating */
    .rating-stars { display: flex; flex-direction: row-reverse; gap: 10px; }
    .rating-stars input[type="radio"] { display: none; }
    .rating-stars label {
        font-size: 50px; cursor: pointer; color: #e9ebec;
        transition: all 0.2s ease-in-out; line-height: 1;
    }
    .rating-stars input:checked ~ label,
    .rating-stars label:hover,
    .rating-stars label:hover ~ label { color: #f7b84b; transform: scale(1.2); text-shadow: 0 0 15px rgba(247, 184, 75, 0.3); }

    /* Custom Radio Buttons */
    .choice-item input:checked + label {
        background-color: rgba(64, 81, 137, 0.05) !important;
        border-color: var(--eval-primary) !important;
        color: var(--eval-primary) !important;
    }
    .choice-item .radio-indicator {
        width: 18px; height: 18px; border: 2px solid #ced4da; border-radius: 50%;
        position: relative; transition: all 0.2s;
    }
    .choice-item input:checked + label .radio-indicator { border-color: var(--eval-primary); }
    .choice-item input:checked + label .radio-indicator::after {
        content: ''; position: absolute; width: 10px; height: 10px;
        background: var(--eval-primary); border-radius: 50%; top: 2px; left: 2px;
    }

    /* Step Logic */
    .form-step { display: none; }
    .form-step.active { display: block; }
    
    .slide-in { animation: slideUp 0.4s ease-out forwards; }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .progress { border-radius: 10px; background-color: #e9ebec; }
    .btn-label { position: relative; padding-left: 44px; }
    .btn-label .label-icon {
        position: absolute; width: 35px; left: -1px; top: -1px; bottom: -1px;
        background-color: rgba(255, 255, 255, 0.1); border-radius: 50px 0 0 50px;
        display: flex; align-items: center; justify-content: center;
    }
    [dir="rtl"] .btn-label { padding-left: 0.75rem; padding-right: 44px; }
    [dir="rtl"] .btn-label .label-icon { left: auto; right: -1px; border-radius: 0 50px 50px 0; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">

        {{-- ─── Header ───────────────────────────── --}}
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="avatar-sm">
                <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-20">
                    <i class="ri-survey-line"></i>
                </div>
            </div>
            <div>
                <h4 class="mb-0 fw-semibold">{{ $assignment->form->title }}</h4>
                <p class="text-muted mb-0 small">
                    <i class="ri-calendar-event-line me-1"></i>
                    {{ $assignment->lecture->starts_at->format('d/m/Y H:i') }}
                    ·
                    <span class="badge bg-{{ $evaluatorRole === 'trainee' ? 'success' : 'info' }}-subtle text-{{ $evaluatorRole === 'trainee' ? 'success' : 'info' }} border rounded-pill">
                        {{ $evaluatorRole === 'trainee' ? '🎓 متدرب' : '👁️ مراقب' }}
                    </span>
                </p>
            </div>
        </div>

        @if($assignment->form->description)
        <div class="alert alert-info border-0 rounded-3 small mb-4">
            <i class="ri-information-line me-2 fs-15"></i>{{ $assignment->form->description }}
        </div>
        @endif

        {{-- ─── Step Progress ──────────────────────── --}}
        @php $questions = $assignment->form->questions; $total = $questions->count(); @endphp
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <div class="progress mb-2" style="height: 6px;">
                    <div id="form-progress" class="progress-bar bg-success progress-bar-animated" role="progressbar" style="width: 0%"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center px-1">
                    <span class="text-muted small fw-medium" id="step-counter">السؤال 1 من {{ $total }}</span>
                    <span class="text-muted small fw-medium" id="percent-label">0% كوبملت</span>
                </div>
            </div>
        </div>

        {{-- ─── Form ───────────────────────────────── --}}
        <form id="evalForm"
              action="{{ route('educational.evaluations.assignments.submit', $assignment) }}"
              method="POST"
              class="needs-validation" novalidate>
            @csrf

            @foreach($questions as $i => $question)
            <div class="form-step {{ $i === 0 ? 'active' : '' }}" id="step-{{ $i }}">
                <div class="card border-0 shadow-lg mb-4 slide-in">
                    <div class="card-body p-4">

                        {{-- Question Header --}}
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">سؤال {{ $i + 1 }}</span>
                                @if($question->is_required)
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2"><i class="ri-error-warning-line me-1"></i>إجباري</span>
                                @endif
                            </div>
                            <h4 class="fw-bold mb-0 text-dark" style="line-height: 1.5;">{{ $question->question_text }}</h4>
                        </div>

                        {{-- ── Answer Input by Type ── --}}
                        <div class="answer-container py-3">

                            @if($question->type === 'rating_1_to_5')
                                <div class="rating-box text-center p-4 bg-light rounded-4 border border-dashed">
                                    <div class="rating-stars justify-content-center mb-3">
                                        @for($r = 5; $r >= 1; $r--)
                                            <input type="radio"
                                                   id="rate-{{ $question->id }}-{{ $r }}"
                                                   name="answers[{{ $question->id }}]"
                                                   value="{{ $r }}"
                                                   class="eval-radio"
                                                   {{ $question->is_required ? 'required' : '' }}>
                                            <label for="rate-{{ $question->id }}-{{ $r }}" class="fs-1">★</label>
                                        @endfor
                                    </div>
                                    <div class="d-flex justify-content-between px-5 text-muted fw-medium">
                                        <span>ضعيف جداً</span>
                                        <span>ممتاز</span>
                                    </div>
                                </div>

                            @elseif($question->type === 'text')
                                <div class="p-1">
                                    <textarea name="answers[{{ $question->id }}]"
                                              class="form-control form-control-lg border-2 shadow-none"
                                              rows="5"
                                              style="border-radius: 15px;"
                                              placeholder="اكتب ملاحظاتك التفصيلية هنا بالتفصيل..."
                                              data-is-required="{{ $question->is_required ? '1' : '0' }}"
                                              maxlength="2000"></textarea>
                                    <div class="d-flex justify-content-between mt-2 px-1">
                                        <div class="invalid-feedback">يرجى كتابة الإجابة للمتابعة.</div>
                                        <small class="text-muted ms-auto">
                                            <span class="char-count" data-target="answers[{{ $question->id }}]">0</span> / 2000
                                        </small>
                                    </div>
                                </div>

                            @elseif($question->type === 'boolean')
                                <div class="row g-3 justify-content-center">
                                    <div class="col-6 col-sm-4">
                                        <div class="bool-option h-100">
                                            <input type="radio" name="answers[{{ $question->id }}]" id="bool-yes-{{ $question->id }}" value="1" class="eval-radio d-none" {{ $question->is_required ? 'required' : '' }}>
                                            <label for="bool-yes-{{ $question->id }}" class="btn btn-outline-success w-100 py-4 rounded-4 shadow-sm h-100 d-flex flex-column align-items-center justify-content-center gap-2">
                                                <i class="ri-checkbox-circle-fill fs-2"></i>
                                                <span class="fw-bold">نعم / موافق</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-4">
                                        <div class="bool-option h-100">
                                            <input type="radio" name="answers[{{ $question->id }}]" id="bool-no-{{ $question->id }}" value="0" class="eval-radio d-none">
                                            <label for="bool-no-{{ $question->id }}" class="btn btn-outline-danger w-100 py-4 rounded-4 shadow-sm h-100 d-flex flex-column align-items-center justify-content-center gap-2">
                                                <i class="ri-close-circle-fill fs-2"></i>
                                                <span class="fw-bold">لا / غير موافق</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            @elseif($question->type === 'multiple_choice')
                                <div class="choice-list d-flex flex-column gap-3">
                                    @foreach($question->getOptionsArray() as $opt)
                                        <div class="choice-item">
                                            <input type="radio" name="answers[{{ $question->id }}]" id="choice-{{ $question->id }}-{{ $loop->index }}" value="{{ $opt }}" class="eval-radio d-none" {{ $question->is_required ? 'required' : '' }}>
                                            <label for="choice-{{ $question->id }}-{{ $loop->index }}" class="btn btn-outline-primary text-start w-100 py-3 px-4 rounded-3 border-2 shadow-sm d-flex align-items-center gap-3">
                                                <div class="radio-indicator"></div>
                                                <span class="fs-16 fw-medium">{{ $opt }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

                {{-- Navigation Buttons --}}
                <div class="d-flex justify-content-between align-items-center mb-5">
                    @if($i > 0)
                        <button type="button" class="btn btn-light btn-label rounded-pill px-4 py-2 shadow-sm" onclick="goStep({{ $i - 1 }})">
                            <i class="ri-arrow-right-line label-icon align-middle fs-16 me-2"></i>السابق
                        </button>
                    @else
                        <div></div>
                    @endif

                    <button type="button" class="btn btn-primary btn-label rounded-pill px-5 py-2 shadow-lg" onclick="validateStep({{ $i }})">
                        التالي<i class="ri-arrow-left-line label-icon align-middle fs-16 ms-2"></i>
                    </button>
                </div>
            </div>
            @endforeach

            {{-- ─── Final Step: Overall Comment + Submit ── --}}
            <div class="form-step" id="step-{{ $total }}">
                <div class="card border-0 shadow-lg mb-4 slide-in">
                    <div class="card-body p-5">
                        <div class="text-center mb-5">
                            <div class="avatar-lg mx-auto mb-4">
                                <div class="avatar-title bg-success-subtle text-success rounded-circle display-4 shadow-sm">
                                    <i class="ri-checkbox-multiple-line"></i>
                                </div>
                            </div>
                            <h3 class="fw-bold text-dark">تم الانتهاء من جميع الأسئلة</h3>
                            <p class="text-muted fs-16">شكراً لك على وقتك ودقة ملاحظاتك. يمكنك إضافة أي تعليقات نهائية قبل الإرسال.</p>
                        </div>

                        <div class="mb-5">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="ri-chat-1-line text-primary fs-20"></i>
                                <label class="form-label fw-bold mb-0 fs-16">ملاحظات عامة مجمعة (اختياري)</label>
                            </div>
                            <textarea name="overall_comments"
                                      class="form-control form-control-lg border-2"
                                      rows="5"
                                      style="border-radius: 15px;"
                                      placeholder="اكتب انطباعك العام أو أي تفاصيل أخرى لم تشملها الأسئلة السابقة..."
                                      maxlength="1000"></textarea>
                        </div>

                        <div class="alert alert-warning border-0 rounded-4 d-flex align-items-center gap-3 mb-5 py-3">
                            <i class="ri-error-warning-fill fs-2 w-auto"></i>
                            <div class="small fw-medium">
                                سيتم تسجيل هذا التقييم بصفتك (<b>{{ $evaluatorRole === 'trainee' ? 'متدرب' : 'مراقب' }}</b>).
                                بمجرد الإرسال، لا يمكن تعديل إجاباتك نهائياً لضمان نزاهة التقييم.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-ghost-secondary rounded-pill px-4" onclick="goStep({{ $total - 1 }})">
                                <i class="ri-arrow-right-line me-2"></i>مراجعة الإجابات
                            </button>
                            <button type="submit" class="btn btn-success btn-lg px-5 rounded-pill shadow-lg fw-bold" id="submitBtn">
                                إرسال التقييم الآن <i class="ri-check-line ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const TOTAL_QUESTIONS = {{ $total }};
let currentStep = 0;

document.addEventListener('DOMContentLoaded', () => {
    updateProgress();
    
    // Auto-advance for radio buttons
    document.querySelectorAll('.eval-radio').forEach(radio => {
        radio.addEventListener('change', () => {
            if (currentStep < TOTAL_QUESTIONS) {
                setTimeout(() => { validateStep(currentStep); }, 400);
            }
        });
    });
});

function updateProgress() {
    const percent = Math.round((currentStep / (TOTAL_QUESTIONS + 1)) * 100);
    const progressBar = document.getElementById('form-progress');
    const stepCounter = document.getElementById('step-counter');
    const percentLabel = document.getElementById('percent-label');
    
    if (progressBar) progressBar.style.width = percent + '%';
    if (stepCounter) {
        stepCounter.textContent = currentStep < TOTAL_QUESTIONS 
            ? `السؤال ${currentStep + 1} من ${TOTAL_QUESTIONS}`
            : 'مرحلة التأكيد النهائية';
    }
    if (percentLabel) percentLabel.textContent = percent + '% مكتمل';
}

function goStep(to) {
    const currentEl = document.getElementById('step-' + currentStep);
    const targetEl = document.getElementById('step-' + to);
    
    if (currentEl) currentEl.classList.remove('active');
    if (targetEl) {
        targetEl.classList.add('active');
        currentStep = to;
        updateProgress();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function validateStep(stepIndex) {
    const stepEl = document.getElementById('step-' + stepIndex);
    const radioNames = new Set();
    stepEl.querySelectorAll('input[type="radio"][required]').forEach(r => radioNames.add(r.name));
    
    let isValid = true;
    radioNames.forEach(name => {
        if (!stepEl.querySelector(`input[name="${name}"]:checked`)) {
            isValid = false;
        }
    });

    stepEl.querySelectorAll('textarea[required], textarea[data-is-required="1"]').forEach(ta => {
        if (!ta.value.trim()) {
            isValid = false;
            ta.classList.add('is-invalid');
        } else {
            ta.classList.remove('is-invalid');
        }
    });

    if (!isValid) {
        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
        Toast.fire({ icon: 'warning', title: 'يرجى الإجابة على السؤال الحالي للمتابعة.' });
        return;
    }

    goStep(stepIndex + 1);
}

// Char counter
document.querySelectorAll('textarea').forEach(ta => {
    const name = ta.getAttribute('name');
    const counter = document.querySelector(`.char-count[data-target="${name}"]`);
    if (!counter) return;
    ta.addEventListener('input', () => { counter.textContent = ta.value.length; });
});

document.getElementById('evalForm')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جاري الإرسال...';
});
</script>
@endpush
