@extends('core::layouts.master')

@section('title', __('tickets::messages.lookups.complaints'))

@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="card" id="complaintList">
            <div class="card-header border-0 mt-2">
                <div class="row align-items-center gy-3">
                    <div class="col-sm">
                        <h5 class="card-title mb-0">{{ __('tickets::messages.records') }}</h5>
                    </div>
                    <div class="col-sm-auto">
                        <div class="d-flex gap-1 flex-wrap">
                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" id="create-btn" data-bs-target="#addComplaintModal">
                                <i class="ri-add-line align-bottom me-1"></i> {{ __('tickets::messages.add_complaint') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body border border-dashed border-start-0 border-end-0">
                <form action="{{ route('admin.tickets.complaints.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-xxl-5 col-sm-12">
                            <div class="search-box position-relative">
                                <input type="text" name="search" class="form-control search bg-light border-light ps-4 pe-5" placeholder="{{ __('tickets::messages.search') }}..." value="{{ request('search') }}">
                                <i class="ri-search-line search-icon position-absolute top-50 translate-middle-y start-0 ms-3 text-muted"></i>
                                @if(request('search'))
                                    <a href="{{ route('admin.tickets.complaints.index') }}" class="position-absolute top-50 translate-middle-y end-0 me-3 text-danger">
                                        <i class="ri-close-circle-line fs-16"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-xxl-3 col-sm-12">
                            <select name="category_id" class="form-select bg-light border-light" onchange="this.form.submit()">
                                <option value="">{{ __('tickets::messages.all_categories') ?? 'جميع التصنيفات' }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div>
                    <div class="table-responsive table-card mb-1">
                        <table class="table align-middle table-nowrap" id="complaintTable">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th style="width: 50px;">{{ __('tickets::messages.id') }}</th>
                                    <th>{{ __('tickets::messages.complaint') }}</th>
                                    <th>{{ __('tickets::messages.category') }}</th>
                                    <th>{{ __('tickets::messages.sub_complaints') }}</th>
                                    <th style="width: 150px;">{{ __('tickets::messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list form-check-all">
                                @foreach($complaints as $complaint)
                                <tr>
                                    <td>{{ $complaint->id }}</td>
                                    <td class="fw-medium text-primary">{{ $complaint->name }}</td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <span class="badge bg-info-subtle text-info fs-12 w-fit-content">{{ $complaint->category?->name ?? '-' }}</span>
                                            <span class="text-muted small"><i class="ri-stack-line me-1"></i>{{ $complaint->category?->stage?->name ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @forelse($complaint->subComplaints as $sub)
                                                <span class="badge border border-light text-muted fs-11">{{ $sub->name }}</span>
                                            @empty
                                                <span class="text-muted fs-11 small italic">No sub-items</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td>
                                        <ul class="list-inline hstack gap-2 mb-0">
                                            <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Edit">
                                                <button class="btn btn-soft-info btn-sm edit-item-btn" 
                                                    data-bs-toggle="modal" data-bs-target="#editComplaintModal"
                                                    data-id="{{ $complaint->id }}"
                                                    data-name="{{ $complaint->name }}"
                                                    data-category="{{ $complaint->category_id }}"
                                                    data-sla="{{ $complaint->sla_hours }}"
                                                    data-group="{{ $complaint->routing ? $complaint->routing->group_id : '' }}"
                                                    data-subs="{{ json_encode($complaint->subComplaints->pluck('name')) }}">
                                                    <i class="ri-pencil-fill align-bottom"></i>
                                                </button>
                                            </li>
                                            <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Remove">
                                                <button class="btn btn-soft-danger btn-sm remove-item-btn" onclick="deleteItem('{{ route('admin.tickets.complaints.destroy', $complaint->id) }}')">
                                                    <i class="ri-delete-bin-fill align-bottom"></i>
                                                </button>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($complaints->isEmpty())
                        <div class="noresult" style="display: block">
                            <div class="text-center py-5">
                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#405189,secondary:#0ab39c" style="width:90px;height:90px"></lord-icon>
                                <h5 class="mt-3 fw-bold text-muted">{{ __('tickets::messages.no_data_found') }}</h5>
                                <p class="text-muted mb-0">{{ __('tickets::messages.no_results_help') ?? 'لم يتم العثور على أي نتائج، ابدأ بإضافة سجل جديد.' }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    @if($complaints->hasPages())
                    <div class="d-flex justify-content-end mt-3">
                        {{ $complaints->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade zoomIn" id="addComplaintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header p-4 bg-soft-primary">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs flex-shrink-0 me-2 ms-2">
                        <div class="avatar-title bg-primary text-white rounded-circle fs-13">
                            <i class="ri-add-line"></i>
                        </div>
                    </div>
                    <h5 class="modal-title fw-bold text-dark">{{ __('tickets::messages.add_complaint') }}</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form action="{{ route('admin.tickets.complaints.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="name" class="form-label fw-bold"><i class="ri-edit-2-line me-1 text-primary"></i> {{ __('tickets::messages.name') }}</label>
                                <input type="text" name="name" class="form-control bg-light border-0 py-2" placeholder="{{ __('tickets::messages.name') }}" required />
                                <div class="form-text text-muted small">{{ __('tickets::messages.complaint_name_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="sla-hours" class="form-label fw-bold"><i class="ri-timer-2-line me-1 text-primary"></i> {{ __('tickets::messages.sla_countdown') }}</label>
                                <div class="input-group">
                                    <input type="number" id="sla-hours" name="sla_hours" class="form-control bg-light border-0 py-2" placeholder="0" />
                                    <span class="input-group-text bg-light border-0 py-2">{{ __('tickets::messages.hours') }}</span>
                                </div>
                                <div class="form-text text-muted small">{{ __('tickets::messages.category_sla_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="category_id" class="form-label fw-bold"><i class="ri-folder-open-line me-1 text-primary"></i> {{ __('tickets::messages.category') }}</label>
                                <select name="category_id" class="form-select select2-modal" required>
                                    <option value="">{{ __('tickets::messages.lookups.categories') }}...</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->stage?->name ?? 'N/A' }} &rsaquo; {{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">{{ __('tickets::messages.complaint_category_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="group_id" class="form-label fw-bold"><i class="ri-group-line me-1 text-primary"></i> {{ __('tickets::messages.lookups.groups') }}</label>
                                <select name="group_id" class="form-select select2-modal">
                                    <option value="">{{ __('tickets::messages.default') }} (From Category)</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">{{ __('tickets::messages.stage_group_help') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-top border-top-dashed">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label fw-bold mb-0"><i class="ri-list-settings-line me-1 text-primary"></i> {{ __('tickets::messages.sub_complaints') }}</label>
                            <button class="btn btn-sm btn-soft-success rounded-pill px-3 add-sub" type="button"><i class="ri-add-line me-1"></i>Add Sub</button>
                        </div>
                        <div id="sub_complaints_inputs" class="bg-light bg-opacity-30 rounded-3 p-3 border border-dashed border-light">
                            <div class="input-group mb-2">
                                <input type="text" name="sub_complaints[]" class="form-control bg-white border-light shadow-none" placeholder="{{ __('tickets::messages.name') }}">
                                <button class="btn btn-ghost-danger remove-sub d-none" type="button"><i class="ri-delete-bin-line"></i></button>
                            </div>
                        </div>
                        <div class="form-text text-muted small mt-2">{{ __('tickets::messages.complaint_sub_help') }}</div>
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
<div class="modal fade zoomIn" id="editComplaintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header p-4 bg-soft-info">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs flex-shrink-0 me-2 ms-2">
                        <div class="avatar-title bg-info text-white rounded-circle fs-13">
                            <i class="ri-pencil-line"></i>
                        </div>
                    </div>
                    <h5 class="modal-title fw-bold text-dark">{{ __('tickets::messages.edit_complaint') }}</h5>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editComplaintForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="edit_name" class="form-label fw-bold"><i class="ri-edit-2-line me-1 text-info"></i> {{ __('tickets::messages.name') }}</label>
                                <input type="text" name="name" id="edit_name" class="form-control bg-light border-0 py-2" required />
                                <div class="form-text text-muted small">{{ __('tickets::messages.complaint_name_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="edit_sla" class="form-label fw-bold"><i class="ri-timer-2-line me-1 text-info"></i> {{ __('tickets::messages.sla_countdown') }}</label>
                                <div class="input-group">
                                    <input type="number" name="sla_hours" id="edit_sla" class="form-control bg-light border-0 py-2" />
                                    <span class="input-group-text bg-light border-0 py-2">{{ __('tickets::messages.hours') }}</span>
                                </div>
                                <div class="form-text text-muted small">{{ __('tickets::messages.category_sla_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="edit_category" class="form-label fw-bold"><i class="ri-folder-open-line me-1 text-info"></i> {{ __('tickets::messages.category') }}</label>
                                <select name="category_id" id="edit_category" class="form-select select2-modal" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->stage?->name ?? 'N/A' }} &rsaquo; {{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">{{ __('tickets::messages.complaint_category_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="edit_group" class="form-label fw-bold"><i class="ri-group-line me-1 text-info"></i> {{ __('tickets::messages.lookups.groups') }}</label>
                                <select name="group_id" id="edit_group" class="form-select select2-modal">
                                    <option value="">{{ __('tickets::messages.default') }} (From Category)</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">{{ __('tickets::messages.stage_group_help') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-top border-top-dashed">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label fw-bold mb-0"><i class="ri-list-settings-line me-1 text-info"></i> {{ __('tickets::messages.sub_complaints') }}</label>
                            <button class="btn btn-sm btn-soft-success rounded-pill px-3 add-edit-sub" type="button"><i class="ri-add-line me-1"></i>Add Sub</button>
                        </div>
                        <div id="edit_sub_complaints_inputs" class="bg-light bg-opacity-30 rounded-3 p-3 border border-dashed border-light">
                            <!-- Populated via JS -->
                        </div>
                        <div class="form-text text-muted small mt-2">{{ __('tickets::messages.complaint_sub_help') }}</div>
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

        // Dynamic sub-complaints logic for Add Modal
        document.querySelectorAll('.add-sub').forEach(btn => {
            btn.addEventListener('click', function() {
                const container = document.getElementById('sub_complaints_inputs');
                addSubInput(container);
            });
        });

        // Dynamic sub-complaints logic for Edit Modal
        document.querySelectorAll('.add-edit-sub').forEach(btn => {
            btn.addEventListener('click', function() {
                const container = document.getElementById('edit_sub_complaints_inputs');
                addSubInput(container);
            });
        });

        function addSubInput(container, value = '') {
            const div = document.createElement('div');
            div.className = 'input-group mb-2 animate__animated animate__fadeInDown';
            div.innerHTML = `
                <input type="text" name="sub_complaints[]" class="form-control bg-white border-light shadow-none" placeholder="{{ __('tickets::messages.name') }}" value="${value}">
                <button class="btn btn-ghost-danger remove-sub" type="button"><i class="ri-delete-bin-line"></i></button>
            `;
            container.appendChild(div);
            
            div.querySelector('.remove-sub').onclick = () => {
                div.classList.replace('animate__fadeInDown', 'animate__fadeOutUp');
                setTimeout(() => div.remove(), 400);
            };
        }

        // Edit button data re-population
        const editButtons = document.querySelectorAll('.edit-item-btn');
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const category = this.dataset.category;
                const sla = this.dataset.sla;
                const group = this.dataset.group;
                const subs = JSON.parse(this.dataset.subs || '[]');

                document.getElementById('editComplaintForm').action = `/admin/tickets/complaints/${id}`;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_sla').value = sla;
                $('#edit_category').val(category).trigger('change');
                $('#edit_group').val(group).trigger('change');

                // Repopulate sub-complaints
                const subContainer = document.getElementById('edit_sub_complaints_inputs');
                subContainer.innerHTML = '';
                if (subs.length > 0) {
                    subs.forEach(sub => addSubInput(subContainer, sub));
                } else {
                    addSubInput(subContainer); // Add one empty input by default
                }
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
    [dir="rtl"] .me-1, [dir="rtl"] .me-2, [dir="rtl"] .me-3 { margin-left: 0.5rem !important; margin-right: 0 !important; }
    [dir="rtl"] .ms-1, [dir="rtl"] .ms-2, [dir="rtl"] .ms-3 { margin-right: 0.5rem !important; margin-left: 0 !important; }
</style>
@endpush

@endsection
