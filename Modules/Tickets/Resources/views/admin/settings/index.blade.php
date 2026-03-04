@extends('core::layouts.master')

@section('title', __('tickets::messages.settings'))

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header border-bottom-dashed">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0"><i class="ri-settings-3-line align-bottom me-1 text-primary"></i>
                                {{ __('tickets::messages.settings') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.tickets.settings.update') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="ri-timer-2-line align-bottom me-1 text-muted"></i>
                                        {{ __('tickets::messages.auto_close') }}
                                    </label>
                                    <div class="input-group">
                                        <input type="number" name="auto_close_after_days" class="form-control"
                                            value="{{ $settings['auto_close_after_days'] }}" required min="0">
                                        <span class="input-group-text">{{ __('tickets::messages.messages.days') }}</span>
                                    </div>
                                    <div class="form-text text-muted small mt-1">
                                        {{ __('tickets::messages.auto_close_help') }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="ri-history-line align-bottom me-1 text-muted"></i>
                                        {{ __('tickets::messages.allow_reopen') }}
                                    </label>
                                    <select name="allow_reopen" class="form-select">
                                        <option value="1" {{ $settings['allow_reopen'] == 1 ? 'selected' : '' }}>
                                            {{ __('tickets::messages.messages.yes') }}</option>
                                        <option value="0" {{ $settings['allow_reopen'] == 0 ? 'selected' : '' }}>
                                            {{ __('tickets::messages.messages.no') }}</option>
                                    </select>
                                    <div class="form-text text-muted small mt-1">
                                        {{ __('tickets::messages.allow_reopen_help') }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="ri-numbers-line align-bottom me-1 text-muted"></i>
                                        {{ __('tickets::messages.max_per_day') }}
                                    </label>
                                    <input type="number" name="max_tickets_per_user_per_day" class="form-control"
                                        value="{{ $settings['max_tickets_per_user_per_day'] }}" required min="1">
                                    <div class="form-text text-muted small mt-1">
                                        {{ __('tickets::messages.max_per_day_help') }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="ri-hashtag align-bottom me-1 text-muted"></i>
                                        {{ __('tickets::messages.settings_fields.ticket_number_format') }}
                                    </label>
                                    <input type="text" name="ticket_number_format" class="form-control"
                                        value="{{ $settings['ticket_number_format'] }}" required>
                                    <div class="form-text text-muted small mt-1">
                                        {{ __('tickets::messages.settings_fields.ticket_number_format_help') }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="ri-medal-fill align-bottom me-1 text-muted"></i>
                                        {{ __('tickets::messages.admin_role') ?? 'الدور الإداري المرجعي/الافتراضي' }}
                                    </label>
                                    <select name="ticket_admin_role" class="form-select" required>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}"
                                                {{ $settings['ticket_admin_role'] == $role->name ? 'selected' : '' }}>
                                                {{ $role->display_name ?? $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted small mt-1">
                                        هذا الدور يمتلك صلاحية افتراضية لمشاهدة كافة التذاكر والمراحل المستحدثة (صلاحية تخطي
                                        الحجب).
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top border-top-dashed text-end">
                            <button type="submit" class="btn btn-primary btn-label">
                                <i class="ri-save-3-line label-icon align-middle fs-16 me-2"></i>
                                {{ __('tickets::messages.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @include('tickets::partials.delete-script')
    @endpush
@endsection
