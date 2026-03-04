@extends('core::layouts.master')
@section('title', 'معاينة: ' . $form->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        {{-- ─── Header ───────────────────────── --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-sm">
                    <div class="avatar-title {{ $form->status === 'published' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} rounded-circle fs-20">
                        <i class="ri-survey-line"></i>
                    </div>
                </div>
                <div>
                    <h4 class="mb-0 fw-semibold">{{ $form->title }}</h4>
                    <p class="mb-0 small text-muted">
                        @if($form->status === 'published')
                            <span class="badge bg-success-subtle text-success border border-success rounded-pill">منشور</span>
                            · نُشر في {{ $form->published_at?->format('d/m/Y H:i') }}
                        @elseif($form->status === 'draft')
                            <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill">مسودة</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary rounded-pill">مؤرشف</span>
                        @endif
                        · {{ $form->formType ? $form->formType->name : (\Modules\Educational\Domain\Models\EvaluationForm::TYPES[$form->type] ?? $form->type) }}
                    </p>
                </div>
            </div>
            <div class="d-flex gap-2">
                @can('update', $form)
                    <a href="{{ route('educational.evaluations.forms.edit', $form) }}" class="btn btn-outline-primary btn-sm">
                        <i class="ri-pencil-line me-1"></i> تعديل
                    </a>
                @endcan
                <a href="{{ route('educational.evaluations.forms.index') }}" class="btn btn-ghost-secondary btn-sm">
                    <i class="ri-arrow-right-line me-1"></i> الرجوع
                </a>
            </div>
        </div>

        {{-- ─── Description ──────────────────── --}}
        @if($form->description)
        <div class="alert alert-info border-0 mb-4 rounded-3 small">
            <i class="ri-information-line me-2"></i>{{ $form->description }}
        </div>
        @endif

        {{-- ─── Questions Preview ─────────────── --}}
        <div class="d-flex flex-column gap-3">
            @forelse($form->questions as $index => $question)
            <div class="card border shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-semibold mb-0">
                            <span class="badge bg-light text-dark border me-2">{{ $index + 1 }}</span>
                            {{ $question->question_text }}
                            @if($question->is_required)
                                <span class="text-danger ms-1" title="إجباري">*</span>
                            @endif
                        </h6>
                        <span class="badge bg-{{ match($question->type) {
                            'rating_1_to_5'   => 'warning',
                            'text'            => 'info',
                            'boolean'         => 'success',
                            'multiple_choice' => 'primary',
                        } }}-subtle text-{{ match($question->type) {
                            'rating_1_to_5'   => 'warning',
                            'text'            => 'info',
                            'boolean'         => 'success',
                            'multiple_choice' => 'primary',
                        } }} border rounded-pill fs-12 px-2">
                            {{ \Modules\Educational\Domain\Models\EvaluationQuestion::TYPES[$question->type] }}
                        </span>
                    </div>

                    {{-- Input preview based on type --}}
                    @if($question->type === 'rating_1_to_5')
                        <div class="d-flex gap-2 mt-3">
                            @for($i = 1; $i <= 5; $i++)
                                <div class="text-center">
                                    <div class="avatar-sm mx-auto mb-1">
                                        <div class="avatar-title bg-light text-muted border rounded-circle fs-16 cursor-not-allowed">
                                            {{ $i }}
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        @if($i === 1) ضعيف @elseif($i === 5) ممتاز @endif
                                    </small>
                                </div>
                            @endfor
                        </div>

                    @elseif($question->type === 'text')
                        <textarea class="form-control mt-2" rows="2" disabled
                                  placeholder="مساحة للإجابة النصية..."></textarea>

                    @elseif($question->type === 'boolean')
                        <div class="d-flex gap-3 mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" disabled>
                                <label class="form-check-label">✅ نعم</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" disabled>
                                <label class="form-check-label">❌ لا</label>
                            </div>
                        </div>

                    @elseif($question->type === 'multiple_choice')
                        <div class="d-flex flex-column gap-2 mt-3">
                            @foreach($question->getOptionsArray() as $opt)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" disabled>
                                    <label class="form-check-label">{{ $opt }}</label>
                                </div>
                            @endforeach
                            @if(empty($question->getOptionsArray()))
                                <small class="text-muted fst-italic">لم تُضف خيارات بعد</small>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted">
                <i class="ri-question-line fs-32 mb-2 d-block"></i>
                <p>لا توجد أسئلة في هذا النموذج بعد.</p>
            </div>
            @endforelse
        </div>

        @if($form->questions->isNotEmpty())
        <div class="text-center mt-4 mb-5">
            <p class="text-muted small mb-0">
                <i class="ri-eye-line me-1"></i>
                هذه معاينة للقراءة فقط — لن يتم حفظ أي إجابات.
            </p>
        </div>
        @endif
    </div>
</div>
@endsection
