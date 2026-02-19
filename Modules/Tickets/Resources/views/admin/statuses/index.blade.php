@extends('core::layouts.master')

@section('title', __('tickets::messages.lookups.statuses'))

@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="card" id="statusList">
            <div class="card-header border-0 mt-2">
                <div class="row align-items-center gy-3">
                    <div class="col-sm">
                        <h5 class="card-title mb-0">{{ __('tickets::messages.records') }}</h5>
                    </div>
                    <div class="col-sm-auto">
                        <div class="d-flex gap-1 flex-wrap">
                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" id="create-btn" data-bs-target="#addStatusModal">
                                <i class="ri-add-line align-bottom me-1"></i> {{ __('tickets::messages.add_status') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body border border-dashed border-start-0 border-end-0">
                <form action="{{ route('admin.tickets.statuses.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-xxl-5 col-sm-12">
                            <div class="search-box position-relative">
                                <input type="text" name="search" class="form-control search bg-light border-light ps-4 pe-5" placeholder="{{ __('tickets::messages.search') }}..." value="{{ request('search') }}">
                                <i class="ri-search-line search-icon position-absolute top-50 translate-middle-y start-0 ms-3 text-muted"></i>
                                @if(request('search'))
                                    <a href="{{ route('admin.tickets.statuses.index') }}" class="position-absolute top-50 translate-middle-y end-0 me-3 text-danger">
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
                        <table class="table align-middle table-nowrap" id="statusTable">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th style="width: 50px;">{{ __('tickets::messages.id') }}</th>
                                    <th>{{ __('tickets::messages.status') }}</th>
                                    <th>{{ __('tickets::messages.color') }}</th>
                                    <th>{{ __('tickets::messages.default') }}</th>
                                    <th>{{ __('tickets::messages.is_final') }}</th>
                                    <th style="width: 150px;">{{ __('tickets::messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list form-check-all">
                                @foreach($statuses as $status)
                                <tr>
                                    <td>{{ $status->id }}</td>
                                    <td class="fw-medium">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 avatar-xxs me-2 ms-2">
                                                <div class="avatar-title bg-{{ $status->color }}-subtle text-{{ $status->color }} rounded-circle">
                                                    <i class="ri-checkbox-blank-circle-fill"></i>
                                                </div>
                                            </div>
                                            <span class="text-{{ $status->color }} fw-bold">{{ $status->name }}</span>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-{{ $status->color }}-subtle text-{{ $status->color }} text-uppercase">{{ $status->color }}</span></td>
                                    <td>
                                        @if($status->is_default)
                                            <span class="badge badge-label bg-success"><i class="ri-check-double-line label-icon"></i> {{ __('tickets::messages.active') }}</span>
                                        @else
                                            <span class="text-muted small italic">{{ __('tickets::messages.none') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($status->is_final)
                                            <span class="badge badge-label bg-danger"><i class="ri-flag-2-line label-icon"></i> {{ __('tickets::messages.active') }}</span>
                                        @else
                                            <span class="text-muted small italic">{{ __('tickets::messages.none') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <ul class="list-inline hstack gap-2 mb-0">
                                            <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Edit">
                                                <button class="btn btn-soft-info btn-sm edit-item-btn" 
                                                    data-bs-toggle="modal" data-bs-target="#editStatusModal"
                                                    data-id="{{ $status->id }}"
                                                    data-name="{{ $status->name }}"
                                                    data-color="{{ $status->color }}"
                                                    data-default="{{ $status->is_default }}"
                                                    data-final="{{ $status->is_final }}">
                                                    <i class="ri-pencil-fill align-bottom"></i>
                                                </button>
                                            </li>
                                            <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Remove">
                                                <button class="btn btn-soft-danger btn-sm remove-item-btn" onclick="deleteItem('{{ route('admin.tickets.statuses.destroy', $status->id) }}')">
                                                    <i class="ri-delete-bin-fill align-bottom"></i>
                                                </button>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($statuses->isEmpty())
                        <div class="noresult" style="display: block">
                            <div class="text-center py-5">
                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#405189,secondary:#0ab39c" style="width:90px;height:90px"></lord-icon>
                                <h5 class="mt-3 fw-bold text-muted">{{ __('tickets::messages.no_data_found') }}</h5>
                                <p class="text-muted mb-0">{{ __('tickets::messages.no_results_help') ?? 'لم يتم العثور على أي نتائج، ابدأ بإضافة سجل جديد.' }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    @if($statuses->hasPages())
                    <div class="d-flex justify-content-end mt-3">
                        {{ $statuses->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade zoomIn" id="addStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header p-4 bg-soft-primary">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs flex-shrink-0 me-2 ms-2">
                        <div class="avatar-title bg-primary text-white rounded-circle fs-13">
                            <i class="ri-add-line"></i>
                        </div>
                    </div>
                    <h5 class="modal-title fw-bold text-dark">{{ __('tickets::messages.add_status') }}</h5>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form action="{{ route('admin.tickets.statuses.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="status-name" class="form-label fw-bold"><i class="ri-edit-2-line me-1 text-primary"></i> {{ __('tickets::messages.name') }}</label>
                                <input type="text" id="status-name" name="name" class="form-control bg-light border-0 py-2" placeholder="{{ __('tickets::messages.name') }}" required />
                                <div class="form-text text-muted small">{{ __('tickets::messages.status_name_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="color-select" class="form-label fw-bold"><i class="ri-palette-line me-1 text-primary"></i> {{ __('tickets::messages.color') }}</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <select name="color" id="color-select" class="form-select bg-light border-0 py-2 color-picker-select">
                                        <option value="primary">Primary</option>
                                        <option value="secondary">Secondary</option>
                                        <option value="success">Success</option>
                                        <option value="info">Info</option>
                                        <option value="warning">Warning</option>
                                        <option value="danger">Danger</option>
                                        <option value="dark">Dark</option>
                                    </select>
                                    <div class="color-preview avatar-xxs flex-shrink-0 rounded-circle bg-primary shadow-sm"></div>
                                </div>
                                <div class="form-text text-muted small">{{ __('tickets::messages.status_color_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="flex-column">
                                <div class="form-check form-switch form-switch-lg form-switch-success mb-1 px-2 hstack gap-2">
                                    <input class="form-check-input ms-0" type="checkbox" name="is_default" value="1" id="is_default_sw">
                                    <label class="form-check-label fw-bold text-muted mb-0" for="is_default_sw">{{ __('tickets::messages.default') }}</label>
                                </div>
                                <div class="form-text text-muted small px-2">{{ __('tickets::messages.status_default_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="flex-column">
                                <div class="form-check form-switch form-switch-lg form-switch-danger mb-1 px-2 hstack gap-2">
                                    <input class="form-check-input ms-0" type="checkbox" name="is_final" value="1" id="is_final_sw">
                                    <label class="form-check-label fw-bold text-muted mb-0" for="is_final_sw">{{ __('tickets::messages.is_final') }}</label>
                                </div>
                                <div class="form-text text-muted small px-2">{{ __('tickets::messages.status_final_help') }}</div>
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
<div class="modal fade zoomIn" id="editStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header p-4 bg-soft-info">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs flex-shrink-0 me-2 ms-2">
                        <div class="avatar-title bg-info text-white rounded-circle fs-13">
                            <i class="ri-pencil-line"></i>
                        </div>
                    </div>
                    <h5 class="modal-title fw-bold text-dark">{{ __('tickets::messages.edit_status') }}</h5>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editStatusForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="edit_name" class="form-label fw-bold"><i class="ri-edit-2-line me-1 text-info"></i> {{ __('tickets::messages.name') }}</label>
                                <input type="text" name="name" id="edit_name" class="form-control bg-light border-0 py-2" required />
                                <div class="form-text text-muted small">{{ __('tickets::messages.status_name_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-0">
                                <label for="edit_color" class="form-label fw-bold"><i class="ri-palette-line me-1 text-info"></i> {{ __('tickets::messages.color') }}</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <select name="color" id="edit_color" class="form-select bg-light border-0 py-2 color-picker-select">
                                        <option value="primary">Primary</option>
                                        <option value="secondary">Secondary</option>
                                        <option value="success">Success</option>
                                        <option value="info">Info</option>
                                        <option value="warning">Warning</option>
                                        <option value="danger">Danger</option>
                                        <option value="dark">Dark</option>
                                    </select>
                                    <div class="color-preview avatar-xxs flex-shrink-0 rounded-circle bg-info shadow-sm"></div>
                                </div>
                                <div class="form-text text-muted small">{{ __('tickets::messages.status_color_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="flex-column">
                                <div class="form-check form-switch form-switch-lg form-switch-success mb-1 px-2 hstack gap-2">
                                    <input class="form-check-input ms-0" type="checkbox" name="is_default" id="edit_default" value="1">
                                    <label class="form-check-label fw-bold text-muted mb-0" for="edit_default">{{ __('tickets::messages.default') }}</label>
                                </div>
                                <div class="form-text text-muted small px-2">{{ __('tickets::messages.status_default_help') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="flex-column">
                                <div class="form-check form-switch form-switch-lg form-switch-danger mb-1 px-2 hstack gap-2">
                                    <input class="form-check-input ms-0" type="checkbox" name="is_final" id="edit_final" value="1">
                                    <label class="form-check-label fw-bold text-muted mb-0" for="edit_final">{{ __('tickets::messages.is_final') }}</label>
                                </div>
                                <div class="form-text text-muted small px-2">{{ __('tickets::messages.status_final_help') }}</div>
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
        // Color preview logic
        const updateColorPreview = (select) => {
            const color = select.value;
            const preview = select.closest('.d-flex').querySelector('.color-preview');
            preview.className = `color-preview avatar-xxs flex-shrink-0 rounded-circle shadow-sm bg-${color}`;
        };

        document.querySelectorAll('.color-picker-select').forEach(select => {
            select.addEventListener('change', () => updateColorPreview(select));
            updateColorPreview(select);
        });

        const editButtons = document.querySelectorAll('.edit-item-btn');
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const color = this.dataset.color;
                const isDefault = this.dataset.default == '1';
                const isFinal = this.dataset.final == '1';

                document.getElementById('editStatusForm').action = `/admin/tickets/statuses/${id}`;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_color').value = color;
                document.getElementById('edit_default').checked = isDefault;
                document.getElementById('edit_final').checked = isFinal;

                // Trigger color preview update
                document.getElementById('edit_color').dispatchEvent(new Event('change'));
            });
        });
    });
</script>
<style>
    .form-switch-lg .form-check-input { width: 3.5rem; height: 1.75rem; }
    .color-preview { width: 24px; height: 24px; border: 2px solid #fff; }
</style>
@endpush

@endsection
