@extends('core::layouts.master')

@section('title', __('Edit Email Template'))

@section('content')
<div class="row">
    <div class="col-12 col-lg-8 mx-auto">
        <div class="card mb-4" style="border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
            <div class="card-header pb-0 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold" style="color: #334155;">{{ __('Edit Email Template') }}</h5>
                    <a href="{{ route('admin.tickets.templates.index') }}" class="btn btn-sm btn-outline-secondary mb-0">
                        <i class="fas fa-arrow-left me-2"></i>{{ __('Back') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.tickets.templates.update', $template->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group mb-4">
                        <label for="event_key" class="form-control-label">{{ __('Event Key') }}</label>
                        <input type="text" class="form-control" name="event_key" value="{{ $template->event_key }}" disabled readonly>
                        <small class="text-muted d-block mt-1">{{ __('System identifier for this event. Cannot be changed.') }}</small>
                    </div>

                    <div class="form-group mb-4">
                        <label for="subject" class="form-control-label">{{ __('Subject') }}</label>
                        <input type="text" class="form-control" name="subject" value="{{ $template->subject }}" required>
                    </div>

                    <div class="form-group mb-4">
                        <label for="body" class="form-control-label">{{ __('Body Content') }}</label>
                        <textarea class="form-control" name="body" rows="12" style="font-family: monospace; font-size: 14px;" required>{{ $template->body }}</textarea>
                        <div class="alert alert-light mt-3" role="alert">
                            <h6 class="alert-heading text-xs font-weight-bold text-uppercase text-muted mb-2">{{ __('Available Variables') }}</h6>
                            <p class="mb-0 text-sm">
                                <code class="text-primary me-2">@{{ ticket_id }}</code>
                                <code class="text-primary me-2">@{{ subject }}</code>
                                <code class="text-primary me-2">@{{ status }}</code>
                                <code class="text-primary me-2">@{{ priority }}</code>
                                <code class="text-primary me-2">@{{ assignee }}</code>
                                <code class="text-primary me-2">@{{ customer_name }}</code>
                                <code class="text-primary me-2">@{{ link }}</code>
                            </p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn bg-gradient-primary mb-0">{{ __('Update Template') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
