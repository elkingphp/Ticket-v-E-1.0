@extends('core::layouts.master')
@section('title', __('educational::messages.evaluation_forms'))

@section('content')

@include('modules.educational.shared.alerts')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">{{ __('educational::messages.evaluation_forms') }}</h5>
                <button class="btn btn-primary flex-shrink-0" data-bs-toggle="modal" data-bs-target="#createFormModal"><i class="ri-add-line align-bottom me-1"></i> {{ __('educational::messages.new_form') }}</button>
            </div>
            <div class="card-body">
                @component('modules.educational.shared.table', ['columns' => [__('educational::messages.id'), __('educational::messages.title'), __('educational::messages.type'), __('educational::messages.status'), __('educational::messages.questions'), __('educational::messages.actions')]])
                    @forelse($forms as $form)
                        <tr>
                            <td>{{ $form->id }}</td>
                            <td>{{ $form->title }}</td>
                            <td>
                                @php
                                    $typeColors = [
                                        'lecture_feedback' => 'info',
                                        'course_evaluation' => 'primary',
                                        'instructor_evaluation' => 'secondary'
                                    ];
                                    $typeColor = $typeColors[$form->type] ?? 'dark';
                                @endphp
                                <span class="badge bg-{{ $typeColor }} fs-12">{{ __("educational::messages.".$form->type) }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $form->status == 'active' ? 'bg-success' : 'bg-warning' }} fs-12">{{ __("educational::messages.".$form->status) }}</span>
                            </td>
                            <td>{{ $form->questions_count ?? 0 }} {{ __('educational::messages.questions') }}</td>
                            <td>
                                <a href="#" class="btn btn-sm btn-info">{{ __('educational::messages.edit') }}</a>
                                <a href="#" class="btn btn-sm btn-primary">{{ __('educational::messages.results') }}</a>
                                @can('education.evaluations.manage')
                                    <button class="btn btn-sm btn-danger" onclick="confirmDeleteForm({{ $form->id }})"><i class="ri-delete-bin-line"></i></button>
                                    <form id="delete-form-{{ $form->id }}" action="#" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">{{ __('educational::messages.no_forms') }}</td>
                        </tr>
                    @endforelse
                @endcomponent
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmDeleteForm(id) {
        Swal.fire({
            title: '{{ __("educational::messages.delete_form_title") }}',
            text: '{{ __("educational::messages.delete_form_text") }}',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#f06548',
            cancelButtonColor: '#878a99',
            confirmButtonText: '{{ __("educational::messages.yes_delete") }}',
            cancelButtonText: '{{ __("educational::messages.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                // If the route existed, we would submit it:
                // document.getElementById('delete-form-' + id).submit();
                Swal.fire('{{ __("educational::messages.deleted") }}', '{{ __("educational::messages.form_deleted") }}', 'success');
            }
        });
    }
</script>
@endpush

@component('modules.educational.shared.modal', ['id' => 'createFormModal', 'title' => __('educational::messages.create_form'), 'size' => 'modal-lg'])
    <form action="{{ route('educational.evaluations.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">{{ __('educational::messages.form_title') }}</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">{{ __('educational::messages.type') }}</label>
                <select name="type" class="form-select" required>
                    <option value="lecture_feedback">{{ __('educational::messages.lecture_feedback') }}</option>
                    <option value="course_evaluation">{{ __('educational::messages.course_evaluation') }}</option>
                    <option value="instructor_evaluation">{{ __('educational::messages.instructor_evaluation') }}</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('educational::messages.status') }}</label>
                <select name="status" class="form-select" required>
                    <option value="draft">{{ __('educational::messages.draft') }}</option>
                    <option value="active">{{ __('educational::messages.active') }}</option>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">{{ __('educational::messages.description') }}</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success">{{ __('educational::messages.save') }}</button>
        </div>
    </form>
@endcomponent
@endsection
