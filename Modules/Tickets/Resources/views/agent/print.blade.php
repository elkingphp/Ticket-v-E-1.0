<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <title>{{ __('tickets::messages.tickets') }} #{{ $ticket->ticket_number ?? substr($ticket->uuid, 0, 8) }}</title>
    @if (app()->getLocale() == 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
            rel="stylesheet">
    @endif
    <style>
        :root {
            --primary: #405189;
            --primary-light: #e8ebf3;
            --secondary: #f3f6f9;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #cbd5e1;
            --danger: #f06548;
            --success: #0ab39c;
            --warning: #f7b84b;

            /* Dynamic badge colors */
            --bs-primary: #405189;
            --bs-success: #0ab39c;
            --bs-danger: #f06548;
            --bs-warning: #f7b84b;
            --bs-info: #299cdb;
            --bs-secondary: #6c757d;
            --bs-dark: #212529;
            --bs-light: #f3f6f9;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: {{ app()->getLocale() == 'ar' ? "'Cairo', sans-serif" : "'Inter', sans-serif" }};
            background: #fff;
            color: var(--text-main);
            margin: 0;
            padding: 0;
            line-height: 1.6;
            font-size: 13px;
        }

        .print-container {
            width: 100%;
            max-width: 210mm;
            /* A4 size */
            margin: 0 auto;
            padding: 10mm 15mm;
            position: relative;
            background: #fff;
        }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.03;
            z-index: 0;
            width: 60%;
            pointer-events: none;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 2px solid var(--text-main);
            padding-bottom: 15px;
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
        }

        .logo-area img {
            max-height: 55px;
            object-fit: contain;
        }

        .logo-area h1 {
            margin: 0;
            font-size: 26px;
            color: var(--text-main);
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .ticket-header-info {
            text-align: {{ app()->getLocale() == 'ar' ? 'left' : 'right' }};
        }

        .ticket-id-box {
            background: var(--primary-light);
            color: var(--primary);
            padding: 8px 15px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 8px;
            border: 1px solid rgba(64, 81, 137, 0.2);
        }

        .ticket-id-box h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
        }

        .ticket-date {
            font-size: 11px;
            color: var(--text-muted);
            margin: 0;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* 3-Column Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-item.full-width {
            grid-column: 1 / -1;
            border-bottom: 1px dashed var(--border-color);
            padding-bottom: 12px;
            margin-bottom: 5px;
        }

        .info-label {
            font-size: 10px;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-main);
        }

        .info-value.subject {
            font-size: 16px;
            color: var(--primary);
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            background: var(--primary);
            color: #fff !important;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
        }

        /* Content Area */
        .section-box {
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .section-title {
            font-size: 14px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
        }

        .section-title::before {
            content: '';
            display: inline-block;
            width: 12px;
            height: 12px;
            background: var(--primary);
            margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 8px;
            border-radius: 2px;
        }

        .details-box {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            font-size: 13px;
            line-height: 1.8;
            white-space: pre-wrap;
            background: #fff;
        }

        /* Timeline / Replies */
        .timeline {
            border-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 2px solid var(--primary-light);
            padding-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 20px;
            margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 5px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            top: 0;
            {{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: -27px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid var(--primary);
        }

        .timeline-box {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }

        .timeline-header {
            background: #f8fafc;
            padding: 8px 15px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .timeline-author {
            font-weight: 800;
            color: var(--text-main);
            font-size: 12px;
        }

        .timeline-date {
            font-size: 10px;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
        }

        .timeline-content {
            padding: 15px;
            font-size: 13px;
            color: #334155;
            white-space: pre-wrap;
            background: #fff;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid var(--text-main);
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
        }

        .print-signature {
            text-align: center;
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signature-line {
            width: 200px;
            border-bottom: 1px solid var(--text-main);
            margin: 0 auto 5px;
        }

        /* Print Specific */
        @media print {
            body {
                background: none;
            }

            .print-container {
                width: 100%;
                max-width: none;
                padding: 0;
                margin: 0;
            }

            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>

<body>
    <div class="print-container">
        <!-- Optional Watermark Logo -->
        @if (get_setting('logo_dark'))
            <img src="{{ asset(get_setting('logo_dark')) }}" class="watermark" alt="Watermark">
        @endif

        <!-- Header -->
        <div class="header">
            <div class="logo-area">
                @if (get_setting('logo_dark'))
                    <img src="{{ asset(get_setting('logo_dark')) }}" alt="Logo">
                @else
                    <h1>{{ get_setting('site_name', 'DIGILIANS') }}</h1>
                @endif
            </div>
            <div class="ticket-header-info">
                <div class="ticket-id-box">
                    <h2>#{{ $ticket->ticket_number ?? substr($ticket->uuid, 0, 8) }}</h2>
                </div>
                <p class="ticket-date">{{ __('tickets::messages.created_at') }}:
                    {{ $ticket->created_at->format('d/m/Y - h:i A') }}</p>
            </div>
        </div>

        <!-- Meta Grid -->
        <div class="info-grid">
            <div class="info-item full-width">
                <div class="info-label">{{ __('tickets::messages.subject') }}</div>
                <div class="info-value subject">{{ $ticket->subject }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">{{ __('tickets::messages.student') }}</div>
                <div class="info-value">{{ $ticket->user->full_name }}</div>
                <div style="font-size:11px; color:var(--text-muted); font-weight:600; margin-top:2px;">
                    {{ $ticket->user->email }}
                </div>
            </div>

            <div class="info-item">
                <div class="info-label">{{ __('tickets::messages.status') }} / {{ __('tickets::messages.priority') }}
                </div>
                <div class="info-value">
                    <span class="status-badge"
                        style="background-color: var(--bs-{{ $ticket->status->color ?? 'primary' }});">{{ $ticket->status->name }}</span>
                    <span style="color:#cbd5e1; margin:0 4px;">|</span>
                    {{ $ticket->priority->name }}
                </div>
            </div>

            <div class="info-item">
                <div class="info-label">{{ __('tickets::messages.category') }}</div>
                <div class="info-value">{{ $ticket->category->name ?? '-' }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">{{ __('tickets::messages.complaint') }}</div>
                <div class="info-value">{{ $ticket->complaint->name ?? '-' }}</div>
            </div>

            @if ($ticket->due_at)
                <div class="info-item">
                    <div class="info-label">{{ __('tickets::messages.sla_end') }}</div>
                    <div class="info-value" style="color: var(--danger);">
                        {{ $ticket->due_at->format('d/m/Y - h:i A') }}</div>
                </div>
            @endif
        </div>

        <!-- Description Content -->
        <div class="section-box">
            <h3 class="section-title">{{ __('tickets::messages.details') }}</h3>
            <div class="details-box">
                {{ $ticket->details }}
            </div>
        </div>

        <!-- Timeline / Replies -->
        @if ($ticket->threads->count() > 0)
            <div class="section-box" style="margin-top: 40px;">
                <h3 class="section-title">{{ __('tickets::messages.replies') }}</h3>
                <div class="timeline">
                    @foreach ($ticket->threads->sortBy('created_at') as $thread)
                        @if ($thread->type !== 'internal_note')
                            <div class="timeline-item">
                                <div class="timeline-box">
                                    <div class="timeline-header">
                                        <span class="timeline-author">{{ $thread->user->full_name }}</span>
                                        <span
                                            class="timeline-date">{{ $thread->created_at->format('d/m/Y - h:i A') }}</span>
                                    </div>
                                    <div class="timeline-content">{{ $thread->content }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer print-footer">
            <div>{{ __('tickets::messages.printed_on') ?? 'Printed on' }}: {{ date('d M Y, h:i A') }}</div>
            <div>{{ __('tickets::messages.system') ?? 'Ticketing System' }} |
                {{ get_setting('site_name', 'DIGILIANS') }}</div>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.focus();
                window.print();
            }, 500);
        });

        window.addEventListener('afterprint', function() {
            window.close();
        });
    </script>
</body>

</html>
