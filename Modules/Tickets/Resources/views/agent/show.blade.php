@extends('core::layouts.master')

@section('title', __('tickets::messages.support_desk') . ' #' . ($ticket->ticket_number ?? substr($ticket->uuid, 0, 8)))

@section('content')

@if($lockedByAnother)
<div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show shadow-lg mb-4 rounded-3 overflow-hidden" role="alert">
    <div class="d-flex align-items-center">
        <div class="flex-shrink-0">
            <i class="ri-lock-fill fs-24 me-3 ms-3"></i>
        </div>
        <div class="flex-grow-1">
            <strong class="fs-15">{{ __('tickets::messages.locked') }}!</strong> 
            <p class="mb-0 opacity-75">{{ __('tickets::messages.locked_note') }} ({{ __('tickets::messages.by') }}: {{ $ticket->lockedBy->full_name ?? $ticket->lockedBy->name }})</p>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
@endif

<!-- Page Header (Expert Expert UX Redesign) -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-0 rounded-4 bg-white">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row-reverse align-items-md-center justify-content-between gap-3">
                    <!-- Status & Actions (Always Physical Left) -->
                    <div class="d-flex flex-wrap align-items-center gap-3 justify-content-start justify-content-md-end">
                        <div class="status-indicator-pill d-flex align-items-center bg-{{ $ticket->status->color ?? 'secondary' }} bg-opacity-10 border border-{{ $ticket->status->color ?? 'secondary' }} border-opacity-10 rounded-pill px-4 py-2 shadow-sm">
                            <span class="badge-dot-live p-1 bg-{{ $ticket->status->color ?? 'secondary' }} rounded-circle me-2 ms-2 animate-pulse"></span>
                            <span class="fw-bold text-{{ $ticket->status->color ?? 'secondary' }} fs-13 uppercase-badge letter-spacing-1">{{ $ticket->status->name }}</span>
                        </div>

                        <div class="dropdown">
                            <button class="btn btn-primary btn-label rounded-pill px-4 fs-13 fw-bold shadow-lg d-flex align-items-center justify-content-center overflow-hidden" 
                                    data-bs-toggle="dropdown" aria-expanded="false" style="height: 40px; padding-left: 1.5rem !important; padding-right: 1.5rem !important;">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <i class="ri-settings-4-fill fs-18 align-middle opacity-90"></i>
                                    <span class="mt-1">{{ __('tickets::messages.actions') }}</span>
                                </div>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 py-2 mt-2" style="min-width: 220px; z-index: 1065;">
                                <li class="px-3 pb-2 pt-1 border-bottom border-light mb-1">
                                    <h6 class="dropdown-header text-uppercase text-muted fs-10 fw-bold px-0 letter-spacing-1">{{ __('tickets::messages.records') }}</h6>
                                </li>
                                <li>
                                    <a class="dropdown-item fs-13 py-2 px-3 fw-medium d-flex align-items-center transition-all" href="javascript:void(0)" onclick="scrollToActivity()">
                                        <i class="ri-history-line me-2 ms-2 text-primary opacity-75 fs-18"></i> {{ __('tickets::messages.activity_log') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item fs-13 py-2 px-3 fw-medium d-flex align-items-center transition-all" href="javascript:void(0)" onclick="printTicket('{{ route('agent.tickets.print', $ticket->uuid) }}')">
                                        <i class="ri-printer-line me-2 ms-2 text-primary opacity-75 fs-18"></i> {{ __('tickets::messages.print') }}
                                    </a>
                                </li>
                                <div class="dropdown-divider mx-3 my-2 opacity-10"></div>
                                @if(!$ticket->status->is_final)
                                <li>
                                    <form action="{{ route('agent.tickets.close', $ticket->uuid) }}" method="POST" id="closeTicketForm">
                                        @csrf
                                        @method('PUT')
                                        <button type="button" onclick="confirmClose()" class="dropdown-item fs-13 py-2 px-3 fw-bold text-danger d-flex align-items-center transition-all">
                                            <i class="ri-close-circle-line me-2 ms-2 text-danger fs-18"></i> {{ __('tickets::messages.close_ticket') }}
                                        </button>
                                    </form>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- Title & Identity (Always Physical Right/Start) -->
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-2">
                            <div class="ticket-id-badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 fw-bold px-3 py-1 rounded-pill fs-11 me-2 ms-2 d-flex align-items-center justify-content-center">
                                <i class="ri-hashtag fs-10 me-1 ms-1 text-primary opacity-50"></i>{{ $ticket->ticket_number ?? substr($ticket->uuid, 0, 8) }}
                            </div>
                            <button class="btn btn-soft-primary btn-sm btn-icon rounded-circle shadow-none p-0 border-0" 
                                    style="width: 28px; height: 28px; min-width: 28px;"
                                    onclick="copyToClipboard('{{ $ticket->ticket_number ?? $ticket->uuid }}')" 
                                    title="{{ __('tickets::messages.copy_id') }}">
                                <i class="ri-file-copy-2-line fs-14"></i>
                            </button>
                            <span class="mx-3 text-muted opacity-25">|</span>
                            <span class="text-muted fw-bold fs-10 text-uppercase tracking-widest letter-spacing-1 opacity-75 d-flex align-items-center">
                                <i class="ri-customer-service-2-line me-1 ms-1 fs-12 text-primary"></i>{{ __('tickets::messages.support_desk') }}
                            </span>
                        </div>
                        <h2 class="fw-bold mb-0 text-dark ls-tight display-6 fs-24" style="letter-spacing: -0.02em;">{{ $ticket->subject }}</h2>
                        <div class="mt-2 d-flex align-items-center flex-wrap gap-4">
                            <span class="text-muted fs-13 d-flex align-items-center opacity-75">
                                <i class="ri-calendar-2-fill me-1 ms-1 text-primary"></i> {{ $ticket->created_at->format('d M, Y') }}
                            </span>
                            <span class="text-muted fs-13 d-flex align-items-center opacity-75 border-start-dashed ps-3 ms-2 me-2 border-light">
                                <i class="ri-user-follow-fill me-1 ms-1 text-primary"></i> {{ $ticket->user->full_name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Hidden Iframe for Silent Print -->
<iframe id="printFrame" style="display:none;"></iframe>

<div class="row">
    <!-- SIDEBAR (Sticky on the Left in RTL) -->
    <div class="col-xl-3">
        <div class="sticky-side-div pb-4">
            <!-- SLA Monitoring Card (Professional Polish) -->
            <div class="card border-0 shadow-sm mb-4 overflow-hidden rounded-4">
                <div class="card-header border-0 bg-sla p-3 border-start border-4 border-primary">
                    <h5 class="card-title mb-0 text-dark fs-14 fw-bold d-flex align-items-center">
                        <i class="ri-timer-flash-line me-2 text-primary fs-18"></i> {{ __('tickets::messages.sla') }}
                    </h5>
                </div>
                <div class="card-body p-4 bg-sla bg-opacity-20">
                    <div class="sla-info-item mb-4 bg-white p-3 rounded-4 shadow-sm border border-light">
                        <p class="text-muted mb-2 fs-10 fw-bold text-uppercase tracking-wider opacity-50">{{ __('tickets::messages.sla_start') }}</p>
                        <div class="d-flex align-items-center">
                            <div class="avatar-xs flex-shrink-0 me-2 ms-2">
                                <div class="avatar-title bg-soft-info text-info rounded-circle fs-14"><i class="ri-calendar-check-line"></i></div>
                            </div>
                            <h6 class="mb-0 fs-13 fw-bold text-dark">{{ $ticket->created_at->format('d/m/Y - h:i A') }}</h6>
                        </div>
                    </div>

                    <div class="sla-info-item mb-4 bg-white p-3 rounded-4 shadow-sm border border-light">
                        <p class="text-muted mb-2 fs-10 fw-bold text-uppercase tracking-wider opacity-50">{{ __('tickets::messages.sla_end') }}</p>
                        <div class="d-flex align-items-center">
                            <div class="avatar-xs flex-shrink-0 me-2">
                                <div class="avatar-title bg-soft-danger text-danger rounded-circle fs-14"><i class="ri-alarm-warning-line"></i></div>
                            </div>
                            <h6 class="mb-0 fs-13 fw-bold text-danger">{{ $ticket->due_at ? $ticket->due_at->format('d/m/Y - h:i A') : '--/--/----' }}</h6>
                        </div>
                    </div>

                    <div class="sla-info-item bg-white p-3 rounded-4 shadow-sm border border-light">
                        <p class="text-muted mb-2 fs-10 fw-bold text-uppercase tracking-wider opacity-50">{{ __('tickets::messages.first_action_time') }}</p>
                        <div class="d-flex align-items-center">
                            <div class="avatar-xs flex-shrink-0 me-2">
                                <div class="avatar-title bg-soft-success text-success rounded-circle fs-14"><i class="ri-checkbox-circle-line"></i></div>
                            </div>
                            <h6 class="mb-0 fs-13 fw-bold text-success">{{ $firstAction ? $firstAction->created_at->format('d/m/Y - h:i A') : '--/--/----' }}</h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card (Centralized Control) -->
            <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden border-top border-4 border-success">
                <div class="card-header border-0 bg-white p-3">
                    <h5 class="card-title mb-0 text-dark fs-14 fw-bold d-flex align-items-center">
                        <i class="ri-flashlight-line me-2 ms-2 text-success fs-18"></i> {{ __('tickets::messages.quick_action') }}
                    </h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <!-- Status Update -->
                    <form action="{{ route('agent.tickets.updateStatus', $ticket->uuid) }}" method="POST" class="mb-4">
                        @csrf
                        @method('PUT')
                        <label class="form-label text-muted fs-11 text-uppercase fw-bold opacity-75 mb-2">{{ __('tickets::messages.status') }}</label>
                        <div class="input-group">
                            <select name="status_id" class="form-select border-light shadow-none fs-13 rounded-start-3">
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" {{ $ticket->status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-soft-success px-3 shadow-none border-light" type="submit"><i class="ri-check-line fw-bold"></i></button>
                        </div>
                    </form>

                    <!-- Priority Update -->
                    <form action="{{ route('agent.tickets.updatePriority', $ticket->uuid) }}" method="POST" class="mb-4">
                        @csrf
                        @method('PUT')
                        <label class="form-label text-muted fs-11 text-uppercase fw-bold opacity-75 mb-2">{{ __('tickets::messages.priority') }}</label>
                        <div class="input-group">
                            <select name="priority_id" class="form-select border-light shadow-none fs-13 rounded-start-3">
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority->id }}" {{ $ticket->priority_id == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-soft-primary px-3 shadow-none border-light" type="submit"><i class="ri-check-line fw-bold"></i></button>
                        </div>
                    </form>

                    <!-- Assignment -->
                    <div class="mb-0">
                        <label class="form-label text-muted fs-11 text-uppercase fw-bold opacity-75 mb-2">{{ __('tickets::messages.assigned_to_him') }}</label>
                        <div class="d-grid gap-2">
                            <div class="p-3 bg-light rounded-4 border border-dashed border-primary border-opacity-25 mb-2">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-xs flex-shrink-0 me-2 ms-2">
                                        @if($ticket->assignedTo)
                                            <div class="avatar-title bg-soft-success text-success rounded-circle fs-12">{{ substr($ticket->assignedTo->full_name, 0, 1) }}</div>
                                        @elseif($ticket->assignedGroup)
                                            <div class="avatar-title bg-soft-dark text-dark rounded-circle fs-12"><i class="ri-group-line"></i></div>
                                        @else
                                            <div class="avatar-title bg-soft-warning text-warning rounded-circle fs-12"><i class="ri-user-unfollow-line"></i></div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h6 class="mb-0 fs-13 fw-bold text-dark text-truncate">
                                            {{ $ticket->assignedTo->full_name ?? ($ticket->assignedGroup->name ?? __('tickets::messages.unassigned_tickets')) }}
                                        </h6>
                                        <p class="text-muted mb-0 fs-11 fw-medium">{{ __('tickets::messages.current_assignment') }}</p>
                                    </div>
                                </div>
                                
                                <button class="btn btn-primary w-100 rounded-pill fw-bold fs-12 py-2 shadow-none" data-bs-toggle="modal" data-bs-target="#assignModalDetail">
                                    <i class="ri-user-shared-line me-1 ms-1 align-bottom"></i> {{ __('tickets::messages.update_assignment') }}
                                </button>
                            </div>
                            
                            @if(!$ticket->assigned_to || $ticket->assigned_to != auth()->id())
                            <form action="{{ route('agent.tickets.assign', $ticket->uuid) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                                <button type="submit" class="btn btn-soft-primary w-100 rounded-pill fw-bold fs-12 py-2 shadow-none border-dashed border-primary border-opacity-50">
                                    <i class="ri-user-follow-line me-1 ms-1 align-bottom"></i> {{ __('tickets::messages.assign_me') }}
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Ticket Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom border-bottom-dashed">
                    <h5 class="card-title mb-0 fs-14 fw-bold text-dark">{{ __('tickets::messages.ticket_info') }}</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-muted fs-11 text-uppercase fw-bold opacity-75 mb-2">{{ __('tickets::messages.stage') }}</label>
                        <div class="p-2 bg-light rounded text-dark fw-semibold fs-13 border border-light">
                            <i class="ri-stack-line text-primary me-2 shadow-sm p-1 rounded bg-white"></i> {{ $ticket->stage->name ?? '-' }}
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted fs-11 text-uppercase fw-bold opacity-75 mb-2">{{ __('tickets::messages.category') }}</label>
                        <div class="p-2 bg-light rounded text-dark fw-semibold fs-13 border border-light">
                            <i class="ri-folders-line text-info me-2 shadow-sm p-1 rounded bg-white"></i> {{ $ticket->category->name ?? '-' }}
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-muted fs-11 text-uppercase fw-bold opacity-75 mb-2">{{ __('tickets::messages.complaint') }}</label>
                        <div class="p-2 bg-light rounded text-dark fw-semibold fs-13 border border-light">
                            <i class="ri-error-warning-line text-warning me-2 shadow-sm p-1 rounded bg-white"></i> {{ $ticket->complaint->name ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Overview Tabbed -->
            <div class="card border-0 shadow-sm mb-0 overflow-hidden">
                <div class="card-header p-0">
                    <ul class="nav nav-tabs nav-tabs-custom nav-primary nav-justified border-bottom-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active py-3 fs-13 fw-bold" data-bs-toggle="pill" href="#v-student" role="tab shadow-none">
                                <i class="ri-user-smile-line align-bottom me-1"></i> {{ __('tickets::messages.student') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3 fs-13 fw-bold" data-bs-toggle="pill" href="#v-lecture" role="tab shadow-none">
                                <i class="ri-slideshow-line align-bottom me-1"></i> {{ __('tickets::messages.lecture_data') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content">
                        <div class="tab-pane fade show active text-center" id="v-student" role="tabpanel">
                            <div class="avatar-lg mx-auto mb-3 shadow-lg p-1 bg-white rounded-circle border border-primary border-opacity-10">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($ticket->user->full_name) }}&background=687cfe&color=fff&size=200" class="rounded-circle img-fluid shadow-sm">
                            </div>
                            <h5 class="fs-16 mb-1 fw-bold text-dark">{{ $ticket->user->full_name }}</h5>
                            <p class="text-muted mb-0 fs-13 fw-medium">{{ $ticket->user->email }}</p>
                            <div class="mt-3 pt-3 border-top border-top-dashed">
                                <button class="btn btn-soft-primary btn-sm w-100 rounded-pill shadow-none fw-bold">{{ __('tickets::messages.view_profile') }}</button>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="v-lecture" role="tabpanel">
                            <div class="text-center py-5 bg-light bg-opacity-50 rounded-4 border border-dashed">
                                <i class="ri-database-2-line fs-32 text-muted opacity-25"></i>
                                <p class="text-muted mb-0 mt-2 small fw-semibold">{{ __('tickets::messages.no_data_found') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN AREA (Ticket Content & Timeline) -->
    <div class="col-xl-9">
        <!-- Ticket Core Content -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-bottom-dashed">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-2">
                            <h4 class="card-title mb-0 fs-18 fw-bold text-dark d-flex align-items-center">
                                <i class="ri-file-text-line me-2 ms-2 text-primary fs-20"></i>{{ __('tickets::messages.details') }}
                            </h4>
                            <div class="ms-auto me-auto d-flex gap-2">
                                <button class="btn btn-ghost-primary btn-sm rounded-pill fw-medium fs-12 px-3 border border-primary border-opacity-10 shadow-none" 
                                    onclick="copyToClipboard(window.location.href)">
                                    <i class="ri-link-m me-1 ms-1"></i> {{ __('tickets::messages.copy_url') }}
                                </button>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-4 text-muted fs-13">
                            <div class="d-flex align-items-center">
                                <div class="avatar-xxs flex-shrink-0 me-2 ms-2">
                                    <div class="avatar-title bg-soft-info text-info rounded-circle fs-12"><i class="ri-user-add-line"></i></div>
                                </div>
                                <span class="fw-medium">
                                    {{ __('tickets::messages.created_by') }}: 
                                    <strong class="text-dark ms-1 me-1">
                                        @if($createdActivity && $createdActivity->user)
                                            {{ $createdActivity->user->full_name }}
                                        @else
                                            {{ __('tickets::messages.system') }}
                                        @endif
                                    </strong>
                                </span>
                            </div>

                            <div class="d-flex align-items-center border-start ps-4 pe-4 ms-2 me-2">
                                <div class="avatar-xxs flex-shrink-0 me-2 ms-2">
                                    <div class="avatar-title bg-soft-success text-success rounded-circle fs-12"><i class="ri-user-smile-line"></i></div>
                                </div>
                                <span class="fw-medium">
                                    {{ __('tickets::messages.student_subject') }}: 
                                    <strong class="text-dark ms-1">{{ $ticket->user->full_name }}</strong>
                                </span>
                            </div>

                            @if($createdActivity && $createdActivity->user_id == $ticket->user_id)
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-3 py-1 fs-11 rounded-pill fw-bold">
                                    <i class="ri-checkbox-circle-fill me-1 ms-1 align-bottom"></i> {{ __('tickets::messages.opened_by_student') }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Description Block (Paper Style) -->
                <div class="ticket-description-container position-relative mb-4">
                    <div class="p-4 bg-light bg-opacity-40 rounded-4 border-0 shadow-none position-relative overflow-hidden" style="min-height: 120px; border: 1px dashed rgba(64, 81, 137, 0.1) !important;">
                        <input type="hidden" id="ticketDetailsRaw" value="{{ $ticket->details }}">
                        <div class="quote-watermark position-absolute bottom-0 end-0 p-0 opacity-5 mb-n4 me-n4">
                            <i class="ri-double-quotes-r fs-96"></i>
                        </div>
                        <div class="fs-15 lh-lg text-dark fw-medium" style="white-space: pre-wrap; position: relative; z-index: 1; letter-spacing: 0.2px;">{{ $ticket->details }}</div>
                    </div>
                </div>

                <!-- Sub-Complaints -->
                @if($ticket->subComplaints->count() > 0)
                <div class="mb-4">
                    <h6 class="fs-12 text-uppercase text-muted fw-bold tracking-widest mb-3">{{ __('tickets::messages.sub_complaints') }}</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($ticket->subComplaints as $sub)
                            <span class="badge bg-white shadow-sm text-dark border border-soft-secondary px-3 py-2 fs-12 rounded-pill fw-bold"><i class="ri-price-tag-3-fill text-primary me-1"></i> {{ $sub->name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Attachments Grid (Premium Redesign) -->
                @if($ticket->attachments->count() > 0)
                <div class="mb-0">
                    <h6 class="fs-12 text-uppercase text-muted fw-bold tracking-widest mb-3 d-flex align-items-center">
                        <i class="ri-attachment-2 me-2 text-primary fs-16"></i> {{ __('tickets::messages.attachments') }}
                    </h6>
                    <div class="row g-3">
                        @foreach($ticket->attachments as $attachment)
                        @php
                            $ext = pathinfo($attachment->file_name, PATHINFO_EXTENSION);
                            $iconClass = 'ri-file-line';
                            $iconBg = 'bg-soft-primary';
                            $iconColor = 'text-primary';
                            
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
                                $iconClass = 'ri-image-line';
                                $iconBg = 'bg-soft-success';
                                $iconColor = 'text-success';
                            } elseif ($ext == 'pdf') {
                                $iconClass = 'ri-file-pdf-line';
                                $iconBg = 'bg-soft-danger';
                                $iconColor = 'text-danger';
                            } elseif (in_array($ext, ['zip', 'rar', '7z'])) {
                                $iconClass = 'ri-file-zip-line';
                                $iconBg = 'bg-soft-warning';
                                $iconColor = 'text-warning';
                            } elseif (in_array($ext, ['doc', 'docx'])) {
                                $iconClass = 'ri-file-word-line';
                                $iconBg = 'bg-soft-info';
                                $iconColor = 'text-info';
                            }
                        @endphp
                        <div class="col-xl-4 col-md-6">
                            <div class="file-premium-card hover-shadow transition-all border rounded-4 p-0 bg-white overflow-hidden shadow-sm">
                                <div class="d-flex align-items-center p-3">
                                    <div class="avatar-sm flex-shrink-0">
                                        <div class="avatar-title {{ $iconBg }} {{ $iconColor }} rounded-3 fs-24 shadow-sm border border-light">
                                            <i class="{{ $iconClass }}"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3 overflow-hidden">
                                        <h5 class="fs-13 mb-1 text-truncate fw-bold">
                                            <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="text-dark link-primary-hover">{{ $attachment->file_name }}</a>
                                        </h5>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted small fw-medium">{{ number_format(($attachment->file_size ?? 0) / 1024, 2) }} KB</span>
                                            <span class="badge bg-light text-muted border-0 rounded-pill px-2 py-0 fs-10 uppercase-badge">{{ strtoupper($ext) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 ms-2 me-2 d-flex gap-1">
                                        <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="btn btn-icon btn-sm btn-ghost-secondary rounded-circle shadow-none" title="{{ __('tickets::messages.view') }}"><i class="ri-eye-line fs-16"></i></a>
                                        <a href="{{ Storage::url($attachment->file_path) }}" download class="btn btn-icon btn-sm btn-soft-primary rounded-circle shadow-none" title="{{ __('tickets::messages.download') }}"><i class="ri-download-cloud-2-line fs-16"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Activities (Timeline & Reply) -->
        <div class="card border-0 shadow-sm">
            <div class="card-header p-0">
                <ul class="nav nav-tabs nav-tabs-custom nav-success border-bottom-0" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active py-4 px-4 fs-14 fw-bold" data-bs-toggle="tab" href="#comments-tab" role="tab">
                            <i class="ri-chat-voice-line align-bottom me-2 text-primary fs-18"></i> {{ __('tickets::messages.replies') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-4 px-4 fs-14 fw-bold" data-bs-toggle="tab" href="#activity-tab" role="tab">
                            <i class="ri-history-line align-bottom me-2 text-primary fs-18"></i> {{ __('tickets::messages.activity_log') }}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4 bg-light bg-opacity-20">
                <div class="tab-content">
                    <!-- Replies Tab -->
                    <div class="tab-pane active show" id="comments-tab" role="tabpanel">
                        <!-- Reply Interface (Chat Style) -->
                        <div class="reply-compose-box bg-white border rounded-4 shadow-sm mb-5 p-4 border-dashed border-primary border-opacity-25">
                            <ul class="nav nav-pills nav-custom-outline nav-success mb-4 gap-2" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active rounded-pill px-4 fs-13 fw-bold" data-bs-toggle="tab" href="#p-public" role="tab"><i class="ri-customer-service-2-line me-1"></i> {{ __('tickets::messages.public_reply') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link rounded-pill px-4 fs-13 fw-bold bg-warning-subtle text-warning border-warning border-opacity-25" data-bs-toggle="tab" href="#p-internal" role="tab"><i class="ri-lock-2-fill me-1"></i> {{ __('tickets::messages.internal_note') }}</a>
                                </li>
                            </ul>
                            
                            <form action="{{ route('agent.tickets.reply', $ticket->uuid) }}" method="POST" enctype="multipart/form-data" id="replyForm">
                                @csrf
                                <input type="hidden" name="type" id="reply_type" value="message">
                                
                                <div class="tab-content">
                                    <div class="tab-pane active" id="p-public">
                                        <div class="alert alert-soft-info border-0 px-3 py-2 fs-12 mb-3 rounded-pill d-inline-flex align-items-center shadow-none">
                                            <i class="ri-information-fill fs-18 me-2 text-info"></i> {{ __('tickets::messages.send_to_all') }}
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="p-internal">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fs-12 text-muted fw-bold text-uppercase mb-1">{{ __('tickets::messages.internal_target') }}</label>
                                                <select name="target_group_id" class="form-select border-dashed bg-light shadow-none fs-13 rounded-3">
                                                    <option value="">{{ __('tickets::messages.default') }}</option>
                                                    @foreach($groups as $group)
                                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted fs-11 mt-1 d-block">{{ __('tickets::messages.transfer_note') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="position-relative">
                                    <textarea class="form-control bg-light border-0 p-4 fs-14 custom-shadow-inset" name="message" rows="5" placeholder="{{ __('tickets::messages.add_reply') }}..." required style="resize: none; border-radius: 16px; min-height: 150px;"></textarea>
                                </div>
                                <div class="d-sm-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                    <div class="flex-grow-1">
                                        <label class="btn btn-soft-secondary border-dashed btn-sm mb-0 px-4 py-2 cursor-pointer w-100 w-sm-auto text-center rounded-pill fw-bold">
                                            <input type="file" name="attachments[]" multiple class="d-none">
                                            <i class="ri-attachment-line me-1 fs-16 align-bottom"></i> {{ __('tickets::messages.attachments') }}
                                        </label>
                                    </div>
                                    <div class="mt-3 mt-sm-0">
                                        <button type="submit" class="btn btn-primary btn-label rounded-pill px-5 shadow-lg fw-bold h-100">
                                            <i class="ri-send-plane-2-fill label-icon align-middle fs-16 me-2"></i> {{ __('tickets::messages.save') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Timeline Messages -->
                        <div class="ticket-timeline mt-5">
                            @foreach($ticket->threads->sortByDesc('created_at') as $thread)
                            <div class="timeline-thread-item d-flex mb-5 ps-2">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-{{ $thread->user_id == $ticket->user_id ? 'primary' : ($thread->type == 'internal_note' ? 'warning' : 'success') }}-subtle text-{{ $thread->user_id == $ticket->user_id ? 'primary' : ($thread->type == 'internal_note' ? 'warning' : 'success') }} fs-18 rounded-circle shadow-sm fw-bold border border-white border-4">
                                            {{ substr($thread->user->name, 0, 1) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="thread-bubble-card card shadow-sm border-0 mb-1 overflow-hidden transition-all {{ $thread->type == 'internal_note' ? 'bg-warning-subtle-glow border-start border-4 border-warning' : ($thread->user_id == $ticket->user_id ? 'bg-white border-start border-4 border-primary' : 'bg-success-subtle-glow border-start border-4 border-success shadow-none border') }}">
                                        <div class="card-body p-4 p-md-4">
                                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom opacity-75">
                                                <div class="d-flex align-items-center">
                                                    <h6 class="fs-15 mb-0 fw-bold text-dark me-2">
                                                        {{ $thread->user->full_name }} 
                                                    </h6>
                                                    @if($thread->type == 'internal_note')
                                                        <span class="badge bg-warning text-dark border border-warning border-opacity-10 fs-10 fw-bold rounded-pill"><i class="ri-lock-line me-1"></i> {{ __('tickets::messages.internal_only') }}</span>
                                                    @elseif($thread->user->hasRole('agent') || $thread->user->hasRole('admin'))
                                                        <span class="badge bg-success text-white border-0 fs-10 fw-bold rounded-pill">{{ __('tickets::messages.support_desk') }}</span>
                                                    @endif
                                                </div>
                                                <div class="text-muted fs-11 fw-bold bg-light px-3 py-1 rounded-pill border shadow-inset-sm">
                                                    <i class="ri-time-line text-primary me-1 ms-1"></i> {{ $thread->created_at->format('d M, h:i A') }}
                                                </div>
                                            </div>
                                            <div class="thread-content-text fs-15 lh-lg text-dark-emphasis mb-0" style="white-space: pre-wrap; letter-spacing: 0.1px;">{{ $thread->content }}</div>
                                            
                                            @if($thread->attachments->count() > 0)
                                            <div class="mt-4 pt-4 border-top">
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($thread->attachments as $att)
                                                    @php
                                                        $attExt = pathinfo($att->file_name, PATHINFO_EXTENSION);
                                                        $attIcon = 'ri-file-line';
                                                        if (in_array($attExt, ['jpg', 'jpeg', 'png'])) $attIcon = 'ri-image-line';
                                                        elseif ($attExt == 'pdf') $attIcon = 'ri-file-pdf-line';
                                                    @endphp
                                                    <div class="thread-attachment-pill d-flex align-items-center bg-white rounded-pill px-3 py-1 border transition-all hover-shadow-sm">
                                                        <i class="{{ $attIcon }} text-primary me-2"></i>
                                                        <a href="{{ Storage::url($att->file_path) }}" target="_blank" class="text-dark fw-bold fs-11 text-truncate me-2" style="max-width: 140px;">{{ $att->file_name }}</a>
                                                        <div class="divider-vertical mx-1"></div>
                                                        <a href="{{ Storage::url($att->file_path) }}" download class="text-primary fs-12 transition-all hover-scale"><i class="ri-download-2-line"></i></a>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Activity Tab (Premium Timeline) -->
                    <div class="tab-pane fade" id="activity-tab" role="tabpanel">
                        <div class="simple-activity-timeline py-3 px-2">
                            @foreach($ticket->activities as $activity)
                            <div class="activity-log-item d-flex position-relative mb-4">
                                <div class="activity-marker flex-shrink-0">
                                    <div class="avatar-xs">
                                        <div class="avatar-title rounded-circle bg-white text-primary border border-primary border-opacity-10 shadow-sm fw-bold">
                                            @if($activity->activity_type == 'status_changed')
                                                <i class="ri-refresh-line fs-14 text-success"></i>
                                            @elseif($activity->activity_type == 'assigned')
                                                <i class="ri-user-shared-line fs-14 text-info"></i>
                                            @elseif($activity->activity_type == 'created')
                                                <i class="ri-add-circle-line fs-14 text-primary"></i>
                                            @else
                                                <i class="ri-settings-3-line fs-14 text-muted"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="activity-content flex-grow-1 ms-3 bg-white p-3 rounded-4 border border-light shadow-none">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0 fw-bold fs-13 text-dark">{{ $activity->description }}</h6>
                                        <span class="text-muted fw-bold bg-light px-2 py-1 rounded fs-10 opacity-75">{{ $activity->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 text-muted fs-11 mt-2">
                                        <div class="d-flex align-items-center">
                                            <i class="ri-user-smile-line text-primary me-1 ms-1"></i>
                                            <span class="fw-bold text-dark opacity-75">{{ $activity->user?->full_name ?? __('tickets::messages.system') }}</span>
                                        </div>
                                        <span class="text-light fs-14">|</span>
                                        <div class="d-flex align-items-center opacity-75">
                                            <i class="ri-time-line me-1 ms-1"></i>
                                            <span>{{ $activity->created_at->format('H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Similar Complaints -->
        <div class="card border-0 shadow-sm mb-4 overflow-hidden">
            <div class="card-header border-0 bg-light bg-opacity-50 p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h4 class="card-title mb-0 fs-16 fw-bold"><i class="ri-stack-line align-bottom me-2 text-primary"></i> {{ __('tickets::messages.similar_tickets') }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="badge bg-soft-info text-info rounded-pill px-3">{{ count($similarTickets) }} {{ __('tickets::messages.records') }}</span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-nowrap align-middle table-sm mb-0">
                        <thead class="table-light fs-11 text-uppercase fw-bold text-muted border-top border-bottom border-dashed">
                            <tr>
                                <th class="ps-4">{{ __('tickets::messages.id') }}</th>
                                <th>{{ __('tickets::messages.subject') }}</th>
                                <th>{{ __('tickets::messages.student') }}</th>
                                <th>{{ __('tickets::messages.status') }}</th>
                                <th class="pe-4">{{ __('tickets::messages.created_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($similarTickets as $similar)
                            <tr class="fs-13">
                                <td class="ps-4 py-3"><a href="{{ route('agent.tickets.show', $similar->uuid) }}" class="fw-bold text-primary link-hover">#{{ $similar->ticket_number ?? substr($similar->uuid, 0, 8) }}</a></td>
                                <td><span class="d-inline-block text-truncate fw-semibold text-dark" style="max-width: 280px;">{{ $similar->subject }}</span></td>
                                <td><span class="fw-medium text-muted">{{ $similar->user->full_name }}</span></td>
                                <td><span class="badge bg-{{ $similar->status->color ?? 'secondary' }}-subtle text-{{ $similar->status->color ?? 'secondary' }} rounded-pill px-2">{{ $similar->status->name }}</span></td>
                                <td class="pe-4 text-muted fs-12 fw-medium">{{ $similar->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-5 fw-medium">{{ __('tickets::messages.no_tickets_found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- Libraries -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<!-- Assign Modal Detail -->
<div class="modal fade" id="assignModalDetail" tabindex="-1" aria-hidden="true">
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
                        <a class="nav-link active rounded-pill fw-bold py-2" data-bs-toggle="tab" href="#assignToAgentDet" role="tab">
                            <i class="ri-user-voice-line me-1 ms-1"></i> {{ __('tickets::messages.assign_to_agent') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill fw-bold py-2" data-bs-toggle="tab" href="#assignToGroupDet" role="tab">
                            <i class="ri-group-line me-1 ms-1"></i> {{ __('tickets::messages.assign_to_group') }}
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="assignToAgentDet" role="tabpanel">
                        <form action="{{ route('agent.tickets.assign', $ticket->uuid) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label text-muted fs-12 fw-bold text-uppercase">{{ __('tickets::messages.assigned_to') }}</label>
                                <select name="user_id" class="form-select select2-show shadow-none border-light py-2 rounded-3">
                                    <option value="">{{ __('tickets::messages.none') }}</option>
                                    @foreach($groups as $group)
                                        <optgroup label="{{ $group->name }}">
                                            @foreach($group->members as $member)
                                                <option value="{{ $member->id }}" {{ $ticket->assigned_to == $member->id ? 'selected' : '' }}>{{ $member->full_name }}</option>
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
                    <div class="tab-pane" id="assignToGroupDet" role="tabpanel">
                        <form action="{{ route('agent.tickets.assignGroup', $ticket->uuid) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label text-muted fs-12 fw-bold text-uppercase">{{ __('tickets::messages.lookups.groups') }}</label>
                                <select name="group_id" class="form-select select2-show shadow-none border-light py-2 rounded-3">
                                    <option value="">{{ __('tickets::messages.none') }}</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ $ticket->assigned_group_id == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
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

<style>
    /* Custom Design Tokens & Utilities */
    :root {
        --vz-card-radius: 16px;
        --vz-ticket-primary: #405189;
    }
    
    .ticket-title-hero {
        font-size: 1.75rem;
        letter-spacing: -0.02em;
        color: #2a2a2a;
    }
    
    .uppercase-badge {
        letter-spacing: 0.05em;
        font-weight: 700;
        text-transform: uppercase;
    }
    .bg-sla{background-color: #f3f6f9;}
    
    /* Box Shadows & Glows */
    .shadow-inset-sm { box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); }
    .custom-shadow-inset { box-shadow: inset 2px 2px 8px rgba(0,0,0,0.04); }
    .bg-warning-subtle-glow { background: linear-gradient(135deg, rgba(255, 190, 84, 0.08) 0%, rgba(255, 190, 84, 0.03) 100%); }
    .bg-success-subtle-glow { background: linear-gradient(135deg, rgba(10, 179, 156, 0.06) 0%, rgba(10, 179, 156, 0.02) 100%); }
    
    .animate-pulse {
        animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes pulse-ring {
        0%, 100% { opacity: 1; }
        50% { opacity: .5; }
    }
    
    .letter-spacing-1 { letter-spacing: 1px; }
    
    /* Layout Logic */
    .sticky-side-div {
        position: sticky;
        top: 80px;
    }
    
    /* RTL Adjustments for Sidebar on Left */
    /* By default in grid, col-3 comes first. In LTR first is left. In RTL first is right. */
    /* User wants it on left in RTL. So col-3 should be second or move it. */
    [dir="rtl"] .row { flex-direction: row-reverse; } /* Swaps sidebar/main for RTL */

    /* Activity Timeline Polish */
    .simple-activity-timeline .activity-log-item:not(:last-child):after {
        content: "";
        position: absolute;
        left: 17px;
        top: 30px;
        height: calc(100% + 20px);
        width: 2px;
        background: linear-gradient(to bottom, #e9ebec 50%, transparent 100%);
    }
    [dir="rtl"] .simple-activity-timeline .activity-log-item:not(:last-child):after {
        left: auto;
        right: 17px;
    }
    
    .activity-log-item .activity-content {
        transition: all 0.2s ease;
    }
    .activity-log-item:hover .activity-content {
        border-color: var(--vz-primary) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03) !important;
    }

    /* Thread Bubble Styling - Rounder & More Professional */
    .thread-bubble-card { border-radius: 20px !important; }
    
    .hover-shadow:hover { 
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08) !important; 
    }
    .link-hover:hover { color: var(--vz-link-hover-color) !important; text-decoration: underline; }
    
    .file-premium-card { border-color: #f1f3f5 !important; }
    .file-premium-card:hover { border-color: var(--vz-primary) !important; }
    .link-primary-hover:hover { color: var(--vz-primary) !important; text-decoration: underline; }
    .thread-attachment-pill { border-radius: 12px !important; }
    .thread-attachment-pill:hover { background-color: #fff !important; border-color: var(--vz-primary) !important; }
    .divider-vertical { width: 1px; height: 16px; background-color: #ced4da; }
    .hover-scale:hover { transform: scale(1.2); }
    .hover-shadow-sm:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .uppercase-badge { font-family: monospace; letter-spacing: 0.05em; }

    [dir="rtl"] .me-1, [dir="rtl"] .me-2, [dir="rtl"] .me-3, [dir="rtl"] .me-4 { margin-left: 0.5rem !important; margin-right: 0 !important; }
    [dir="rtl"] .ms-1, [dir="rtl"] .ms-2, [dir="rtl"] .ms-3, [dir="rtl"] .ms-4 { margin-right: 0.5rem !important; margin-left: 0 !important; }
    [dir="rtl"] .ms-auto { margin-right: auto !important; margin-left: 0 !important; }
    [dir="rtl"] .me-auto { margin-left: auto !important; margin-right: 0 !important; }
    [dir="rtl"] .pe-3 { padding-left: 1rem !important; padding-right: 0 !important; }
    [dir="rtl"] .ps-3 { padding-right: 1rem !important; padding-left: 0 !important; }
    [dir="rtl"] .border-end { border-left: 1px solid #EFF2F7 !important; border-right: 0 !important; }
    [dir="rtl"] .border-start { border-right: 4px solid var(--vz-primary) !important; border-left: 0 !important; }
    [dir="rtl"] .timeline-thread-item { padding-right: 0.5rem !important; padding-left: 0 !important; }

    /* Custom Scrollbar for Textarea */
    textarea::-webkit-scrollbar { width: 6px; }
    textarea::-webkit-scrollbar-thumb { background-color: #e2e2e2; border-radius: 10px; }
</style>

<script>
    /**
     * Show Premium Toast (Global Scope)
     */
    window.showToast = function(message, icon = 'success') {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });
            Toast.fire({
                icon: icon,
                title: message
            });
        }
    };

    /**
     * Copy text to clipboard and show premium toast
     */
    window.copyToClipboard = function(text) {
        if (!text) return;
        
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                window.showToast('{{ __("tickets::messages.copied_success") }}', 'success');
            }).catch(err => {
                console.error('Failed to copy: ', err);
                fallbackCopyToClipboard(text);
            });
        } else {
            fallbackCopyToClipboard(text);
        }
    };

    function fallbackCopyToClipboard(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            window.showToast('{{ __("tickets::messages.copied_success") }}', 'success');
        } catch (err) {
            console.error('Fallback copy failed', err);
        }
        document.body.removeChild(textArea);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Handle Session Messages with SweetAlert
        @if(session('success'))
            showToast('{{ session("success") }}', 'success');
        @endif
        @if(session('error'))
            showToast('{{ session("error") }}', 'error');
        @endif

        // Initialize Select2 in modal
        $('#assignModalDetail').on('shown.bs.modal', function () {
            $('.select2-show').select2({
                dropdownParent: $('#assignModalDetail'),
                width: '100%'
            });
        });

        // Handle Reply Type Switching
        const replyTypeInput = document.getElementById('reply_type');
        const replyTabs = document.querySelectorAll('.reply-compose-box .nav-link');
        if (replyTabs && replyTypeInput) {
            replyTabs.forEach(link => {
                link.addEventListener('shown.bs.tab', function(e) {
                    const target = e.target.getAttribute('href');
                    if (target === '#p-internal') {
                        replyTypeInput.value = 'internal_note';
                    } else {
                        replyTypeInput.value = 'message';
                    }
                });
            });
        }
    });

    /**
     * Scroll to Activity Log and activate tab
     */
    function scrollToActivity() {
        const tabEl = document.querySelector('[href="#activity-tab"]');
        if (tabEl) {
            const tab = new bootstrap.Tab(tabEl);
            tab.show();
            setTimeout(() => {
                const target = document.getElementById('activity-tab');
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 150);
        }
    }

    /**
     * Silent Printing via Iframe
     */
    function printTicket(url) {
        const frame = document.getElementById('printFrame');
        if (!frame) return;
        frame.src = url;
        frame.onload = function() {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        };
    }

    /**
     * Confirm ticket closure (Exclusively using SweetAlert2)
     */
    function confirmClose() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '{{ __("tickets::messages.confirm_delete") }}',
                text: '{{ __("tickets::messages.close_ticket") }}؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{ __("tickets::messages.messages.yes") }}',
                cancelButtonText: '{{ __("tickets::messages.cancel") }}',
                customClass: {
                    confirmButton: 'rounded-pill px-4',
                    cancelButton: 'rounded-pill px-4'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('closeTicketForm').submit();
                }
            });
        }
    }
</script>
@endpush
