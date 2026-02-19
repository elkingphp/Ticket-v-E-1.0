@extends('core::layouts.master')

@section('title', __('tickets::messages.view_details') . ' #' . ($ticket->ticket_number ?? substr($ticket->uuid, 0, 8)))

@section('content')

<!-- Header Stats / Breadcrumbs -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-0 rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center p-4 bg-white">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary border-opacity-10 px-3 py-1 rounded-pill fs-11 fw-bold text-uppercase tracking-wider">
                                <i class="ri-ticket-2-fill me-1 align-bottom"></i> #{{ $ticket->ticket_number ?? substr($ticket->uuid, 0, 8) }}
                            </span>
                            <span class="ms-2 badge bg-{{ $ticket->status->color ?? 'secondary' }}-subtle text-{{ $ticket->status->color ?? 'secondary' }} border border-{{ $ticket->status->color ?? 'secondary' }} border-opacity-10 px-3 py-1 rounded-pill fs-11 fw-bold text-uppercase tracking-wider">
                                {{ $ticket->status->name }}
                            </span>
                        </div>
                        <h2 class="fw-bold mb-0 text-dark ls-tight">{{ $ticket->subject }}</h2>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-end d-none d-sm-block">
                                <p class="text-muted mb-0 small fw-bold text-uppercase opacity-50">{{ __('tickets::messages.created_at') }}</p>
                                <h6 class="mb-0 fw-bold text-primary">{{ $ticket->created_at->format('d M, Y') }}</h6>
                            </div>
                            <div class="avatar-sm">
                                <div class="avatar-title bg-soft-primary text-primary rounded-circle fs-20 shadow-sm border border-primary border-opacity-10">
                                    <i class="ri-user-received-2-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- LEFT SIDEBAR (RTL Focus) -->
    <div class="col-xl-3">
        <div class="sticky-side-div">
            <!-- SLA Status Card -->
            <div class="card border-0 shadow-sm mb-4 overflow-hidden rounded-4">
                <div class="card-header border-0 bg-sla bg-gradient p-3">
                    <h5 class="card-title mb-0 fs-14 fw-bold"><i class="ri-timer-flash-line align-bottom me-1"></i> {{ __('tickets::messages.sla') }}</h5>
                </div>
                <div class="card-body p-4 bg-sla bg-opacity-5">
                    <div class="mb-4">
                        <p class="text-muted mb-1 fs-11 fw-bold text-uppercase tracking-widest opacity-75">{{ __('tickets::messages.sla_start') }}</p>
                        <h6 class="mb-0 fs-13 fw-bold text-dark"><i class="ri-calendar-line text-info me-2"></i> {{ $ticket->created_at->format('d/m/Y - h:i A') }}</h6>
                    </div>
                    <div class="mb-4">
                        <p class="text-muted mb-1 fs-11 fw-bold text-uppercase tracking-widest opacity-75">{{ __('tickets::messages.sla_end') }}</p>
                        <h6 class="mb-0 fs-13 fw-bold text-danger"><i class="ri-alarm-warning-line text-danger me-2"></i> {{ $ticket->due_at ? $ticket->due_at->format('d/m/Y - h:i A') : '--/--/----' }}</h6>
                    </div>
                    <div class="mb-0">
                        <p class="text-muted mb-1 fs-11 fw-bold text-uppercase tracking-widest opacity-75">{{ __('tickets::messages.first_action_time') }}</p>
                        <h6 class="mb-0 fs-13 fw-bold text-success"><i class="ri-checkbox-circle-fill text-success me-2"></i> 
                            @php $firstAction = $ticket->threads->where('user_id', '!=', $ticket->user_id)->first(); @endphp
                            {{ $firstAction ? $firstAction->created_at->format('d/m/Y - h:i A') : '--/--/----' }}
                        </h6>
                    </div>
                </div>
            </div>

            <!-- Meta Data Card -->
            <div class="card border-0 shadow-sm mb-4 rounded-4">
                <div class="card-header border-bottom bg-white p-3">
                    <h5 class="card-title mb-0 fs-14 fw-bold text-dark">{{ __('tickets::messages.ticket_info') }}</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted fs-11 text-uppercase fw-bold opacity-50 mb-1">{{ __('tickets::messages.priority') }}</label>
                        <div class="p-2 rounded bg-light border border-light fw-bold text-{{ $ticket->priority->color ?? 'dark' }} fs-13">
                            <i class="ri-shield-flash-line me-2"></i> {{ $ticket->priority->name }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fs-11 text-uppercase fw-bold opacity-50 mb-1">{{ __('tickets::messages.category') }}</label>
                        <div class="p-2 rounded bg-light border border-light fw-bold text-dark fs-13 text-truncate">
                            <i class="ri-folders-line text-primary me-2"></i> {{ $ticket->category->name ?? '-' }}
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-muted fs-11 text-uppercase fw-bold opacity-50 mb-1">{{ __('tickets::messages.complaint') }}</label>
                        <div class="p-2 rounded bg-light border border-light fw-bold text-dark fs-13">
                            <i class="ri-error-warning-line text-warning me-2"></i> {{ $ticket->complaint->name ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="col-xl-9">
        <div class="card border-0 shadow-sm mb-5 rounded-4 overflow-hidden">
            <div class="card-body p-4 p-md-5">
                <!-- Metadata Section -->
                <div class="mb-5 border-bottom border-bottom-dashed pb-4">
                    <div class="d-flex flex-wrap gap-4 text-muted fs-13">
                        <div class="d-flex align-items-center">
                            <div class="p-1 bg-soft-info rounded-circle me-2 d-flex">
                                <i class="ri-user-add-line text-info fs-14"></i>
                            </div>
                            <span>{{ __('tickets::messages.created_by') }}: 
                                <strong class="text-dark ms-1">
                                    @if($createdActivity && $createdActivity->user)
                                        {{ $createdActivity->user->full_name }}
                                        @if($createdActivity->user_id == $ticket->user_id)
                                            <span class="badge bg-soft-success text-success border-0 px-2 ms-1 fs-10 fw-bold rounded-pill">{{ __('tickets::messages.you') }}</span>
                                        @endif
                                    @else
                                        {{ __('tickets::messages.system') ?? 'System' }}
                                    @endif
                                </strong>
                            </span>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="p-1 bg-soft-success rounded-circle me-2 d-flex">
                                <i class="ri-user-star-line text-success fs-14"></i>
                            </div>
                            <span>{{ __('tickets::messages.student_subject') }}: 
                                <strong class="text-dark ms-1">{{ $ticket->user->full_name }}</strong>
                            </span>
                        </div>

                        @if($createdActivity && $createdActivity->user_id == $ticket->user_id)
                        <div class="d-flex align-items-center">
                            <span class="badge bg-soft-warning text-warning border border-warning border-opacity-10 px-2 py-1 fs-11 rounded-pill">
                                <i class="ri-information-line me-1 align-bottom"></i> {{ __('tickets::messages.opened_by_student') }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-5">
                    <h6 class="text-uppercase fw-bold text-muted mb-4 fs-12 tracking-widest">
                        <span class="bg-primary text-white p-2 rounded-3 me-2 shadow-sm"><i class="ri-chat-quote-fill"></i></span> {{ __('tickets::messages.details') }}
                    </h6>
                    <div class="p-4 p-md-5 bg-light bg-opacity-30 rounded-4 border border-light position-relative" style="line-height: 2; font-size: 16px; color: #1e293b;">
                        <span class="position-absolute top-0 start-0 p-3 opacity-10"><i class="ri-double-quotes-l fs-60"></i></span>
                        <div style="white-space: pre-wrap; position: relative; z-index: 1;">{{ $ticket->details }}</div>
                    </div>
                </div>

                <!-- Sub-Complaints -->
                @if($ticket->subComplaints->count() > 0)
                <div class="mb-5">
                    <h6 class="text-uppercase fw-bold text-muted mb-3 fs-12 tracking-widest">{{ __('tickets::messages.sub_complaints') }}</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($ticket->subComplaints as $sub)
                            <span class="badge bg-white shadow-sm text-dark border border-soft-secondary px-3 py-2 fs-12 rounded-pill fw-bold">
                                <i class="ri-checkbox-circle-fill text-success me-1"></i> {{ $sub->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Attachments Section (Premium Redesign) -->
                @if($ticket->attachments->count() > 0)
                <div class="mb-0">
                    <h6 class="text-uppercase fw-bold text-muted mb-3 fs-12 tracking-widest d-flex align-items-center">
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
                            <div class="file-premium-card hover-shadow transition-all border rounded-4 p-0 bg-white overflow-hidden">
                                <div class="d-flex align-items-center p-3">
                                    <div class="avatar-sm flex-shrink-0">
                                        <div class="avatar-title {{ $iconBg }} {{ $iconColor }} rounded-3 fs-24 shadow-sm">
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
                                    <div class="flex-shrink-0 ms-2 d-flex gap-1">
                                        <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="btn btn-icon btn-sm btn-ghost-secondary rounded-circle shadow-none" title="View"><i class="ri-eye-line fs-16"></i></a>
                                        <a href="{{ Storage::url($attachment->file_path) }}" download class="btn btn-icon btn-sm btn-soft-primary rounded-circle shadow-none" title="Download"><i class="ri-download-cloud-2-line fs-16"></i></a>
                                    </div>
                                </div>
                                <div class="card-footer bg-light bg-opacity-50 p-2 border-top-0 d-flex justify-content-center">
                                    <span class="text-muted fs-11 fw-medium"><i class="ri-history-line me-1"></i> {{ $attachment->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- REPLIES AREA -->
        <div class="replies-header d-flex align-items-center mb-4 mt-5">
            <h4 class="fw-bold mb-0 text-dark fs-18"><i class="ri-discuss-line me-2 text-primary fs-22 align-bottom"></i> {{ __('tickets::messages.replies') }}</h4>
            <span class="ms-3 badge bg-soft-primary text-primary rounded-pill px-3 py-2 fw-bold shadow-none border bin-opacity-10">{{ $ticket->threads->count() }}</span>
        </div>

        @foreach($ticket->threads->sortByDesc('created_at') as $thread)
        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden {{ $thread->user_id == $ticket->user_id ? 'border-start border-4 border-primary' : 'border-start border-4 border-success' }}">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-{{ $thread->user_id == $ticket->user_id ? 'primary' : 'success' }}-subtle text-{{ $thread->user_id == $ticket->user_id ? 'primary' : 'success' }} fs-14 fw-bold rounded-circle shadow-sm">
                                {{ substr($thread->user->name, 0, 1) }}
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="fs-15 mb-0 fw-bold text-dark">
                                {{ $thread->user->full_name }}
                                @if($thread->user->hasRole('agent') || $thread->user->hasRole('admin'))
                                    <span class="badge bg-success text-white border-0 ms-2 fs-10 fw-bold rounded-pill text-uppercase tracking-wider shadow-sm">{{ __('tickets::messages.support_desk') }}</span>
                                @endif
                            </h6>
                            <span class="text-muted fs-11 fw-bold bg-light px-3 py-1 rounded-pill"><i class="ri-time-line me-1"></i> {{ $thread->created_at->format('d M, h:i A') }}</span>
                        </div>
                    </div>
                </div>
                <div class="text-dark fs-15 lh-lg opacity-90" style="white-space: pre-wrap;">{{ $thread->content }}</div>
                
                @if($thread->attachments->count() > 0)
                <div class="mt-4 pt-4 border-top border-top-dashed">
                    <div class="d-flex flex-wrap gap-3">
                        @foreach($thread->attachments as $att)
                        @php
                            $attExt = pathinfo($att->file_name, PATHINFO_EXTENSION);
                            $attIcon = 'ri-file-line';
                            if (in_array($attExt, ['jpg', 'jpeg', 'png'])) $attIcon = 'ri-image-line';
                            elseif ($attExt == 'pdf') $attIcon = 'ri-file-pdf-line';
                        @endphp
                        <div class="thread-attachment-pill d-flex align-items-center bg-light rounded-pill px-3 py-1 border transition-all hover-shadow-sm">
                            <i class="{{ $attIcon }} text-primary me-2"></i>
                            <a href="{{ Storage::url($att->file_path) }}" target="_blank" class="text-dark fw-bold fs-12 text-truncate me-2" style="max-width: 150px;">{{ $att->file_name }}</a>
                            <div class="divider-vertical mx-1"></div>
                            <a href="{{ Storage::url($att->file_path) }}" download class="text-primary fs-14 transition-all hover-scale"><i class="ri-download-2-line"></i></a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach

        @if(!$ticket->status->is_final)
        <!-- REPLY FORM -->
        <div class="card border-0 shadow-sm mt-5 rounded-4 overflow-hidden border-top border-4 border-primary">
            <div class="card-header bg-white p-4 p-md-5 border-0">
                <h5 class="fw-bold mb-0 text-primary fs-16"><i class="ri-reply-fill me-2 fs-20 align-bottom"></i> {{ __('tickets::messages.add_reply') }}</h5>
            </div>
            <div class="card-body p-4 p-md-5 pt-0">
                <form action="{{ route('tickets.update', $ticket->uuid) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <textarea class="form-control bg-light border-0 shadow-none p-4 fs-14 rounded-4" name="message" rows="5" placeholder="{{ __('tickets::messages.add_reply') }}..." required style="resize: none;"></textarea>
                    </div>
                    <div class="d-sm-flex align-items-center justify-content-between gap-3">
                        <div class="flex-grow-1">
                            <label class="btn btn-soft-secondary border-dashed w-100 w-sm-auto mb-0 px-4 py-2 fw-bold fs-13 cursor-pointer">
                                <input type="file" name="attachments[]" multiple class="d-none">
                                <i class="ri-attachment-2 fs-16 me-1 align-bottom"></i> {{ __('tickets::messages.attachments') }}
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
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<style>
    .ls-tight { letter-spacing: -0.025em; }
    .tracking-widest { letter-spacing: 0.15em; }
    .transition-all { transition: all 0.3s ease; }
    .hover-shadow:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.08) !important; }
    .sticky-side-div { position: sticky; top: 80px; }
    .bg-sla{background-color: #f3f6f9;}
    
    /* Layout Swap for RTL */
    [dir="rtl"] .row { flex-direction: row-reverse; }
    [dir="rtl"] .ms-3 { margin-right: 1rem !important; margin-left: 0 !important; }
    [dir="rtl"] .ms-2 { margin-right: 0.5rem !important; margin-left: 0 !important; }
    [dir="rtl"] .me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
    [dir="rtl"] .ms-auto { margin-right: auto !important; margin-left: 0 !important; }
    [dir="rtl"] .text-end { text-align: left !important; }

    .file-premium-card { border-color: #f1f3f5 !important; }
    .file-premium-card:hover { border-color: var(--vz-primary) !important; }
    .link-primary-hover:hover { color: var(--vz-primary) !important; text-decoration: underline; }
    .thread-attachment-pill:hover { background-color: #fff !important; border-color: var(--vz-primary) !important; }
    .divider-vertical { width: 1px; height: 16px; background-color: #e9ecef; }
    .hover-scale:hover { transform: scale(1.2); }
    .hover-shadow-sm:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .uppercase-badge { font-family: monospace; }
    .cursor-pointer { cursor: pointer; }
</style>
@endpush
