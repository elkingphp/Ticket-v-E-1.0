@extends('core::layouts.master')

@section('title', __('tickets::messages.support_desk'))

@section('content')

<!-- Stats Widgets -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-bold text-muted text-truncate mb-3 fs-11 tracking-wider">{{ __('tickets::messages.total_tickets') }}</p>
                        <div class="d-flex align-items-center mb-0">
                            <h4 class="fs-22 fw-bold ff-secondary mb-0"><span class="counter-value" data-target="{{ $stats['total'] ?? 0 }}">{{ $stats['total'] ?? 0 }}</span></h4>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-soft-primary text-primary rounded-3 fs-24 shadow-sm">
                                <i class="ri-ticket-2-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-bold text-muted text-truncate mb-3 fs-11 tracking-wider">{{ __('tickets::messages.open_tickets') }}</p>
                        <div class="d-flex align-items-center mb-0">
                            <h4 class="fs-22 fw-bold ff-secondary mb-0"><span class="counter-value" data-target="{{ $stats['open'] ?? 0 }}">{{ $stats['open'] ?? 0 }}</span></h4>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-soft-success text-success rounded-3 fs-24 shadow-sm">
                                <i class="ri-loader-2-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-bold text-muted text-truncate mb-3 fs-11 tracking-wider">{{ __('tickets::messages.unassigned_tickets') }}</p>
                        <div class="d-flex align-items-center mb-0">
                            <h4 class="fs-22 fw-bold ff-secondary mb-0"><span class="counter-value" data-target="{{ $stats['unassigned'] ?? 0 }}">{{ $stats['unassigned'] ?? 0 }}</span></h4>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-soft-warning text-warning rounded-3 fs-24 shadow-sm">
                                <i class="ri-user-unfollow-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate border-0 shadow-sm rounded-4 overflow-hidden h-100 border-start border-4 border-danger">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-bold text-muted text-truncate mb-3 fs-11 tracking-wider">{{ __('tickets::messages.overdue_tickets') }}</p>
                        <div class="d-flex align-items-center mb-0">
                            <h4 class="fs-22 fw-bold ff-secondary mb-0 text-danger"><span class="counter-value" data-target="{{ $stats['overdue'] ?? 0 }}">{{ $stats['overdue'] ?? 0 }}</span></h4>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-soft-danger text-danger rounded-3 fs-24 shadow-sm">
                                <i class="ri-alarm-warning-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header border-0 bg-white p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <h5 class="card-title mb-0 fw-bold fs-18 text-dark d-flex align-items-center">
                        <i class="ri-ticket-2-line me-2 text-primary fs-22"></i>
                        {{ __('tickets::messages.support_desk') }}
                    </h5>
                    <div class="flex-shrink-0">
                        <div class="d-inline-flex bg-light p-1 rounded-pill">
                            <a href="{{ route('agent.tickets.index', ['view' => 'all']) }}" class="btn btn-sm rounded-pill px-3 transition-all {{ request('view') == 'all' || !request('view') ? 'btn-white shadow-sm fw-bold border-0 text-primary' : 'btn-link text-muted' }}">
                                {{ __('tickets::messages.all_tickets') }}
                            </a>
                            <a href="{{ route('agent.tickets.index', ['view' => 'my']) }}" class="btn btn-sm rounded-pill px-3 transition-all {{ request('view') == 'my' ? 'btn-white shadow-sm fw-bold border-0 text-primary' : 'btn-link text-muted' }}">
                                {{ __('tickets::messages.my_tickets') }}
                            </a>
                            <a href="{{ route('agent.tickets.index', ['view' => 'unassigned']) }}" class="btn btn-sm rounded-pill px-3 transition-all {{ request('view') == 'unassigned' ? 'btn-white shadow-sm fw-bold border-0 text-primary' : 'btn-link text-muted' }}">
                                {{ __('tickets::messages.unassigned_tickets') }}
                            </a>
                            <a href="{{ route('agent.tickets.index', ['view' => 'group']) }}" class="btn btn-sm rounded-pill px-3 transition-all {{ request('view') == 'group' ? 'btn-white shadow-sm fw-bold border-0 text-primary' : 'btn-link text-muted' }}">
                                {{ __('tickets::messages.my_group_tickets') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body border-top border-bottom border-light p-4 bg-light bg-opacity-30">
                <form action="{{ route('agent.tickets.index') }}" method="GET">
                    <input type="hidden" name="view" value="{{ request('view') }}">
                    <div class="row g-3">
                        <div class="col-xl-4">
                            <div class="search-box">
                                <input type="text" name="search" class="form-control border-white shadow-sm fs-13 py-2 px-3 rounded-3" placeholder="{{ __('tickets::messages.search') }}" value="{{ request('search') }}">
                                <i class="ri-search-line search-icon text-muted"></i>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-4">
                            <select name="status_id" class="form-select border-white shadow-sm fs-13 py-2 rounded-3">
                                <option value="">{{ __('tickets::messages.status') }}</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-3 col-md-4">
                            <select name="priority_id" class="form-select border-white shadow-sm fs-13 py-2 rounded-3">
                                <option value="">{{ __('tickets::messages.priority') }}</option>
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority->id }}" {{ request('priority_id') == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-md-4">
                            <div class="d-flex gap-2 h-100">
                                <button type="submit" class="btn btn-primary w-100 fw-bold fs-13 shadow-none rounded-3 py-2">
                                    <i class="ri-equalizer-fill me-1 align-bottom"></i> {{ __('tickets::messages.filter') }}
                                </button>
                                <a href="{{ route('agent.tickets.index') }}" class="btn btn-soft-danger w-50 shadow-none d-flex align-items-center justify-content-center rounded-3">
                                    <i class="ri-refresh-line"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive table-card mb-0">
                    <table class="table table-nowrap align-middle table-hover mb-0">
                        <thead class="bg-light text-muted border-bottom border-dashed fs-12 text-uppercase fw-bold">
                            <tr>
                                <th class="ps-4" style="width: 140px;">{{ __('tickets::messages.id') }}</th>
                                <th>{{ __('tickets::messages.subject') }}</th>
                                <th style="width: 180px;">{{ __('tickets::messages.created_at') }}</th>
                                <th style="width: 130px;">{{ __('tickets::messages.status') }}</th>
                                <th style="width: 110px;">{{ __('tickets::messages.priority') }}</th>
                                <th style="width: 180px;">{{ __('tickets::messages.assigned_to') }} / {{ __('tickets::messages.assigned_group') }}</th>
                                <th class="text-center" style="width: 100px;">{{ __('tickets::messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @forelse($tickets as $ticket)
                                <tr class="transition-all {{ $ticket->isOverdue() ? 'overdue-row' : '' }}">
                                    <td class="ps-4">
                                        <a href="{{ route('agent.tickets.show', $ticket->uuid) }}" class="fw-bold text-primary fs-13 hover-underline d-flex align-items-center">
                                            <span class="badge bg-soft-primary text-primary me-2 ms-2">#{{ $ticket->ticket_number ?? substr($ticket->uuid, 0, 8) }}</span>
                                        </a>
                                        @if($ticket->isOverdue())
                                            <span class="d-block text-danger fs-10 fw-bold text-uppercase mt-1 ms-2 ms-2"><i class="ri-error-warning-fill me-1"></i> {{ __('tickets::messages.overdue') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 avatar-xs me-2 ms-2">
                                                <div class="avatar-title bg-soft-info text-info rounded-circle fs-12 fw-bold shadow-none">
                                                    {{ substr($ticket->user->full_name ?? $ticket->user->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden" style="max-width: 300px;">
                                                <h5 class="fs-14 mb-1"><a href="{{ route('agent.tickets.show', $ticket->uuid) }}" class="text-dark fw-bold text-truncate d-block">{{ $ticket->subject }}</a></h5>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="text-muted small fw-medium text-truncate">{{ $ticket->user->full_name }}</span>
                                                    <span class="text-muted opacity-50 small fs-10">|</span>
                                                    <span class="badge bg-light text-muted border-0 rounded-pill px-2 fs-10">{{ $ticket->category->name ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-dark fs-13 fw-medium">{{ $ticket->created_at->format('d M, Y') }}</div>
                                        <div class="text-muted small fs-11 mt-1"><i class="ri-time-line me-1"></i> {{ $ticket->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $ticket->status->color ?? 'secondary' }}-subtle text-{{ $ticket->status->color ?? 'secondary' }} text-uppercase fs-11 border border-{{ $ticket->status->color ?? 'secondary' }} border-opacity-10 px-2 py-1 rounded-pill">
                                            {{ $ticket->status->name ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $ticket->priority->color ?? 'secondary' }}-subtle text-{{ $ticket->priority->color ?? 'secondary' }} text-uppercase fs-11 px-2 py-1 rounded-pill">
                                            {{ $ticket->priority->name ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($ticket->assignedTo)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xxs flex-shrink-0 me-2 ms-2">
                                                    <div class="avatar-title bg-soft-success text-success rounded-circle fs-10 border border-success border-opacity-10 shadow-none">
                                                        {{ substr($ticket->assignedTo->full_name ?? $ticket->assignedTo->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <span class="fs-13 text-dark fw-medium text-truncate" style="max-width: 120px;">{{ $ticket->assignedTo->full_name }}</span>
                                            </div>
                                        @elseif($ticket->assignedGroup)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xxs flex-shrink-0 me-2 ms-2">
                                                    <div class="avatar-title bg-soft-dark text-dark rounded-circle fs-10 border border-dark border-opacity-10 shadow-none">
                                                        <i class="ri-group-line"></i>
                                                    </div>
                                                </div>
                                                <span class="fs-12 text-muted fw-bold text-truncate" title="{{ $ticket->assignedGroup->name }}">{{ $ticket->assignedGroup->name }}</span>
                                            </div>
                                        @else
                                            <button class="btn btn-sm btn-ghost-warning rounded-pill px-3 fs-11 fw-medium" data-bs-toggle="modal" data-bs-target="#assignModal" data-uuid="{{ $ticket->uuid }}">
                                                <i class="ri-user-add-line me-1"></i> {{ __('tickets::messages.unassigned_tickets') }}
                                            </button>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('agent.tickets.show', $ticket->uuid) }}" class="btn btn-sm btn-icon btn-soft-primary rounded-circle shadow-none">
                                                <i class="ri-eye-line fs-14"></i>
                                            </a>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-icon btn-soft-secondary rounded-circle shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ri-more-2-fill fs-14"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4">
                                                    <li><a class="dropdown-item fs-13 py-2" href="{{ route('agent.tickets.show', $ticket->uuid) }}"><i class="ri-chat-1-line me-2 ms-2 text-muted fs-16 align-middle"></i> {{ __('tickets::messages.add_reply') }}</a></li>
                                                    <li><button class="dropdown-item fs-13 py-2" data-bs-toggle="modal" data-bs-target="#assignModal" data-uuid="{{ $ticket->uuid }}"><i class="ri-user-shared-line me-2 ms-2 text-muted fs-16 align-middle"></i> {{ __('tickets::messages.lookups.assigned_to') }}</button></li>
                                                    <li>
                                                        <form action="{{ route('agent.tickets.assign', $ticket->uuid) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                                                            <button type="submit" class="dropdown-item fs-13 py-2"><i class="ri-user-follow-line me-2 ms-2 text-muted fs-16 align-middle"></i> {{ __('tickets::messages.assign_me') }}</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="avatar-lg bg-soft-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center">
                                            <i class="ri-ticket-2-line text-muted display-4 opacity-50"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark fs-18">{{ __('tickets::messages.no_tickets_found') }}</h5>
                                        <p class="text-muted mx-auto pb-3" style="max-width: 300px;">Try adjusting your filters or search query to find the tickets you're looking for.</p>
                                        <a href="{{ route('agent.tickets.index') }}" class="btn btn-primary rounded-pill px-4 shadow-none fw-bold">{{ __('tickets::messages.reset') }}</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($tickets->hasPages())
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-4 border-top border-light gap-3">
                    <div class="text-muted fs-13 fw-medium">
                        Showing <b>{{ $tickets->firstItem() }}</b> to <b>{{ $tickets->lastItem() }}</b> of <b>{{ $tickets->total() }}</b> entries
                    </div>
                    <div class="pagination-rounded shadow-none">
                        {{ $tickets->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header p-4 border-0">
                <h5 class="modal-title fw-bold text-dark fs-18 d-flex align-items-center">
                    <i class="ri-user-shared-line me-2 ms-2 text-primary fs-24"></i>
                    {{ __('tickets::messages.lookups.assigned_to') }}
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-0">
                <ul class="nav nav-pills nav-justified bg-light p-1 rounded-pill mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active rounded-pill fw-bold py-2" data-bs-toggle="tab" href="#assignToAgent" role="tab">
                            <i class="ri-user-voice-line me-1 ms-1"></i> {{ __('tickets::messages.assign_to_agent') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill fw-bold py-2" data-bs-toggle="tab" href="#assignToGroup" role="tab">
                            <i class="ri-group-line me-1 ms-1"></i> {{ __('tickets::messages.assign_to_group') }}
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="assignToAgent" role="tabpanel">
                        <form id="assignAgentForm" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label text-muted fs-12 fw-bold text-uppercase">{{ __('tickets::messages.assigned_to') }}</label>
                                <select name="user_id" class="form-select select2-modal shadow-none border-light py-2 rounded-3">
                                    <option value="">{{ __('tickets::messages.none') }}</option>
                                    @foreach($groups as $group)
                                        <optgroup label="{{ $group->name }}">
                                            @foreach($group->members as $member)
                                                <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm mt-2">
                                <i class="ri-save-line me-1 ms-1"></i> {{ __('tickets::messages.save') }}
                            </button>
                        </form>
                    </div>
                    <div class="tab-pane" id="assignToGroup" role="tabpanel">
                        <form id="assignGroupForm" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label text-muted fs-12 fw-bold text-uppercase">{{ __('tickets::messages.lookups.groups') }}</label>
                                <select name="group_id" class="form-select select2-modal shadow-none border-light py-2 rounded-3">
                                    <option value="">{{ __('tickets::messages.none') }}</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm mt-2">
                                <i class="ri-save-line me-1 ms-1"></i> {{ __('tickets::messages.save') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    .ls-tight { letter-spacing: -0.025em; }
    .tracking-wider { letter-spacing: 0.1em; }
    .transition-all { transition: all 0.3s ease; }
    .hover-underline:hover { text-decoration: underline; }
    .card-animate:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; }
    
    .overdue-row { background-color: rgba(240, 101, 72, 0.03) !important; border-right: 3px solid #f06548; }
    [dir="rtl"] .overdue-row { border-right: 0; border-left: 3px solid #f06548; }

    .btn-white { background: #fff; }
    .btn-white:hover { background: #f8f9fa; }
    
    .search-box { position: relative; }
    .search-box .search-icon { position: absolute; top: 50%; transform: translateY(-50%); left: 12px; font-size: 16px; pointer-events: none; }
    [dir="rtl"] .search-box .search-icon { left: auto; right: 12px; }
    .search-box input { padding-left: 38px !important; }
    [dir="rtl"] .search-box input { padding-left: 12px !important; padding-right: 38px !important; }

    .btn-ghost-warning { color: #f7b84b; background-color: transparent; border: 1px dashed rgba(247, 184, 75, 0.5); }
    .btn-ghost-warning:hover { background-color: rgba(247, 184, 75, 0.1); border-color: #f7b84b; }

    .dropdown-menu { border-radius: 12px; padding: 8px; }
    .dropdown-item { border-radius: 6px; }
    
    .table thead th { border-top: 0; }
    .table tbody tr:hover { background-color: #fcfdfe !important; }

    /* RTL Adjustments */
    [dir="rtl"] .ms-auto { margin-right: auto !important; margin-left: 0 !important; }
    [dir="rtl"] .me-1, [dir="rtl"] .me-2, [dir="rtl"] .me-3 { margin-left: 0.5rem !important; margin-right: 0 !important; }
    [dir="rtl"] .modal-header .btn-close { margin: -0.5rem auto -0.5rem -0.5rem; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2 in modal
        $('#assignModal').on('shown.bs.modal', function () {
            $('.select2-modal').select2({
                dropdownParent: $('#assignModal'),
                width: '100%'
            });
        });

        // Set form action when assign modal is triggered
        const assignModal = document.getElementById('assignModal');
        assignModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const uuid = button.getAttribute('data-uuid');
            
            const agentForm = document.getElementById('assignAgentForm');
            const groupForm = document.getElementById('assignGroupForm');
            
            agentForm.action = `/agent/tickets/${uuid}/assign`;
            groupForm.action = `/agent/tickets/${uuid}/assign-group`;
        });
    });
</script>
@endpush
