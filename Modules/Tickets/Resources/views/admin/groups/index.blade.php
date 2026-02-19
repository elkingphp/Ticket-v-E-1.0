@extends('core::layouts.master')

@section('title', __('tickets::messages.lookups.groups'))

@section('content')
<div class="container-fluid">
<div class="row">
    <div class="col-lg-12">
        <div class="card" id="groupList">
            <div class="card-header border-0 mt-2">
                <div class="row align-items-center gy-3">
                    <div class="col-sm">
                        <h5 class="card-title mb-0">{{ __('tickets::messages.lookups.groups') }}</h5>
                    </div>
                    <div class="col-sm-auto">
                        <div class="d-flex gap-1 flex-wrap">
                            <button class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addGroupModal">
                                <i class="ri-add-line align-bottom me-1"></i> {{ __('tickets::messages.add_group') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body border border-dashed border-start-0 border-end-0">
                <form action="{{ route('admin.tickets.groups.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-xxl-5 col-sm-12">
                            <div class="search-box position-relative">
                                <input type="text" name="search" class="form-control search bg-light border-light ps-4 pe-5" 
                                       placeholder="{{ __('tickets::messages.search') }}..." value="{{ request('search') }}">
                                <i class="ri-search-line search-icon position-absolute top-50 translate-middle-y start-0 ms-3 text-muted"></i>
                                @if(request('search'))
                                    <a href="{{ route('admin.tickets.groups.index') }}" class="position-absolute top-50 translate-middle-y end-0 me-3 text-danger">
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
                        <table class="table align-middle table-nowrap" id="groupTable">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th style="width: 50px;">{{ __('tickets::messages.id') }}</th>
                                    <th>{{ __('tickets::messages.name') }}</th>
                                    <th>{{ __('tickets::messages.members') }}</th>
                                    <th>{{ __('tickets::messages.default') }}</th>
                                    <th style="width: 150px;">{{ __('tickets::messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list form-check-all">
                                @forelse($groups as $group)
                                <tr>
                                    <td>{{ $group->id }}</td>
                                    <td class="fw-medium text-primary">{{ $group->name }}</td>
                                    <td>
                                        <div class="avatar-group hstack gap-n1">
                                            @foreach($group->members->take(5) as $member)
                                                <div class="avatar-group-item shadow-sm border border-2 border-white rounded-circle" 
                                                   data-bs-toggle="tooltip" data-bs-placement="top" 
                                                   title="{{ $member->full_name }} {{ $member->pivot->is_leader ? '('.__('tickets::messages.leader').')' : '' }}">
                                                    <div class="avatar-xxs">
                                                        @if($member->avatar)
                                                            <img src="{{ Storage::url($member->avatar) }}" class="rounded-circle img-fluid">
                                                        @else
                                                            <span class="avatar-title rounded-circle bg-{{ $member->pivot->is_leader ? 'warning' : 'info' }} text-white fs-10 fw-bold">
                                                                {{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}
                                                            </span>
                                                        @endif
                                                        @if($member->pivot->is_leader)
                                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-warning p-1 border border-white shadow-sm" style="margin-top: -1px; margin-left: -1px;">
                                                                <i class="ri-vip-crown-fill text-white fs-8"></i>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if($group->members->count() > 5)
                                                <div class="avatar-group-item">
                                                    <div class="avatar-xxs">
                                                        <div class="avatar-title rounded-circle bg-primary-subtle text-primary fs-10 fw-bold border border-2 border-white">
                                                            +{{ $group->members->count() - 5 }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($group->members->count() == 0)
                                                <span class="text-muted fs-12 fw-medium opacity-50">{{ __('tickets::messages.none') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($group->is_default)
                                            <span class="badge bg-success-subtle text-success fs-11 px-3 py-1 rounded-pill">
                                                <i class="ri-checkbox-circle-fill me-1 align-middle"></i> {{ __('tickets::messages.active') }}
                                            </span>
                                        @else
                                            <span class="text-muted opacity-50 small italic">{{ __('tickets::messages.none') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <ul class="list-inline hstack gap-2 mb-0">
                                            <li class="list-inline-item">
                                                <button class="btn btn-soft-info btn-sm edit-item-btn" 
                                                    data-id="{{ $group->id }}"
                                                    data-name="{{ $group->name }}"
                                                    data-default="{{ $group->is_default }}"
                                                    data-members="{{ json_encode($group->members->pluck('id')) }}"
                                                    data-leader="{{ $group->members->where('pivot.is_leader', true)->first()?->id }}">
                                                    <i class="ri-pencil-fill"></i>
                                                </button>
                                            </li>
                                            <li class="list-inline-item">
                                                <button class="btn btn-soft-danger btn-sm" onclick="confirmDeleteGroup('{{ route('admin.tickets.groups.destroy', $group->id) }}')">
                                                    <i class="ri-delete-bin-fill"></i>
                                                </button>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="noresult">
                                            <div class="text-center">
                                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#405189,secondary:#0ab39c" style="width:75px;height:75px"></lord-icon>
                                                <h5 class="mt-2">{{ __('tickets::messages.no_data_found') }}</h5>
                                                <p class="text-muted mb-0">{{ __('tickets::messages.no_results_help') ?? 'لم يتم العثور على أي نتائج، ابدأ بإضافة سجل جديد.' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($groups->hasPages())
                <div class="card-footer border-0 py-3">
                    <div class="d-flex justify-content-end">
                        {{ $groups->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade overflow-hidden" id="addGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header p-4 bg-soft-primary">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs flex-shrink-0 me-2 ms-2">
                        <div class="avatar-title bg-primary text-white rounded-circle fs-13">
                            <i class="ri-add-line"></i>
                        </div>
                    </div>
                    <h5 class="modal-title fw-bold text-dark">{{ __('tickets::messages.add_group') }}</h5>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form action="{{ route('admin.tickets.groups.store') }}" method="POST" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Group Name -->
                        <div class="col-md-12">
                            <div class="mb-0">
                                <label class="form-label fw-bold"><i class="ri-edit-2-line me-1 text-primary"></i> {{ __('tickets::messages.name') }}</label>
                                <input type="text" name="name" class="form-control bg-light border-0 py-2" placeholder="{{ __('tickets::messages.name') }}" required>
                                <div class="form-text text-muted small">{{ __('tickets::messages.group_name_help') }}</div>
                            </div>
                        </div>

                        <!-- Members Selection -->
                        <div class="col-md-12">
                            <div class="mb-0">
                                <label class="form-label fw-bold"><i class="ri-team-line me-1 text-primary"></i> {{ __('tickets::messages.members') }}</label>
                                <select name="members[]" class="form-select select2-modal" multiple id="add_members_select" data-placeholder="{{ __('tickets::messages.members') }}">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">{{ __('tickets::messages.group_members_help') }}</div>
                            </div>
                        </div>

                        <!-- Leader Selection -->
                        <div class="col-md-12">
                            <div class="mb-0">
                                <label class="form-label fw-bold"><i class="ri-vip-crown-line me-1 text-primary"></i> {{ __('tickets::messages.leader') }}</label>
                                <select name="leader_id" class="form-select select2-modal" id="add_leader_select" data-placeholder="{{ __('tickets::messages.none') }}">
                                    <option value="">{{ __('tickets::messages.none') }}</option>
                                </select>
                                <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info fs-12 mt-2 mb-0 p-2">
                                    <i class="ri-information-line me-2 align-middle"></i> {{ __('tickets::messages.leader_help') }}
                                </div>
                            </div>
                        </div>

                        <!-- Default Toggle -->
                        <div class="col-md-12">
                            <div class="p-3 rounded-3 bg-light border border-dashed border-primary border-opacity-10">
                                <div class="form-check form-switch form-switch-lg form-switch-success mb-0 d-flex align-items-center px-0">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" name="is_default" value="1" id="addDefaultSwitch">
                                    <label class="form-check-label fw-bold text-dark fs-14 mb-0 cursor-pointer" for="addDefaultSwitch">{{ __('tickets::messages.default') }}</label>
                                </div>
                                <div class="form-text text-muted small mt-2">{{ __('tickets::messages.group_default_help') }}</div>
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
<div class="modal fade overflow-hidden" id="editGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header p-4 bg-soft-info">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs flex-shrink-0 me-2 ms-2">
                        <div class="avatar-title bg-info text-white rounded-circle fs-13">
                            <i class="ri-pencil-line"></i>
                        </div>
                    </div>
                    <h5 class="modal-title fw-bold text-dark">{{ __('tickets::messages.edit_group') }}</h5>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editGroupForm" method="POST" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Group Name -->
                        <div class="col-md-12">
                            <div class="mb-0">
                                <label class="form-label fw-bold"><i class="ri-edit-2-line me-1 text-info"></i> {{ __('tickets::messages.name') }}</label>
                                <input type="text" name="name" id="edit_name" class="form-control bg-light border-0 py-2" required>
                                <div class="form-text text-muted small">{{ __('tickets::messages.group_name_help') }}</div>
                            </div>
                        </div>

                        <!-- Members Selection -->
                        <div class="col-md-12">
                            <div class="mb-0">
                                <label class="form-label fw-bold"><i class="ri-team-line me-1 text-info"></i> {{ __('tickets::messages.members') }}</label>
                                <select name="members[]" id="edit_members" class="form-select select2-modal" multiple data-placeholder="{{ __('tickets::messages.members') }}">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">{{ __('tickets::messages.group_members_help') }}</div>
                            </div>
                        </div>

                        <!-- Leader Selection -->
                        <div class="col-md-12">
                            <div class="mb-0">
                                <label class="form-label fw-bold"><i class="ri-vip-crown-line me-1 text-info"></i> {{ __('tickets::messages.leader') }}</label>
                                <select name="leader_id" class="form-select select2-modal" id="edit_leader_select" data-placeholder="{{ __('tickets::messages.none') }}">
                                    <option value="">{{ __('tickets::messages.none') }}</option>
                                </select>
                                <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info fs-12 mt-2 mb-0 p-2">
                                    <i class="ri-information-line me-2 align-middle"></i> {{ __('tickets::messages.leader_help') }}
                                </div>
                            </div>
                        </div>

                        <!-- Default Toggle -->
                        <div class="col-md-12">
                            <div class="p-3 rounded-3 bg-light border border-dashed border-info border-opacity-10">
                                <div class="form-check form-switch form-switch-lg form-switch-success mb-0 d-flex align-items-center px-0">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" name="is_default" id="edit_default" value="1">
                                    <label class="form-check-label fw-bold text-dark fs-14 mb-0 cursor-pointer" for="edit_default">{{ __('tickets::messages.default') }}</label>
                                </div>
                                <div class="form-text text-muted small mt-2">{{ __('tickets::messages.group_default_help') }}</div>
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}">

<script>
    function confirmDeleteGroup(url) {
        Swal.fire({
            title: "{{ __('tickets::messages.confirm_delete') }}",
            text: "{{ __('tickets::messages.delete_group_warning') }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f06548',
            cancelButtonColor: '#212529',
            confirmButtonText: "{{ __('tickets::messages.delete_group') }}",
            cancelButtonText: "{{ __('tickets::messages.cancel') }}",
            customClass: {
                popup: 'rounded-4 border-0 shadow-lg',
                confirmButton: 'btn btn-danger rounded-pill px-4 fw-bold shadow-sm',
                cancelButton: 'btn btn-ghost-secondary rounded-pill px-4 fw-bold shadow-none me-2'
            },
            buttonsStyling: false,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const users = @json($users);
        
        function updateLeaderOptions(membersSelectId, leaderSelectId, selectedLeaderId = null) {
            const selectedMembers = $(membersSelectId).val() || [];
            const leaderSelect = $(leaderSelectId);
            const currentVal = selectedLeaderId || leaderSelect.val();
            
            leaderSelect.empty().append('<option value="">{{ __("tickets::messages.none") }}</option>');
            
            selectedMembers.forEach(memberId => {
                const user = users.find(u => u.id == memberId);
                if (user) {
                    const isSelected = (memberId == currentVal);
                    const displayName = user.full_name || (user.first_name + ' ' + (user.last_name || ''));
                    leaderSelect.append(new Option(displayName, memberId, isSelected, isSelected));
                }
            });
            leaderSelect.trigger('change.select2');
        }

        function initSelect2() {
            $('.select2-modal').each(function() {
                const placeholder = $(this).data('placeholder');
                $(this).select2({
                    dropdownParent: $(this).closest('.modal'),
                    placeholder: placeholder,
                    allowClear: !$(this).prop('multiple'),
                    width: '100%'
                });
            });
        }

        initSelect2();

        $('#add_members_select').on('change', function() {
            updateLeaderOptions('#add_members_select', '#add_leader_select');
        });

        $('#edit_members').on('change', function() {
            const leaderId = $('#edit_leader_select').val();
            updateLeaderOptions('#edit_members', '#edit_leader_select', leaderId);
        });

        // Expert-level fix for Support Group Edit action and modal handling
        $(document).on('click', '.edit-item-btn', function(e) {
            e.preventDefault();
            
            const btn = $(this);
            const id = btn.attr('data-id');
            const name = btn.attr('data-name');
            const isDefault = btn.attr('data-default') == '1';
            const leaderId = btn.attr('data-leader');
            let members = btn.attr('data-members');

            if (!id) return;

            // Safely handle members data
            if (typeof members === 'string') {
                try {
                    members = JSON.parse(members);
                } catch (err) {
                    members = [];
                }
            }

            // Target the form and modal
            const editModal = $('#editGroupModal');
            const form = editModal.find('form');
            const baseUrl = "{{ url('admin/tickets/groups') }}";
            
            // CRITICAL: Set the explicit URL for the PUT request
            form.attr('action', baseUrl + '/' + id);
            
            // Set field values
            $('#edit_name').val(name);
            $('#edit_default').prop('checked', isDefault);
            
            // Re-populate members and leader
            $('#edit_members').val(members).trigger('change');
            
            setTimeout(() => {
                updateLeaderOptions('#edit_members', '#edit_leader_select', leaderId);
            }, 350);

            // Show the modal
            editModal.modal('show');
        });
    });
</script>
<style>
    .select2-container--default .select2-selection--multiple,
    .select2-container--default .select2-selection--single {
        background-color: #f3f6f9 !important;
        border: 1px solid #e9ebec !important;
        border-radius: 8px !important;
        padding: 4px !important;
        min-height: 40px;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        background-color: #fff !important;
        border-color: var(--vz-primary) !important;
    }
    
    .select2-dropdown {
        border-radius: 12px !important;
        border: 1px solid #e9ebec !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
        overflow: hidden !important;
        z-index: 9999 !important;
    }

    .avatar-group-item {
        position: relative;
        transition: all 0.2s ease;
    }
    .avatar-group-item:hover {
        transform: translateY(-3px) scale(1.1);
        z-index: 10;
    }

    [dir="rtl"] .me-1, [dir="rtl"] .me-2, [dir="rtl"] .me-3 { margin-left: 0.5rem !important; margin-right: 0 !important; }
    [dir="rtl"] .ms-1, [dir="rtl"] .ms-2, [dir="rtl"] .ms-3 { margin-right: 0.5rem !important; margin-left: 0 !important; }
    [dir="rtl"] .form-switch { padding-left: 0 !important; padding-right: 3.5rem !important; }
    [dir="rtl"] .form-switch .form-check-input { float: right !important; margin-right: -3.5rem !important; margin-left: 0 !important; }
</style>
@endpush
@endsection
