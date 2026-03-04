@extends('core::layouts.master')

@section('title', __('tickets::messages.edit_template'))

@section('content')
    <div class="row">
        <div class="col-12 col-lg-8 mx-auto">
            <div class="card mb-4"
                style="border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="card-header pb-0 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold" style="color: #334155;">{{ __('tickets::messages.edit_template') }}
                        </h5>
                        <a href="{{ route('admin.tickets.templates.index') }}" class="btn btn-sm btn-outline-secondary mb-0">
                            <i class="fas fa-arrow-left me-2"></i>{{ __('tickets::messages.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tickets.templates.update', $template->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-4">
                            <label for="event_key"
                                class="form-control-label">{{ __('tickets::messages.event_key') }}</label>
                            <input type="text" class="form-control" name="event_key" value="{{ $template->event_key }}"
                                disabled readonly>
                            <small class="text-muted d-block mt-1">{{ __('tickets::messages.event_key_help') }}</small>
                        </div>

                        <div class="form-group mb-4">
                            <label for="subject" class="form-control-label">{{ __('tickets::messages.subject') }}</label>
                            <input type="text" class="form-control" name="subject" value="{{ $template->subject }}"
                                required>
                        </div>

                        <div class="form-group mb-4">
                            <label for="body" class="form-control-label">{{ __('tickets::messages.details') }}</label>
                            <textarea class="form-control" name="body" rows="12" style="font-family: monospace; font-size: 14px;" required>{{ $template->body }}</textarea>
                            <div class="alert alert-info mt-3" role="alert"
                                style="background-color: #f0f7ff; border-left: 5px solid #007bff;">
                                <h6 class="alert-heading text-xs font-weight-bold text-uppercase mb-2"
                                    style="color: #0056b3;">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ __('tickets::messages.available_variables') }}
                                </h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ ticket_number }}</code> <small
                                            class="text-muted">({{ __('tickets::messages.variable_id') }})</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ subject }}</code> <small
                                            class="text-muted">({{ __('tickets::messages.variable_subject') }})</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ customer_name }}</code> <small
                                            class="text-muted">({{ __('tickets::messages.variable_customer') }})</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ status }}</code> <small
                                            class="text-muted">({{ __('tickets::messages.variable_status') }})</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ priority }}</code> <small
                                            class="text-muted">({{ __('tickets::messages.variable_priority') }})</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ assignee }}</code> <small
                                            class="text-muted">({{ __('tickets::messages.variable_assignee') }})</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ message }}</code> <small
                                            class="text-muted">({{ __('tickets::messages.variable_reply') }})</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ logo }}</code> <small
                                            class="text-muted">({{ __('tickets::messages.variable_logo') }})</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ app_name }}</code> <small
                                            class="text-muted">({{ __('tickets::messages.variable_app') }})</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ link }}</code> <small
                                            class="text-muted">({{ __('tickets::messages.variable_link') }})</small></span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit"
                                class="btn bg-gradient-primary mb-0">{{ __('tickets::messages.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
