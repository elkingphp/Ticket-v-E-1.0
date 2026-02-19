<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('tickets::messages.tickets') }} #{{ $ticket->ticket_number ?? substr($ticket->uuid, 0, 8) }}</title>
    <style>
        body {
            font-family: 'Cairo', 'Inter', sans-serif;
            background: white;
            color: #333;
            margin: 0;
            padding: 40px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f3f6f9;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo img {
            height: 40px;
        }
        .system-info {
            text-align: {{ app()->getLocale() == 'ar' ? 'left' : 'right' }};
        }
        .ticket-title {
            margin: 0;
            font-size: 24px;
            color: #405189;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .meta-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .meta-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #777;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .meta-value {
            font-size: 14px;
            font-weight: 600;
        }
        .content-box {
            border: 1px solid #e9ebec;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }
        .details {
            white-space: pre-wrap;
            line-height: 1.6;
            font-size: 14px;
        }
        .timeline {
            margin-top: 40px;
        }
        .thread-item {
            margin-bottom: 20px;
            border-bottom: 1px solid #f3f6f9;
            padding-bottom: 15px;
        }
        .thread-header {
            font-size: 12px;
            color: #555;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }
        .thread-content {
            font-size: 14px;
            color: #222;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
    @if(app()->getLocale() == 'ar')
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    @endif
</head>
<body>
    <div class="header">
        <div class="logo">
            @if(get_setting('logo_dark'))
                <img src="{{ asset(get_setting('logo_dark')) }}" alt="Logo">
            @else
                <h2 style="margin:0">{{ get_setting('site_name', 'DIGILIANS') }}</h2>
            @endif
        </div>
        <div class="system-info">
            <h1 class="ticket-title">#{{ $ticket->ticket_number ?? substr($ticket->uuid, 0, 8) }}</h1>
            <p style="margin:5px 0; color:#666; font-size:12px">
                {{ __('tickets::messages.created_at') }}: {{ $ticket->created_at->format('Y-m-d H:i') }}
            </p>
        </div>
    </div>

    <div class="meta-grid">
        <div class="meta-item">
            <div class="meta-label">{{ __('tickets::messages.subject') }}</div>
            <div class="meta-value">{{ $ticket->subject }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">{{ __('tickets::messages.student') }}</div>
            <div class="meta-value">{{ $ticket->user->full_name }} ({{ $ticket->user->email }})</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">{{ __('tickets::messages.status') }}</div>
            <div class="meta-value">{{ $ticket->status->name }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">{{ __('tickets::messages.priority') }}</div>
            <div class="meta-value">{{ $ticket->priority->name }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">{{ __('tickets::messages.category') }}</div>
            <div class="meta-value">{{ $ticket->category->name ?? '-' }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">{{ __('tickets::messages.complaint') }}</div>
            <div class="meta-value">{{ $ticket->complaint->name ?? '-' }}</div>
        </div>
    </div>

    <div class="content-box">
        <div class="section-title">{{ __('tickets::messages.details') }}</div>
        <div class="details">{{ $ticket->details }}</div>
    </div>

    @if($ticket->threads->count() > 0)
    <div class="timeline">
        <div class="section-title">{{ __('tickets::messages.replies') }}</div>
        @foreach($ticket->threads->sortBy('created_at') as $thread)
            @if($thread->type !== 'internal_note')
            <div class="thread-item">
                <div class="thread-header">
                    <strong>{{ $thread->user->full_name }}</strong>
                    <span>{{ $thread->created_at->format('Y-m-d H:i') }}</span>
                </div>
                <div class="thread-content">{{ $thread->content }}</div>
            </div>
            @endif
        @endforeach
    </div>
    @endif

    <div style="margin-top: 50px; text-align: center; font-size: 11px; color: #aaa; border-top: 1px solid #eee; padding-top: 20px;">
        {{ __('tickets::messages.system') }} | {{ date('Y-m-d H:i:s') }}
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
