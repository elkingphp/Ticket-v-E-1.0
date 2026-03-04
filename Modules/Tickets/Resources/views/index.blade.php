@extends('core::layouts.master')

@section('title', __('tickets::messages.my_tickets'))

@section('content')

    <!-- Header Section with Greeting and Global Search -->
    <div class="row mb-4 pb-1">
        <div class="col-12">
            <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-24 fw-bold mb-1 text-dark tracking-tight">{{ __('tickets::messages.my_tickets') }}</h4>
                    <p class="text-muted mb-0 fs-13">{{ __('tickets::messages.create_ticket_help') }}</p>
                </div>
                <div class="mt-3 mt-lg-0">
                    <form action="{{ route('tickets.index') }}" method="GET" class="d-flex align-items-center gap-2">
                        @if (request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        <div class="search-box position-relative">
                            <input type="text" name="search"
                                class="form-control border-light rounded-pill shadow-sm ps-4 pe-5"
                                placeholder="{{ __('tickets::messages.search') }}" value="{{ request('search') }}"
                                style="min-width: 320px; height: 46px; background: rgba(255,255,255,0.8); backdrop-filter: blur(5px);">
                            <i
                                class="ri-search-2-line search-icon position-absolute top-50 translate-middle-y end-0 me-3 text-primary fs-18"></i>
                        </div>
                        @if (request('search') || request('status'))
                            <a href="{{ route('tickets.index') }}"
                                class="btn btn-soft-danger btn-icon rounded-circle shadow-sm"
                                style="height: 46px; width: 46px;" title="{{ __('tickets::messages.reset') }}">
                                <i class="ri-refresh-line fs-18"></i>
                            </a>
                        @endif
                        <a href="{{ route('tickets.create') }}" class="btn btn-create-premium shadow-lg">
                            <i class="ri-add-line fw-bold"></i>
                            <span>{{ __('tickets::messages.create_ticket') }}</span>
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Widgets (Professional Premium Redesign) -->
    <div class="row mt-2">
        <div class="col-xl-3 col-md-6">
            <div
                class="card border-0 shadow-sm rounded-4 mb-4 stats-card-premium bg-white border-start border-4 border-primary">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-bold text-muted mb-2 fs-11 tracking-widest opacity-75">
                                {{ __('tickets::messages.total_tickets') }}</p>
                            <h4 class="fs-26 fw-extrabold mb-0 text-dark counter-value"
                                data-target="{{ $stats['total'] ?? 0 }}">{{ $stats['total'] ?? 0 }}</h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-primary text-primary rounded-circle fs-22 shadow-sm">
                                <i class="ri-ticket-2-fill"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div
                class="card border-0 shadow-sm rounded-4 mb-4 stats-card-premium bg-white border-start border-4 border-warning">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-bold text-muted mb-2 fs-11 tracking-widest opacity-75">
                                {{ __('tickets::messages.open_tickets') }}</p>
                            <h4 class="fs-26 fw-extrabold mb-0 text-dark counter-value"
                                data-target="{{ $stats['open'] ?? 0 }}">{{ $stats['open'] ?? 0 }}</h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-warning text-warning rounded-circle fs-22 shadow-sm">
                                <i class="ri-flashlight-fill"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div
                class="card border-0 shadow-sm rounded-4 mb-4 stats-card-premium bg-white border-start border-4 border-success">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-bold text-muted mb-2 fs-11 tracking-widest opacity-75">
                                {{ __('tickets::messages.resolved') }}</p>
                            <h4 class="fs-26 fw-extrabold mb-0 text-dark counter-value"
                                data-target="{{ $stats['resolved'] ?? 0 }}">{{ $stats['resolved'] ?? 0 }}</h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-success text-success rounded-circle fs-22 shadow-sm">
                                <i class="ri-shield-check-fill"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-lg rounded-4 mb-4 bg-primary bg-gradient-premium stats-card-premium cta-card">
                <div class="card-body p-0">
                    <a href="{{ route('tickets.create') }}"
                        class="d-flex align-items-center justify-content-center p-4 text-white text-decoration-none h-100 transition-all">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-xs">
                                <span class="avatar-title bg-white text-primary rounded-circle fs-16 shadow pulse-white">
                                    <i class="ri-add-fill fw-bold"></i>
                                </span>
                            </div>
                            <h5 class="mb-0 fw-bold fs-16 text-white tracking-tight">
                                {{ __('tickets::messages.create_ticket') }}</h5>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
                <div class="card-header bg-white border-0 py-4 px-4 border-bottom border-light">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="card-title mb-0 fw-extrabold text-dark fs-17 tracking-tight">
                                {{ __('tickets::messages.records') }}</h5>
                            <div class="dropdown">
                                <button class="btn btn-soft-primary btn-sm rounded-pill px-3 fs-12 fw-bold"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-equalizer-line align-middle me-1"></i>
                                    {{ __('tickets::messages.filter') }}
                                    @if (request('status'))
                                        <span class="badge bg-primary rounded-circle ms-1 p-1"
                                            style="width: 5px; height: 5px;"> </span>
                                    @endif
                                </button>
                                <ul class="dropdown-menu shadow-lg border-0 rounded-4 py-2 mt-2" style="min-width: 200px;">
                                    <li>
                                        <h6
                                            class="dropdown-header text-muted text-uppercase fs-10 tracking-widest fw-bold py-2">
                                            {{ __('tickets::messages.status') }}</h6>
                                    </li>
                                    <li><a class="dropdown-item fs-13 py-2 px-3 fw-medium {{ !request('status') ? 'active bg-soft-primary text-primary' : '' }}"
                                            href="{{ route('tickets.index', request()->except('status')) }}">{{ __('tickets::messages.all_tickets') }}</a>
                                    </li>
                                    <li><a class="dropdown-item fs-13 py-2 px-3 fw-medium {{ request('status') == 'open' ? 'active bg-soft-primary text-primary' : '' }}"
                                            href="{{ route('tickets.index', array_merge(request()->all(), ['status' => 'open'])) }}">{{ __('tickets::messages.open_tickets') }}</a>
                                    </li>
                                    <li><a class="dropdown-item fs-13 py-2 px-3 fw-medium {{ request('status') == 'resolved' ? 'active bg-soft-primary text-primary' : '' }}"
                                            href="{{ route('tickets.index', array_merge(request()->all(), ['status' => 'resolved'])) }}">{{ __('tickets::messages.resolved') }}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="text-muted fs-12 fw-bold text-uppercase tracking-wider opacity-75">
                            {{ $tickets->firstItem() ?? 0 }}-{{ $tickets->lastItem() ?? 0 }}
                            {{ __('tickets::messages.by') ?? 'of' }} {{ $tickets->total() }}
                            {{ __('tickets::messages.records') }}
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="ticketTable">
                            <thead class="table-light fs-11 text-uppercase fw-bold text-muted border-top-0 border-bottom">
                                <tr>
                                    <th class="ps-4 py-3" style="width: 140px;">{{ __('tickets::messages.id') }}</th>
                                    <th class="py-3">{{ __('tickets::messages.subject') }}</th>
                                    <th class="py-3">{{ __('tickets::messages.lecture_data') }}</th>
                                    <th class="py-3">{{ __('tickets::messages.category') }}</th>
                                    <th class="py-3" style="width: 160px;">{{ __('tickets::messages.status') }}</th>
                                    <th class="py-3" style="width: 130px;">{{ __('tickets::messages.priority') }}</th>
                                    <th class="py-3" style="width: 180px;">{{ __('tickets::messages.created_at') }}
                                    </th>
                                    <th class="pe-4 text-end py-3" style="width: 100px;">
                                        {{ __('tickets::messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="border-0">
                                @forelse($tickets as $ticket)
                                    <tr class="transition-all hover-row border-bottom border-light border-opacity-50">
                                        <td class="ps-4">
                                            <div
                                                class="ticket-id-pill bg-light text-dark fw-bold px-3 py-2 rounded-3 fs-12 border border-light d-inline-block shadow-sm">
                                                <span
                                                    class="opacity-25 pb-1">#</span>{{ $ticket->ticket_number ?? substr($ticket->uuid, 0, 8) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="py-2">
                                                <h6 class="fs-15 mb-1 fw-bold text-dark-emphasis">
                                                    <a href="{{ route('tickets.show', $ticket->uuid) }}"
                                                        class="text-dark link-primary transition-all">{{ $ticket->subject }}</a>
                                                </h6>
                                                @if ($ticket->stage)
                                                    <div class="d-flex align-items-center gap-1 opacity-75">
                                                        <i class="ri-stack-line text-primary fs-12"></i>
                                                        <span
                                                            class="text-muted fs-11 fw-bold">{{ $ticket->stage->name }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if ($ticket->lecture)
                                                <div class="d-flex align-items-center gap-1">
                                                    <i class="ri-slideshow-line text-info fs-12"></i>
                                                    <span
                                                        class="text-dark fw-medium fs-11">{{ $ticket->lecture->sessionType->name ?? __('tickets::messages.generic_lecture') }}</span>
                                                </div>
                                                <div class="text-muted fs-10">
                                                    {{ $ticket->lecture->starts_at->format('d/m H:i') }}</div>
                                            @else
                                                <span class="text-muted fs-12">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div
                                                class="badge bg-soft-info text-info border border-info border-opacity-10 px-2 py-1 rounded-pill fs-11 fw-bold">
                                                {{ $ticket->category?->name ?? '---' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div
                                                    class="badge-dot p-1 bg-{{ $ticket->status->color ?? 'secondary' }} rounded-circle animate-pulse-ring">
                                                </div>
                                                <span
                                                    class="text-{{ $ticket->status->color ?? 'secondary' }} fw-extrabold fs-12 text-uppercase tracking-wider">
                                                    {{ $ticket->status->name ?? 'Unknown' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $priorityClass = 'text-info';
                                                $bgClass = 'bg-info bg-opacity-10';
                                                $icon = 'ri-flashlight-line';
                                                if (
                                                    $ticket->priority?->name == 'High' ||
                                                    $ticket->priority?->name == 'Urgent'
                                                ) {
                                                    $priorityClass = 'text-danger';
                                                    $bgClass = 'bg-danger bg-opacity-10';
                                                    $icon = 'ri-flashlight-fill';
                                                } elseif ($ticket->priority?->name == 'Medium') {
                                                    $priorityClass = 'text-warning';
                                                    $bgClass = 'bg-warning bg-opacity-10';
                                                    $icon = 'ri-flashlight-fill';
                                                }
                                            @endphp
                                            <div
                                                class="d-inline-flex align-items-center gap-2 {{ $bgClass }} {{ $priorityClass }} px-3 py-1 rounded-pill border-0">
                                                <i class="{{ $icon }} fs-14"></i>
                                                <span
                                                    class="fw-extrabold fs-11 text-uppercase">{{ $ticket->priority->name ?? 'Normal' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <span class="text-dark fw-bold fs-13"><i
                                                        class="ri-calendar-event-line text-primary me-1"></i>
                                                    {{ $ticket->created_at->format('d M, Y') }}</span>
                                                <span
                                                    class="text-muted fs-11 fw-bold opacity-75">{{ $ticket->created_at->diffForHumans() }}</span>
                                            </div>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="d-flex justify-content-end">
                                                <a href="{{ route('tickets.show', $ticket->uuid) }}"
                                                    class="btn btn-soft-primary btn-icon btn-sm rounded-circle d-flex align-items-center justify-content-center transition-all hover-scale"
                                                    style="width: 32px; height: 32px;">
                                                    <i class="ri-arrow-right-line fs-16" dir="ltr"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <div class="text-center py-5 my-5">
                                                <div
                                                    class="avatar-xl bg-soft-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center shadow-inset-sm">
                                                    <i class="ri-ticket-2-line fs-60 text-muted opacity-25"></i>
                                                </div>
                                                <h4 class="fw-extrabold text-dark-emphasis mb-2">
                                                    {{ __('tickets::messages.no_tickets_found') }}</h4>
                                                <p class="text-muted mx-auto fs-14 opacity-75 mb-4"
                                                    style="max-width: 400px;">{{ __('tickets::messages.no_data_found') }}
                                                </p>
                                                <a href="{{ route('tickets.create') }}"
                                                    class="btn btn-primary btn-lg rounded-pill px-5 shadow-lg fw-bold transition-all hover-scale">
                                                    <i class="ri-add-fill me-2 fs-20 align-middle"></i>
                                                    {{ __('tickets::messages.create_ticket') }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($tickets->hasPages())
                    <div class="card-footer bg-white border-0 py-5 px-4 shadow-inset-lg">
                        <div class="d-flex justify-content-center">
                            {{ $tickets->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        /* UX/UI Premium Enhancement Styles */
        .hover-row {
            transition: all 0.2s ease-in-out;
            position: relative;
        }

        .hover-row:hover {
            background-color: rgba(64, 81, 137, 0.04) !important;
        }

        /* Elegant hover indicator instead of translateX to avoid scroll issues */
        .hover-row:hover td:first-child {
            box-shadow: inset 4px 0 0 -1px var(--vz-primary);
        }

        [dir="rtl"] .hover-row:hover td:first-child {
            box-shadow: inset -4px 0 0 -1px var(--vz-primary);
        }

        .stats-card-premium {
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 1px solid rgba(0, 0, 0, 0.03) !important;
            position: relative;
            z-index: 1;
        }

        .stats-card-premium:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
        }

        .btn-create-premium {
            background: linear-gradient(135deg, var(--vz-primary) 0%, #5a66c3 100%);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 10px 28px;
            font-weight: 800;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            height: 46px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 20px -10px rgba(64, 81, 137, 0.5);
        }

        .btn-create-premium:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 30px -10px rgba(64, 81, 137, 0.6);
            background: linear-gradient(135deg, #5a66c3 0%, var(--vz-primary) 100%);
        }

        .btn-create-premium i {
            font-size: 20px;
            transition: transform 0.5s ease;
            display: inline-block;
        }

        .btn-create-premium:hover i {
            transform: rotate(180deg);
        }

        .bg-gradient-premium {
            background: linear-gradient(135deg, var(--vz-primary) 0%, #405189 100%) !important;
        }

        .cta-card {
            cursor: pointer;
            border: none !important;
        }

        .cta-card:hover {
            opacity: 0.95;
        }

        .animate-pulse-ring {
            animation: pulse-ring 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse-ring {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: .6;
                transform: scale(1.2);
            }
        }

        .pulse-white {
            animation: shadow-pulse-white 2s infinite;
        }

        @keyframes shadow-pulse-white {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.5);
            }

            70% {
                box-shadow: 0 0 0 12px rgba(255, 255, 255, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }

        .shadow-inset-sm {
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.03);
        }

        .shadow-inset-lg {
            box-shadow: inset 0 8px 16px rgba(0, 0, 0, 0.02);
        }

        .tracking-tight {
            letter-spacing: -0.015em;
        }

        .tracking-widest {
            letter-spacing: 0.12em;
        }

        .link-primary:hover {
            color: var(--vz-primary) !important;
            text-decoration: none !important;
        }

        .hover-scale {
            transition: all 0.2s ease;
        }

        .hover-scale:hover {
            transform: scale(1.1);
        }

        /* Perfect RTL Alignment */
        [dir="rtl"] .search-box .search-icon {
            left: 0 !important;
            right: auto !important;
            margin-left: 1.25rem;
            margin-right: 0 !important;
        }

        [dir="rtl"] .search-box input {
            padding-right: 1.5rem !important;
            padding-left: 3.5rem !important;
        }

        [dir="rtl"] .btn-label.ms-2 {
            margin-right: 0.75rem !important;
            margin-left: 0 !important;
        }

        [dir="rtl"] .ticket-id-pill span {
            margin-left: 2px !important;
            margin-right: 0 !important;
        }

        [dir="rtl"] .btn-label i.label-icon {
            margin-left: 14px !important;
            margin-right: -10px !important;
        }
    </style>

@endsection
