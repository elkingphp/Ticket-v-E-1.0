@extends('core::layouts.master')

@section('title', __('tickets::messages.lookups.stages'))

@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="card" id="stageList">
            <div class="card-header border-0 mt-2">
                <div class="row align-items-center gy-3">
                    <div class="col-sm">
                        <h5 class="card-title mb-0">{{ __('tickets::messages.records') }}</h5>
                    </div>
                    <div class="col-sm-auto">
                        <div class="d-flex gap-1 flex-wrap">
                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" id="create-btn" data-bs-target="#addStageModal">
                                <i class="ri-add-line align-bottom me-1"></i> {{ __('tickets::messages.add_stage') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body border border-dashed border-start-0 border-end-0">
                <form action="{{ route('admin.tickets.stages.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-xxl-5 col-sm-12">
                            <div class="search-box position-relative">
                                <input type="text" name="search" class="form-control search bg-light border-light ps-4 pe-5" placeholder="{{ __('tickets::messages.search') }}..." value="{{ request('search') }}">
                                <i class="ri-search-line search-icon position-absolute top-50 translate-middle-y start-0 ms-3 text-muted"></i>
                                @if(request('search'))
                                    <a href="{{ route('admin.tickets.stages.index') }}" class="position-absolute top-50 translate-middle-y end-0 me-3 text-danger">
                                        <i class="ri-close-circle-line fs-16"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-auto">
                            <button type="submit" class="btn btn-primary d-none">{{ __('tickets::messages.search') }}</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div>
                    <div class="table-responsive table-card mb-1">
                        <table class="table align-middle table-nowrap" id="stageTable">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th style="width: 50px;">{{ __('tickets::messages.id') }}</th>
                                    <th>{{ __('tickets::messages.name') }}</th>
                                    <th>{{ __('tickets::messages.sla_countdown') }} ({{ __('tickets::messages.replies') }})</th>
                                    <th>{{ __('tickets::messages.lookups.groups') }}</th>
                                    <th style="width: 150px;">{{ __('tickets::messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list form-check-all">
                                @foreach($stages as $stage)
                                <tr>
                                    <td>{{ $stage->id }}</td>
                                    <td class="fw-medium text-primary">{{ $stage->name }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="ri-timer-line text-info me-2 fs-18"></i>
                                            <span class="badge bg-info-subtle text-info fs-12">{{ $stage->sla_hours ?? '-' }} {{ __('tickets::messages.hours') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($stage->routing && $stage->routing->group)
                                            <span class="badge bg-primary-subtle text-primary fs-11">{{ $stage->routing->group->name }}</span>
                                        @else
                                            <span class="badge bg-light text-muted fs-11">{{ __('tickets::messages.default') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <ul class="list-inline hstack gap-2 mb-0">
                                            <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Edit">
                                                <button class="btn btn-soft-info btn-sm edit-item-btn" 
                                                    data-bs-toggle="modal" data-bs-target="#editStageModal"
                                                    data-id="{{ $stage->id }}"
                                                    data-name="{{ $stage->name }}"
                                                    data-sla="{{ $stage->sla_hours }}"
                                                    data-group="{{ $stage->routing ? $stage->routing->group_id : '' }}">
                                                    <i class="ri-pencil-fill align-bottom"></i>
                                                </button>
                                            </li>
                                            <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Remove">
                                                <button class="btn btn-soft-danger btn-sm remove-item-btn" onclick="deleteItem('{{ route('admin.tickets.stages.destroy', $stage->id) }}')">
                                                    <i class="ri-delete-bin-fill align-bottom"></i>
                                                </button>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($stages->isEmpty())
                        <div class="noresult" style="display: block">
                            <div class="text-center py-5">
                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#405189,secondary:#0ab39c" style="width:90px;height:90px"></lord-icon>
                                <h5 class="mt-3 fw-bold text-muted">{{ __('tickets::messages.no_data_found') }}</h5>
                                <p class="text-muted mb-0">{{ __('tickets::messages.no_results_help') ?? 'لم يتم العثور على أي نتائج، ابدأ بإضافة سجل جديد.' }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    @if($stages->hasPages())
                    <div class="d-flex justify-content-end mt-3">
                        {{ $stages->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade zoomIn" id="addStageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header p-4 bg-soft-primary">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs flex-shrink-0 me-2 ms-2">
                        <div class="avatar-title bg-primary text-white rounded-circle fs-13">
                            <i class="ri-add-line"></i>
                        </div>
                    </div>
                    <h5 class="modal-title fw-bold text-dark">{{ __('tickets::messages.add_stage') }}</h5>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form action="{{ route('admin.tickets.stages.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="stage-name" class="form-label fw-bold"><i class="ri-node-tree me-1 text-primary"></i> {{ __('tickets::messages.name') }}</label>
                                <input type="text" id="stage-name" name="name" class="form-control bg-light border-0 py-2" placeholder="{{ __('tickets::messages.name') }}" required />
                                <div class="form-text text-muted small">{{ __('tickets::messages.stage_name_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="sla-hours" class="form-label fw-bold"><i class="ri-timer-2-line me-1 text-primary"></i> {{ __('tickets::messages.sla_countdown') }}</label>
                                <div class="input-group">
                                    <input type="number" id="sla-hours" name="sla_hours" class="form-control bg-light border-0 py-2" placeholder="0" />
                                    <span class="input-group-text bg-light border-0 py-2">{{ __('tickets::messages.hours') }}</span>
                                </div>
                                <div class="form-text text-muted small">{{ __('tickets::messages.stage_sla_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-0">
                                <label for="group-id" class="form-label fw-bold"><i class="ri-group-line me-1 text-primary"></i> {{ __('tickets::messages.lookups.groups') }}</label>
                                <select name="group_id" id="group-id" class="form-select bg-light border-0 py-2">
                                    <option value="">{{ __('tickets::messages.default') }} (System Default)</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">{{ __('tickets::messages.stage_group_help') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 border-top-0">
                    <div class="hstack gap-2 justify-content-end w-100">
                        <button type="button" class="btn btn-ghost-secondary shadow-none" data-bs-dismiss="modal">{{ __('tickets::messages.cancel') }}</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm d-flex align-items-center">
                            <i class="ri-save-line me-1"></i> {{ __('tickets::messages.save') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade zoomIn" id="editStageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header p-4 bg-soft-info">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs flex-shrink-0 me-2 ms-2">
                        <div class="avatar-title bg-info text-white rounded-circle fs-13">
                            <i class="ri-pencil-line"></i>
                        </div>
                    </div>
                    <h5 class="modal-title fw-bold text-dark">{{ __('tickets::messages.edit_stage') }}</h5>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editStageForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="edit_name" class="form-label fw-bold"><i class="ri-node-tree me-1 text-info"></i> {{ __('tickets::messages.name') }}</label>
                                <input type="text" name="name" id="edit_name" class="form-control bg-light border-0 py-2" required />
                                <div class="form-text text-muted small">{{ __('tickets::messages.stage_name_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="edit_sla" class="form-label fw-bold"><i class="ri-timer-2-line me-1 text-info"></i> {{ __('tickets::messages.sla_countdown') }}</label>
                                <div class="input-group">
                                    <input type="number" name="sla_hours" id="edit_sla" class="form-control bg-light border-0 py-2" />
                                    <span class="input-group-text bg-light border-0 py-2">{{ __('tickets::messages.hours') }}</span>
                                </div>
                                <div class="form-text text-muted small">{{ __('tickets::messages.stage_sla_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-0">
                                <label for="edit_group" class="form-label fw-bold"><i class="ri-group-line me-1 text-info"></i> {{ __('tickets::messages.lookups.groups') }}</label>
                                <select name="group_id" id="edit_group" class="form-select bg-light border-0 py-2">
                                    <option value="">{{ __('tickets::messages.default') }} (System Default)</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">{{ __('tickets::messages.stage_group_help') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 border-top-0">
                    <div class="hstack gap-2 justify-content-end w-100">
                        <button type="button" class="btn btn-ghost-secondary shadow-none" data-bs-dismiss="modal">{{ __('tickets::messages.cancel') }}</button>
                        <button type="submit" class="btn btn-info px-4 fw-bold shadow-sm d-flex align-items-center text-white">
                            <i class="ri-save-line me-1"></i> {{ __('tickets::messages.save') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
@include('tickets::partials.delete-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-item-btn');
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const sla = this.dataset.sla;
                const group = this.dataset.group;

                document.getElementById('editStageForm').action = `/admin/tickets/stages/${id}`;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_sla').value = (sla && sla !== 'null') ? sla : '';
                document.getElementById('edit_group').value = group;
            });
        });
    });
</script>
@endpush

@endsection
