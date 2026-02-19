@extends('core::layouts.master')

@section('title', __('tickets::messages.lookups.categories'))

@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="card" id="categoryList">
            <div class="card-header border-0 mt-2">
                <div class="row align-items-center gy-3">
                    <div class="col-sm">
                        <h5 class="card-title mb-0">{{ __('tickets::messages.records') }}</h5>
                    </div>
                    <div class="col-sm-auto">
                        <div class="d-flex gap-1 flex-wrap">
                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" id="create-btn" data-bs-target="#addCategoryModal">
                                <i class="ri-add-line align-bottom me-1"></i> {{ __('tickets::messages.add_category') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body border border-dashed border-start-0 border-end-0">
                <form action="{{ route('admin.tickets.categories.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-xxl-5 col-sm-12">
                            <div class="search-box position-relative">
                                <input type="text" name="search" class="form-control search bg-light border-light ps-4 pe-5" placeholder="{{ __('tickets::messages.search') }}..." value="{{ request('search') }}">
                                <i class="ri-search-line search-icon position-absolute top-50 translate-middle-y start-0 ms-3 text-muted"></i>
                                @if(request('search'))
                                    <a href="{{ route('admin.tickets.categories.index') }}" class="position-absolute top-50 translate-middle-y end-0 me-3 text-danger">
                                        <i class="ri-close-circle-line fs-16"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-xxl-3 col-sm-12">
                            <select name="stage_id" class="form-select bg-light border-light" onchange="this.form.submit()">
                                <option value="">{{ __('tickets::messages.all_stages') ?? 'جميع المراحل' }}</option>
                                @foreach($stages as $stage)
                                    <option value="{{ $stage->id }}" {{ request('stage_id') == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div>
                    <div class="table-responsive table-card mb-1">
                        <table class="table align-middle table-nowrap" id="categoryTable">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th style="width: 50px;">{{ __('tickets::messages.id') }}</th>
                                    <th>{{ __('tickets::messages.category') }}</th>
                                    <th>{{ __('tickets::messages.stage') }}</th>
                                    <th>{{ __('tickets::messages.sla_countdown') }}</th>
                                    <th>{{ __('tickets::messages.lookups.groups') }}</th>
                                    <th style="width: 150px;">{{ __('tickets::messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list form-check-all">
                                @foreach($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td class="fw-medium text-primary">{{ $category->name }}</td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info fs-12">{{ $category->stage?->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="ri-timer-line text-warning me-2 fs-18"></i>
                                            <span class="badge bg-warning-subtle text-warning fs-12">{{ $category->sla_hours ?? '-' }} {{ __('tickets::messages.hours') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($category->routing && $category->routing->group)
                                            <span class="badge bg-primary-subtle text-primary fs-11">{{ $category->routing->group->name }}</span>
                                        @else
                                            <span class="badge bg-light text-muted fs-11">{{ __('tickets::messages.default') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <ul class="list-inline hstack gap-2 mb-0">
                                            <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Edit">
                                                <button class="btn btn-soft-info btn-sm edit-item-btn" 
                                                    data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                                    data-id="{{ $category->id }}"
                                                    data-name="{{ $category->name }}"
                                                    data-stage="{{ $category->stage_id }}"
                                                    data-sla="{{ $category->sla_hours }}"
                                                    data-group="{{ $category->routing ? $category->routing->group_id : '' }}">
                                                    <i class="ri-pencil-fill align-bottom"></i>
                                                </button>
                                            </li>
                                            <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Remove">
                                                <button class="btn btn-soft-danger btn-sm remove-item-btn" onclick="deleteItem('{{ route('admin.tickets.categories.destroy', $category->id) }}')">
                                                    <i class="ri-delete-bin-fill align-bottom"></i>
                                                </button>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($categories->isEmpty())
                        <div class="noresult" style="display: block">
                            <div class="text-center py-5">
                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#405189,secondary:#0ab39c" style="width:90px;height:90px"></lord-icon>
                                <h5 class="mt-3 fw-bold text-muted">{{ __('tickets::messages.no_data_found') }}</h5>
                                <p class="text-muted mb-0">{{ __('tickets::messages.no_results_help') ?? 'لم يتم العثور على أي نتائج، ابدأ بإضافة سجل جديد.' }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    @if($categories->hasPages())
                    <div class="d-flex justify-content-end mt-3">
                        {{ $categories->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade zoomIn" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header p-3 bg-soft-primary">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs flex-shrink-0 me-2 ms-2">
                        <div class="avatar-title bg-primary text-white rounded-circle fs-13">
                            <i class="ri-add-line"></i>
                        </div>
                    </div>
                    <h5 class="modal-title fw-bold">{{ __('tickets::messages.add_category') }}</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form action="{{ route('admin.tickets.categories.store') }}" method="POST" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="cat-name" class="form-label fw-bold"><i class="ri-edit-2-line me-1 text-primary"></i> {{ __('tickets::messages.name') }}</label>
                                <input type="text" id="cat-name" name="name" class="form-control bg-light border-0" placeholder="{{ __('tickets::messages.name') }}" required />
                                <div class="form-text text-muted small">{{ __('tickets::messages.category_name_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="stage-id" class="form-label fw-bold"><i class="ri-stack-line me-1 text-primary"></i> {{ __('tickets::messages.stage') }}</label>
                                <select name="stage_id" id="stage-id" class="form-select select2-modal" required>
                                    <option value="">{{ __('tickets::messages.lookups.stages') }}...</option>
                                    @foreach($stages as $stage)
                                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">{{ __('tickets::messages.category_stage_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="sla-hours" class="form-label fw-bold"><i class="ri-timer-2-line me-1 text-primary"></i> {{ __('tickets::messages.sla_countdown') }}</label>
                                <div class="input-group">
                                    <input type="number" id="sla-hours" name="sla_hours" class="form-control bg-light border-0" placeholder="0" />
                                    <span class="input-group-text bg-light border-0">{{ __('tickets::messages.hours') }}</span>
                                </div>
                                <div class="form-text text-muted small">{{ __('tickets::messages.category_sla_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="group-id" class="form-label fw-bold"><i class="ri-group-line me-1 text-primary"></i> {{ __('tickets::messages.lookups.groups') }}</label>
                                <select name="group_id" id="group-id" class="form-select select2-modal">
                                    <option value="">{{ __('tickets::messages.default') }}</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">{{ __('tickets::messages.stage_group_help') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <div class="hstack gap-2 justify-content-end w-100">
                        <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">{{ __('tickets::messages.cancel') }}</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm"><i class="ri-save-line me-1"></i> {{ __('tickets::messages.save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade zoomIn" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header p-3 bg-soft-info">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs flex-shrink-0 me-2 ms-2">
                        <div class="avatar-title bg-info text-white rounded-circle fs-13">
                            <i class="ri-pencil-line"></i>
                        </div>
                    </div>
                    <h5 class="modal-title fw-bold">{{ __('tickets::messages.edit_category') }}</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="edit_name" class="form-label fw-bold"><i class="ri-edit-2-line me-1 text-info"></i> {{ __('tickets::messages.name') }}</label>
                                <input type="text" name="name" id="edit_name" class="form-control bg-light border-0" required />
                                <div class="form-text text-muted small">{{ __('tickets::messages.category_name_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="edit_stage" class="form-label fw-bold"><i class="ri-stack-line me-1 text-info"></i> {{ __('tickets::messages.stage') }}</label>
                                <select name="stage_id" id="edit_stage" class="form-select select2-modal" required>
                                    @foreach($stages as $stage)
                                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">{{ __('tickets::messages.category_stage_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="edit_sla" class="form-label fw-bold"><i class="ri-timer-2-line me-1 text-info"></i> {{ __('tickets::messages.sla_countdown') }}</label>
                                <div class="input-group">
                                    <input type="number" name="sla_hours" id="edit_sla" class="form-control bg-light border-0" />
                                    <span class="input-group-text bg-light border-0">{{ __('tickets::messages.hours') }}</span>
                                </div>
                                <div class="form-text text-muted small">{{ __('tickets::messages.category_sla_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="edit_group" class="form-label fw-bold"><i class="ri-group-line me-1 text-info"></i> {{ __('tickets::messages.lookups.groups') }}</label>
                                <select name="group_id" id="edit_group" class="form-select select2-modal">
                                    <option value="">{{ __('tickets::messages.default') }}</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">{{ __('tickets::messages.stage_group_help') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <div class="hstack gap-2 justify-content-end w-100">
                        <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">{{ __('tickets::messages.cancel') }}</button>
                        <button type="submit" class="btn btn-info px-4 fw-bold shadow-sm"><i class="ri-save-line me-1"></i> {{ __('tickets::messages.save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@include('tickets::partials.delete-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2 for modals
        function initSelect2() {
            $('.select2-modal').each(function() {
                $(this).select2({
                    dropdownParent: $(this).closest('.modal'),
                    width: '100%'
                });
            });
        }

        initSelect2();

        const editButtons = document.querySelectorAll('.edit-item-btn');
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const stage = this.dataset.stage;
                const sla = this.dataset.sla;
                const group = this.dataset.group;

                document.getElementById('editCategoryForm').action = `/admin/tickets/categories/${id}`;
                document.getElementById('edit_name').value = name;
                $('#edit_stage').val(stage).trigger('change');
                document.getElementById('edit_sla').value = sla;
                $('#edit_group').val(group).trigger('change');
            });
        });
    });
</script>
<style>
    .select2-container--default .select2-selection--single {
        background-color: #f3f6f9 !important;
        border: none !important;
        height: 38px !important;
        line-height: 38px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        padding-left: 12px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
    .modal-lg { max-width: 800px; }
</style>
@endpush

@endsection
