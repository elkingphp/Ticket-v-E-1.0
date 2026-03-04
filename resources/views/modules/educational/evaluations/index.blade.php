@extends('core::layouts.master')
@section('title', 'نماذج الرقابة على المحاضرات')

@section('title-actions')
    @can('education.evaluations.manage')
        <a href="{{ route('educational.evaluations.settings.index') }}"
           class="btn btn-soft-info btn-icon shadow-sm d-flex align-items-center justify-content-center"
           style="height: 38.5px; width: 38.5px;"
           title="إعدادات نظام التقييم"
           data-bs-toggle="tooltip">
            <i class="ri-settings-4-line fs-18"></i>
        </a>
    @endcan
@endsection

@push('styles')
    <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
@endpush

@section('content')
@include('modules.educational.shared.alerts')

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0" id="evaluationFormsList">

            {{-- ─── Header ─────────────────────────────────────────────── --}}
            <div class="card-header border-0 pb-0">
                <div class="row align-items-center gy-3">
            <div class="col-sm">                    
                        <h5 class="card-title mb-0 d-flex align-items-center">
                            <div class="avatar-xs me-3">
                                <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-16 shadow-sm">
                                    <i class="ri-survey-line"></i>
                                </div>
                            </div>
                            نماذج الرقابة على المحاضرات
                        </h5>
                    </div>
                    <div class="col-sm-auto">
                        <div class="hstack gap-2">
                            @can('education.evaluations.manage')
                                <a href="{{ route('educational.evaluations.settings.index') }}"
                                   class="btn btn-soft-info btn-icon shadow-sm d-flex align-items-center justify-content-center"
                                   style="height: 38px; width: 38px;"
                                   title="إعدادات نظام التقييم"
                                   data-bs-toggle="tooltip">
                                    <i class="ri-settings-4-line fs-16"></i>
                                </a>
                            @endcan
                            @can('create', \Modules\Educational\Domain\Models\EvaluationForm::class)
                                <a href="{{ route('educational.evaluations.forms.create') }}" class="btn btn-primary shadow-sm">
                                    <i class="ri-add-line align-bottom me-1"></i> نموذج جديد
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─── Filters ─────────────────────────────────────────────── --}}
            <div class="card-body border-bottom pb-3 pt-3">
                <form method="GET" action="{{ route('educational.evaluations.forms.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">بحث</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="ri-search-line text-muted"></i></span>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       class="form-control border-start-0 ps-0" placeholder="ابحث بعنوان النموذج...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="">الكل</option>
                                @foreach(\Modules\Educational\Domain\Models\EvaluationForm::STATUSES as $key => $label)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">النوع</label>
                            <select name="type" class="form-select">
                                <option value="">الكل</option>
                                @foreach($formTypes as $typeOption)
                                    <option value="{{ $typeOption->id }}" {{ request('type') == $typeOption->id ? 'selected' : '' }}>{{ $typeOption->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="ri-filter-line me-1"></i> فلتر
                            </button>
                            <a href="{{ route('educational.evaluations.forms.index') }}" class="btn btn-light">
                                <i class="ri-refresh-line"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- ─── Table ────────────────────────────────────────────────── --}}
            <div class="card-body">
                <div class="table-responsive table-card">
                    <table class="table table-hover table-centered align-middle table-nowrap mb-0" id="formsTable">
                        <thead class="text-muted table-light">
                            <tr>
                                <th style="width:50px">#</th>
                                <th>العنوان</th>
                                <th>النوع</th>
                                <th>الأسئلة</th>
                                <th>التقييمات</th>
                                <th>الحالة</th>
                                <th>تاريخ النشر</th>
                                <th style="width:160px">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @forelse($forms as $form)
                            <tr>
                                <td><span class="fw-medium text-muted">{{ $loop->iteration }}</span></td>

                                {{-- Title --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-xs">
                                            <div class="avatar-title bg-primary-subtle text-primary rounded fs-16">
                                                <i class="ri-file-list-3-line"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold">{{ $form->title }}</h6>
                                            @if($form->description)
                                                <small class="text-muted">{{ Str::limit($form->description, 45) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Type --}}
                                <td>
                                    @if($form->formType)
                                        <span class="badge bg-primary-subtle text-primary border border-primary fs-12 px-2 py-1 rounded-pill">
                                            {{ $form->formType->name }}
                                        </span>
                                    @else
                                        @php
                                            $typeColors = [
                                                'lecture_feedback'      => 'info',
                                                'course_evaluation'     => 'primary',
                                                'instructor_evaluation' => 'secondary',
                                                'general'               => 'dark',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $typeColors[$form->type] ?? 'dark' }}-subtle text-{{ $typeColors[$form->type] ?? 'dark' }} border border-{{ $typeColors[$form->type] ?? 'dark' }} fs-12 px-2 py-1 rounded-pill">
                                            {{ \Modules\Educational\Domain\Models\EvaluationForm::TYPES[$form->type] ?? $form->type }} (إرث)
                                        </span>
                                    @endif
                                </td>

                                {{-- Questions count --}}
                                <td>
                                    <span class="badge bg-light text-dark border fs-12 px-2 py-1">
                                        <i class="ri-question-line align-bottom me-1"></i>
                                        {{ $form->questions_count ?? 0 }} سؤال
                                    </span>
                                </td>

                                {{-- Evaluations count --}}
                                <td>
                                    <span class="badge bg-light text-dark border fs-12 px-2 py-1">
                                        <i class="ri-file-check-line align-bottom me-1"></i>
                                        {{ $form->lecture_evaluations_count ?? 0 }} تقييم
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td>
                                    @if($form->status === 'published')
                                        <span class="badge border border-success text-success bg-success-subtle fs-12 px-3 py-1 rounded-pill shadow-sm">
                                            <i class="ri-checkbox-circle-fill me-1"></i> منشور
                                        </span>
                                    @elseif($form->status === 'draft')
                                        <span class="badge border border-warning text-warning bg-warning-subtle fs-12 px-3 py-1 rounded-pill shadow-sm">
                                            <i class="ri-edit-circle-line me-1"></i> مسودة
                                        </span>
                                    @else
                                        <span class="badge border border-secondary text-secondary bg-secondary-subtle fs-12 px-3 py-1 rounded-pill shadow-sm">
                                            <i class="ri-archive-line me-1"></i> مؤرشف
                                        </span>
                                    @endif
                                </td>

                                {{-- Published at --}}
                                <td>
                                    @if($form->published_at)
                                        <small class="text-muted">{{ $form->published_at->format('d/m/Y') }}</small>
                                    @else
                                        <small class="text-muted">—</small>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td>
                                    <ul class="list-inline hstack gap-1 mb-0">
                                        {{-- Preview --}}
                                        <li class="list-inline-item" data-bs-toggle="tooltip" title="معاينة">
                                            <a href="{{ route('educational.evaluations.forms.show', $form) }}"
                                               class="btn btn-sm text-info p-2 rounded bg-info-subtle border-0">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                        </li>

                                        {{-- Edit (draft only) --}}
                                        @can('update', $form)
                                            <li class="list-inline-item" data-bs-toggle="tooltip" title="تعديل">
                                                <a href="{{ route('educational.evaluations.forms.edit', $form) }}"
                                                   class="btn btn-sm text-primary p-2 rounded bg-primary-subtle border-0">
                                                    <i class="ri-pencil-fill"></i>
                                                </a>
                                            </li>
                                        @endcan

                                        {{-- Publish (draft only) --}}
                                        @can('publish', $form)
                                            <li class="list-inline-item" data-bs-toggle="tooltip" title="نشر النموذج">
                                                <button type="button"
                                                        class="btn btn-sm text-success p-2 rounded bg-success-subtle border-0"
                                                        onclick="confirmPublish({{ $form->id }})">
                                                    <i class="ri-send-plane-fill"></i>
                                                </button>
                                                <form id="publish-form-{{ $form->id }}"
                                                      action="{{ route('educational.evaluations.forms.publish', $form) }}"
                                                      method="POST" class="d-none">@csrf</form>
                                            </li>
                                        @endcan

                                        {{-- Results (published/archived) --}}
                                        @can('viewResults', $form)
                                            @if($form->status !== 'draft')
                                                <li class="list-inline-item" data-bs-toggle="tooltip" title="النتائج">
                                                    <a href="{{ route('educational.evaluations.forms.results', $form) }}"
                                                       class="btn btn-sm text-secondary p-2 rounded bg-secondary-subtle border-0">
                                                        <i class="ri-bar-chart-line"></i>
                                                    </a>
                                                </li>
                                            @endif
                                        @endcan

                                        {{-- Archive (published only) --}}
                                        @can('archive', $form)
                                            <li class="list-inline-item" data-bs-toggle="tooltip" title="أرشفة">
                                                <button type="button"
                                                        class="btn btn-sm text-warning p-2 rounded bg-warning-subtle border-0"
                                                        onclick="confirmArchive({{ $form->id }})">
                                                    <i class="ri-archive-line"></i>
                                                </button>
                                                <form id="archive-form-{{ $form->id }}"
                                                      action="{{ route('educational.evaluations.forms.archive', $form) }}"
                                                      method="POST" class="d-none">@csrf</form>
                                            </li>
                                        @endcan

                                        {{-- Delete (draft + no evaluations) --}}
                                        @can('delete', $form)
                                            <li class="list-inline-item" data-bs-toggle="tooltip" title="حذف">
                                                <button type="button"
                                                        class="btn btn-sm text-danger p-2 rounded bg-danger-subtle border-0"
                                                        onclick="confirmDelete({{ $form->id }})">
                                                    <i class="ri-delete-bin-5-fill"></i>
                                                </button>
                                                <form id="delete-form-{{ $form->id }}"
                                                      action="{{ route('educational.evaluations.forms.destroy', $form) }}"
                                                      method="POST" class="d-none">
                                                    @csrf @method('DELETE')
                                                </form>
                                            </li>
                                        @endcan
                                    </ul>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8">
                                    <div class="text-center p-5">
                                        <div class="avatar-md mx-auto mb-3">
                                            <div class="avatar-title bg-light text-primary rounded-circle fs-24">
                                                <i class="ri-survey-line"></i>
                                            </div>
                                        </div>
                                        <h5 class="mt-2">لا توجد نماذج مسجلة</h5>
                                        <p class="text-muted mb-3">ابدأ بإنشاء أول نموذج رقابة للمحاضرات.</p>
                                        @can('create', \Modules\Educational\Domain\Models\EvaluationForm::class)
                                            <a href="{{ route('educational.evaluations.forms.create') }}" class="btn btn-primary">
                                                <i class="ri-add-line me-1"></i> نموذج جديد
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($forms->hasPages())
                    <div class="mt-4 d-flex justify-content-end">
                        {{ $forms->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    });

    function confirmPublish(id) {
        Swal.fire({
            title: 'نشر النموذج؟',
            text: 'بعد النشر لن تتمكن من تعديل النموذج أو أسئلته.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0ab39c',
            cancelButtonColor: '#878a99',
            confirmButtonText: 'نعم، انشر',
            cancelButtonText: 'إلغاء',
            customClass: { confirmButton: 'btn btn-success w-xs me-2 mt-2', cancelButton: 'btn btn-ghost-dark w-xs mt-2' },
            buttonsStyling: false,
            showCloseButton: true,
        }).then(r => { if (r.isConfirmed) document.getElementById('publish-form-' + id).submit(); });
    }

    function confirmArchive(id) {
        Swal.fire({
            title: 'أرشفة النموذج؟',
            text: 'لن يتمكن أحد من تقديم تقييمات جديدة على هذا النموذج.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f7b84b',
            cancelButtonColor: '#878a99',
            confirmButtonText: 'نعم، أرشف',
            cancelButtonText: 'إلغاء',
            customClass: { confirmButton: 'btn btn-warning w-xs me-2 mt-2', cancelButton: 'btn btn-ghost-dark w-xs mt-2' },
            buttonsStyling: false,
            showCloseButton: true,
        }).then(r => { if (r.isConfirmed) document.getElementById('archive-form-' + id).submit(); });
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'حذف النموذج؟',
            text: 'سيتم حذف النموذج نهائياً. هذا الإجراء لا يمكن التراجع عنه.',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#f06548',
            cancelButtonColor: '#878a99',
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء',
            customClass: { confirmButton: 'btn btn-danger w-xs me-2 mt-2', cancelButton: 'btn btn-ghost-dark w-xs mt-2' },
            buttonsStyling: false,
            showCloseButton: true,
        }).then(r => { if (r.isConfirmed) document.getElementById('delete-form-' + id).submit(); });
    }
</script>
@endpush
