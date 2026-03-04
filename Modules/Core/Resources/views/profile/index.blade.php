@extends('core::layouts.master')

@section('title', __('core::profile.my_profile'))

@push('styles')
    <style>
        .progress-ring {
            transform: rotate(-90deg);
        }

        .progress-ring__circle {
            transition: stroke-dashoffset 0.35s;
            transform-origin: 50% 50%;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush
@section('content')
    <div class="position-relative mx-n4 mt-n4">
        <div class="profile-wid-bg profile-setting-img">
            <img src="{{ asset('assets/images/profile-bg.jpg') }}" class="profile-wid-img" alt="">
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-3">
            <div class="card mt-n5">
                <div class="card-body p-4">
                    <div class="text-center">
                        <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                            @if ($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}"
                                    class="rounded-circle avatar-xl img-thumbnail user-profile-image shadow"
                                    alt="user-profile-image" id="user-avatar-image">
                            @else
                                <div class="avatar-xl img-thumbnail rounded-circle bg-secondary d-flex align-items-center justify-content-center shadow mx-auto"
                                    id="user-avatar-initials">
                                    <span class="fs-24 fw-bold text-white">{{ $user->initials }}</span>
                                </div>
                                <img src=""
                                    class="rounded-circle avatar-xl img-thumbnail user-profile-image shadow d-none"
                                    alt="user-profile-image" id="user-avatar-image">
                            @endif
                            <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
                                <input id="profile-img-file-input" type="file" name="avatar"
                                    class="profile-img-file-input" accept="image/*">
                                <label for="profile-img-file-input" class="profile-photo-edit avatar-xs">
                                    <span class="avatar-title rounded-circle bg-light text-body shadow">
                                        <i class="ri-camera-fill"></i>
                                    </span>
                                </label>
                            </div>
                            @if ($user->avatar)
                                <button type="button"
                                    class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 shadow"
                                    onclick="deleteAvatar()" id="delete-avatar-btn"
                                    style="width: 28px; height: 28px; padding: 0;">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            @endif
                        </div>
                        <h5 class="fs-16 mb-1">{{ $user->full_name }}</h5>
                        <p class="text-muted mb-0">
                            {{ $user->roles->first()?->display_name ?? ($user->roles->first()?->name ?? __('core::profile.user')) }}
                        </p>
                    </div>
                </div>
            </div>
            <!--end card-->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">{{ __('core::profile.security_status') }}</h5>
                        </div>
                        <div class="flex-shrink-0">
                            <span
                                class="badge {{ $user->security_status['class'] }} fs-12">{{ $user->security_status['label'] }}</span>
                        </div>
                    </div>
                    <div class="text-center py-2">
                        <div class="position-relative d-inline-block">
                            <svg class="progress-ring" width="120" height="120">
                                <circle class="progress-ring__circle-bg" stroke="#f3f3f3" stroke-width="8"
                                    fill="transparent" r="50" cx="60" cy="60" />
                                <circle class="progress-ring__circle"
                                    stroke="{{ $user->security_status['color'] == 'success' ? '#0ab39c' : ($user->security_status['color'] == 'warning' ? '#f7b84b' : '#f06548') }}"
                                    stroke-width="8" stroke-dasharray="314.159"
                                    stroke-dashoffset="{{ 314.159 - (314.159 * $user->profile_completion_score) / 100 }}"
                                    stroke-linecap="round" fill="transparent" r="50" cx="60" cy="60" />
                            </svg>
                            <div class="position-absolute top-50 start-50 translate-middle">
                                <h4 class="mb-0">{{ $user->profile_completion_score }}%</h4>
                            </div>
                        </div>
                        <p class="text-muted mt-3 mb-0">{{ __('core::profile.security_score') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
        <div class="col-xxl-9">
            <div class="card mt-xxl-n5">
                <div class="card-header">
                    <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails" role="tab">
                                <i class="fas fa-home"></i> {{ __('core::profile.personal_details') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab">
                                <i class="far fa-user"></i> {{ __('core::profile.change_password') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#twoFactor" role="tab">
                                <i class="fas fa-shield-alt"></i> {{ __('core::profile.two_factor_auth') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#sessions" role="tab">
                                <i class="fas fa-desktop"></i> {{ __('core::profile.active_sessions') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#activities" role="tab">
                                <i class="fas fa-history"></i> {{ __('core::profile.session_history') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#notifications" role="tab">
                                <i class="fas fa-bell"></i> {{ __('core::profile.notification_preferences') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#privacy" role="tab">
                                <i class="fas fa-user-shield"></i> {{ __('core::profile.data_privacy') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content">
                        <div class="tab-pane active" id="personalDetails" role="tabpanel">
                            <form id="personal-details-form" action="{{ route('profile.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="firstnameInput"
                                                class="form-label">{{ __('core::profile.first_name') }}</label>
                                            <input type="text" class="form-control" name="first_name"
                                                id="firstnameInput"
                                                placeholder="{{ __('core::profile.enter_first_name') }}"
                                                value="{{ old('first_name', $user->first_name) }}">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="lastnameInput"
                                                class="form-label">{{ __('core::profile.last_name') }}</label>
                                            <input type="text" class="form-control" name="last_name"
                                                id="lastnameInput"
                                                placeholder="{{ __('core::profile.enter_last_name') }}"
                                                value="{{ old('last_name', $user->last_name) }}">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="phonenumberInput"
                                                class="form-label">{{ __('core::profile.phone_number') }}</label>
                                            <input type="text" class="form-control" name="phone"
                                                id="phonenumberInput"
                                                placeholder="{{ __('core::profile.enter_phone_number') }}"
                                                value="{{ old('phone', $user->phone) }}">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="emailInput"
                                                class="form-label">{{ __('core::profile.email_address') }}</label>
                                            <input type="email" class="form-control" name="email" id="emailInput"
                                                placeholder="{{ __('core::profile.enter_email') }}"
                                                value="{{ old('email', $user->email) }}">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="languageSelect"
                                                class="form-label">{{ __('core::profile.language') }}</label>
                                            <select class="form-control" name="language" id="languageSelect">
                                                <option value="en" {{ $user->language == 'en' ? 'selected' : '' }}>
                                                    English</option>
                                                <option value="ar" {{ $user->language == 'ar' ? 'selected' : '' }}>
                                                    {{ __('core::messages.lang_arabic') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="timezoneSelect"
                                                class="form-label">{{ __('core::profile.timezone') }}</label>
                                            <select class="form-control" name="timezone" id="timezoneSelect"
                                                data-choices>
                                                @foreach ($timezones as $tz)
                                                    <option value="{{ $tz }}"
                                                        {{ $user->timezone == $tz ? 'selected' : '' }}>{{ $tz }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <div
                                            class="hstack gap-2 justify-content-{{ app()->getLocale() == 'ar' ? 'start' : 'end' }}">
                                            <button type="submit"
                                                class="btn btn-primary">{{ __('core::profile.update') }}</button>
                                            <button type="button"
                                                class="btn btn-soft-secondary">{{ __('core::profile.cancel') }}</button>
                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>
                        </div>
                        <!--end tab-pane-->
                        <div class="tab-pane" id="changePassword" role="tabpanel">
                            <form id="change-password-form" action="{{ route('profile.password') }}" method="POST">
                                @csrf
                                <div class="row g-2">
                                    <div class="col-lg-4">
                                        <div>
                                            <label for="oldpasswordInput"
                                                class="form-label">{{ __('core::profile.old_password') }}</label>
                                            <input type="password" class="form-control" name="current_password"
                                                id="oldpasswordInput"
                                                placeholder="{{ __('core::profile.enter_current_password') }}">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-4">
                                        <div>
                                            <label for="newpasswordInput"
                                                class="form-label">{{ __('core::profile.new_password') }}</label>
                                            <input type="password" class="form-control" name="password"
                                                id="newpasswordInput"
                                                placeholder="{{ __('core::profile.enter_new_password') }}">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-4">
                                        <div>
                                            <label for="confirmpasswordInput"
                                                class="form-label">{{ __('core::profile.confirm_password') }}</label>
                                            <input type="password" class="form-control" name="password_confirmation"
                                                id="confirmpasswordInput"
                                                placeholder="{{ __('core::profile.confirm_new_password') }}">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <div class="text-{{ app()->getLocale() == 'ar' ? 'start' : 'end' }}">
                                            <button type="submit"
                                                class="btn btn-success">{{ __('core::profile.change_password') }}</button>
                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>
                        </div>
                        <!--end tab-pane-->
                        <div class="tab-pane" id="twoFactor" role="tabpanel">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">{{ __('core::profile.two_factor_auth') }}</h5>
                                </div>
                            </div>

                            <p class="text-muted">{{ __('core::profile.2fa_tip') }}</p>
                            <p class="text-info fs-12"><i class="ri-information-line me-1"></i>
                                {{ __('core::profile.2fa_google_tip') }}</p>

                            @if (!auth()->user()->two_factor_secret)
                                <form id="enable-2fa-form" method="POST"
                                    action="{{ url('user/two-factor-authentication') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('core::profile.enable_2fa') }}
                                    </button>
                                </form>
                            @elseif(!auth()->user()->two_factor_confirmed_at)
                                <div class="alert alert-info border-0 mb-4" role="alert">
                                    <strong>{{ __('core::profile.2fa_pending_title') }}</strong>
                                    <p class="mb-0">{{ __('core::profile.2fa_pending_tip') }}</p>
                                </div>

                                <div class="mb-4 text-center p-3 bg-light rounded">
                                    {!! auth()->user()->twoFactorQrCodeSvg() !!}
                                    <p class="mt-3 text-muted">{{ __('core::profile.setup_key') }}:
                                        <code>{{ decrypt(auth()->user()->two_factor_secret) }}</code>
                                    </p>
                                </div>

                                <form id="confirm-2fa-form" method="POST"
                                    action="{{ url('user/confirmed-two-factor-authentication') }}">
                                    @csrf
                                    <div class="row justify-content-center">
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label for="2fa_code"
                                                    class="form-label">{{ __('core::profile.enter_2fa_code') }}</label>
                                                <input type="text" class="form-control text-center fs-18 fw-bold"
                                                    name="code" id="2fa_code" maxlength="6" placeholder="000000"
                                                    required autofocus autocomplete="one-time-code">
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">
                                                {{ __('core::profile.confirm_2fa') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <div class="alert alert-success border-0 mb-4" role="alert">
                                    <strong>{{ __('core::profile.2fa_enabled') }}</strong>
                                </div>

                                <div class="mb-4">
                                    <button type="button" class="btn btn-danger" onclick="disable2FA()">
                                        {{ __('core::profile.disable_2fa') }}
                                    </button>
                                </div>

                                <script>
                                    function disable2FA() {
                                        performAction('{{ url('user/two-factor-authentication') }}', 'DELETE', {
                                            title: '{{ __('core::profile.disable_2fa') }}',
                                            text: '{{ __('core::profile.2fa_disable_confirm') }}',
                                            confirmText: '{{ __('core::profile.disable_2fa') }}'
                                        });
                                    }
                                </script>


                                <div class="mt-4">
                                    <h6>{{ __('core::profile.recovery_codes') }}</h6>
                                    <p class="text-muted">{{ __('core::profile.recovery_codes_tip') }}</p>
                                    <div class="bg-light p-3 rounded mb-3">
                                        <div class="row">
                                            @foreach (auth()->user()->recoveryCodes() as $code)
                                                <div class="col-6"><code>{{ $code }}</code></div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <form id="regenerate-codes-form" method="POST"
                                        action="{{ url('user/two-factor-recovery-codes') }}">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-soft-primary">{{ __('core::profile.regenerate_codes') }}</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <!--end tab-pane-->

                        <div class="tab-pane" id="sessions" role="tabpanel">
                            <div class="d-flex align-items-center mb-4">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">{{ __('core::profile.active_sessions') }}</h5>
                                    <p class="text-muted mb-0">{{ __('core::profile.sessions_tip') }}</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-soft-danger btn-sm"
                                        onclick="terminateOtherSessions()">
                                        <i class="ri-logout-box-line align-bottom me-1"></i>
                                        {{ __('core::profile.terminate_others') }}
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">{{ __('core::profile.device_browser') }}</th>
                                            <th scope="col">{{ __('core::profile.ip_address') }}</th>
                                            <th scope="col">{{ __('core::profile.last_activity') }}</th>
                                            <th scope="col" class="text-end">{{ __('core::profile.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sessions as $session)
                                            <tr id="session-{{ $session['id'] }}">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-xs flex-shrink-0 me-3">
                                                            <div
                                                                class="avatar-title bg-soft-primary text-primary rounded-circle fs-16">
                                                                <i
                                                                    class="{{ $session['agent']['is_mobile'] ? 'ri-smartphone-line' : 'ri-computer-line' }}"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="fs-14 mb-1">{{ $session['agent']['browser'] }} on
                                                                {{ $session['agent']['platform'] }}</h6>
                                                            <p class="text-muted mb-0 fs-12">
                                                                {{ $session['agent']['device'] ?: __('core::profile.unknown_device') }}
                                                            </p>
                                                        </div>
                                                        @if ($session['is_current_device'])
                                                            <span
                                                                class="badge bg-success-subtle text-success ms-2">{{ __('core::profile.current') }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td><code>{{ $session['ip_address'] }}</code></td>
                                                <td class="text-muted">{{ $session['last_activity'] }}</td>
                                                <td class="text-end">
                                                    @if (!$session['is_current_device'])
                                                        <button type="button" class="btn btn-icon btn-soft-danger btn-sm"
                                                            onclick="terminateSession('{{ $session['id'] }}')">
                                                            <i class="ri-close-fill"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!--end tab-pane-->

                        <div class="tab-pane" id="activities" role="tabpanel">
                            <div class="profile-timeline">
                                <div class="acitivity-timeline" id="activity-timeline">
                                    @if ($activities->count() > 0)
                                        @include('core::profile.partials.activity_items', [
                                            'activities' => $activities,
                                        ])
                                    @else
                                        <div class="text-center py-5">
                                            <i class="ri-history-line display-4 text-muted"></i>
                                            <p class="text-muted mt-2">{{ __('core::profile.no_activities') }}</p>
                                        </div>
                                    @endif
                                </div>

                                @if ($activities->hasMorePages())
                                    <div class="text-center mt-4">
                                        <button type="button" class="btn btn-soft-primary btn-sm"
                                            id="load-more-activities"
                                            data-next-page="{{ $activities->currentPage() + 1 }}">
                                            {{ __('core::profile.load_more') }}
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <!--end tab-pane-->

                        <div class="tab-pane" id="notifications" role="tabpanel">
                            <div class="d-flex align-items-center mb-4">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">{{ __('core::profile.notification_preferences') }}</h5>
                                    <p class="text-muted mb-0">{{ __('core::profile.notifications_tip') }}</p>
                                </div>
                            </div>

                            <form id="notification-preferences-form">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-nowrap align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 40%;">
                                                    {{ __('core::profile.event_description') }}</th>
                                                <th scope="col" class="text-center">{{ __('core::profile.in_app') }}
                                                </th>
                                                <th scope="col" class="text-center">{{ __('core::profile.email') }}
                                                </th>
                                                <th scope="col" class="text-center">{{ __('core::profile.browser') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($notificationTypes as $type)
                                                @php
                                                    $pref = $userPreferences->get($type->key);
                                                    $enabledChannels = $pref ? $pref->channels ?? [] : [];
                                                    $isEnabled = $pref ? $pref->enabled : true;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-grow-1">
                                                                <h6 class="fs-14 mb-1">
                                                                    {{ __('core::profile.events.' . $type->key . '.title') }}
                                                                    @if ($type->is_mandatory)
                                                                        <span
                                                                            class="badge bg-danger-subtle text-danger ms-1 fs-10">{{ __('core::profile.mandatory') }}</span>
                                                                    @endif
                                                                </h6>
                                                                <p class="text-muted mb-0 fs-12 text-wrap">
                                                                    {{ __('core::profile.events.' . $type->key . '.description') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    @foreach (['database', 'mail', 'broadcast'] as $channel)
                                                        <td class="text-center">
                                                            <div
                                                                class="form-check form-switch form-switch-md d-inline-block">
                                                                <input class="form-check-input notification-channel-toggle"
                                                                    type="checkbox" name="prefs[{{ $type->key }}][]"
                                                                    value="{{ $channel }}"
                                                                    @if (in_array($channel, $type->available_channels)) @if (in_array($channel, $enabledChannels) || ($type->is_mandatory && $channel === 'database')) checked @endif
                                                                @if ($type->is_mandatory && $channel === 'database') disabled @endif @else
                                                                    disabled @endif
                                                                data-event="{{ $type->key }}"
                                                                data-channel="{{ $channel }}">
                                                            </div>
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4 text-end">
                                    <button type="button" class="btn btn-primary"
                                        onclick="saveNotificationPreferences()">
                                        {{ __('core::profile.save_preferences') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane" id="privacy" role="tabpanel">
                            <div class="mb-4">
                                <h5 class="card-title">{{ __('core::profile.data_export') }}</h5>
                                <p class="text-muted">{{ __('core::profile.data_export_tip') }}</p>
                                <button type="button" class="btn btn-soft-primary" onclick="requestDataExport()">
                                    <i class="ri-download-2-line align-bottom me-1"></i>
                                    {{ __('core::profile.request_data_export') }}
                                </button>
                            </div>

                            @if (count($exports) > 0)
                                <div class="mt-4 mb-4">
                                    <h6 class="fs-14 mb-3">{{ __('core::profile.available_exports') }}</h6>
                                    <div class="table-responsive">
                                        <table class="table table-borderless align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('core::profile.created_at') }}</th>
                                                    <th>{{ __('core::profile.size') }}</th>
                                                    <th class="text-end">{{ __('core::profile.action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($exports as $export)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-shrink-0 me-2">
                                                                    <div class="avatar-xs">
                                                                        <div
                                                                            class="avatar-title bg-light text-primary rounded fs-16">
                                                                            <i class="ri-file-zip-line"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <h6 class="fs-13 mb-0">{{ $export['name'] }}</h6>
                                                                    <small
                                                                        class="text-muted">{{ $export['created_at'] }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>{{ $export['size'] }}</td>
                                                        <td class="text-end">
                                                            <div class="hstack gap-2 justify-content-end">
                                                                <a href="{{ $export['url'] }}"
                                                                    class="btn btn-sm btn-soft-info">
                                                                    <i
                                                                        class="ri-download-line font-size-14 align-middle me-1"></i>
                                                                    {{ __('core::profile.download') }}
                                                                </a>
                                                                <button type="button" class="btn btn-sm btn-soft-danger"
                                                                    onclick="deleteExport('{{ $export['name'] }}')">
                                                                    <i
                                                                        class="ri-delete-bin-line font-size-14 align-middle me-1"></i>
                                                                    {{ __('core::profile.delete') }}
                                                                </button>
                                                            </div>
                                                        </td>

                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @else
                                <div class="mt-4 mb-4 text-center py-3 bg-light rounded border border-dashed">
                                    <i class="ri-file-search-line fs-24 text-muted d-block mb-2"></i>
                                    <p class="text-muted mb-0">{{ __('core::profile.no_exports_found') }}</p>
                                </div>
                            @endif


                            <hr class="my-4">

                            <div class="mb-4">
                                <h5 class="card-title text-danger">{{ __('core::profile.delete_account') }}</h5>
                                <p class="text-muted">{{ __('core::profile.delete_account_tip') }}</p>

                                @if ($user->scheduled_for_deletion_at)
                                    <div class="alert alert-warning border-0" role="alert">
                                        <strong>{{ __('core::profile.scheduled_for_deletion_on') }}:</strong>
                                        {{ $user->scheduled_for_deletion_at->format('M d, Y') }}
                                        <button type="button" class="btn btn-sm btn-link text-warning p-0 ms-2"
                                            onclick="cancelAccountDeletion()">{{ __('core::profile.cancel_deletion') }}</button>
                                    </div>
                                @else
                                    <button type="button" class="btn btn-danger" onclick="scheduleAccountDeletion()">
                                        {{ __('core::profile.delete_my_account') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                        <!--end tab-pane-->
                    </div>
                </div>
            </div>
            <!--end col-->
        </div>
        <!--end row-->
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                // Global Swal Toast configuration
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                @if (session('success'))
                    Toast.fire({
                        icon: 'success',
                        title: '{{ session('success') }}',
                        background: '#0ab39c',
                        color: '#fff',
                        iconColor: '#fff'
                    });
                @endif

                @if (session('error'))
                    Toast.fire({
                        icon: 'error',
                        title: '{{ session('error') }}',
                        background: '#f06548',
                        color: '#fff',
                        iconColor: '#fff'
                    });
                @endif

                // Handle hash in URL to switch tabs automatically
                window.addEventListener('DOMContentLoaded', () => {
                    const hash = window.location.hash;
                    if (hash) {
                        const tabEl = document.querySelector(`.nav-tabs-custom a[href="${hash}"]`);
                        if (tabEl) {
                            const tab = new bootstrap.Tab(tabEl);
                            tab.show();
                        }
                    }
                });

                // Update hash on tab change
                document.querySelectorAll('.nav-tabs-custom a[data-bs-toggle="tab"]').forEach(tabEl => {
                    tabEl.addEventListener('shown.bs.tab', (e) => {
                        history.replaceState(null, null, e.target.hash);
                    });
                });

                /**
                 * Sudo Mode Re-authentication Handler
                 */
                async function promptSudo() {
                    const {
                        value: password
                    } = await Swal.fire({
                        title: '{{ __('core::profile.sudo_title') }}',
                        text: '{{ __('core::profile.sudo_message') }}',
                        input: 'password',
                        inputPlaceholder: '{{ __('core::profile.password_placeholder') }}',
                        showCancelButton: true,
                        confirmButtonText: '{{ __('core::profile.confirm_action') }}',
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        inputValidator: (value) => {
                            if (!value) {
                                return '{{ __('core::profile.password_required') }}';
                            }
                        }
                    });

                    if (password) {
                        try {
                            const response = await fetch('{{ route('profile.sudo.confirm') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    password
                                })
                            });

                            const data = await response.json();
                            if (data.success) {
                                return true;
                            } else {
                                Swal.fire('{{ __('core::profile.error') }}', data.message ||
                                    '{{ __('core::profile.incorrect_password') }}', 'error');
                                return false;
                            }
                        } catch (error) {
                            Swal.fire('{{ __('core::profile.error') }}', '{{ __('core::profile.verify_failed') }}',
                                'error');
                            return false;
                        }
                    }
                    return false;
                }

                /**
                 * AJAX Form Handler
                 */
                async function handleFormSubmit(e) {
                    e.preventDefault();
                    const form = e.target;
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;

                    const submitAction = async () => {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML =
                            '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> {{ __('core::profile.update') }}...';

                        const formData = new FormData(form);
                        const methodAttr = form.getAttribute('method') || 'POST';
                        const methodOverride = form.querySelector('input[name="_method"]')?.value;
                        const finalMethod = (methodOverride || methodAttr).toUpperCase();

                        try {
                            const response = await fetch(form.action, {
                                method: finalMethod === 'GET' ? 'GET' : 'POST',
                                body: finalMethod === 'GET' ? null : formData,
                                redirect: 'manual', // Prevent browser from following redirects automatically
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });

                            // If it's a redirect (301, 302, etc.), just reload the page
                            // as manual redirect makes the response opaque or type basic with status 0 or 302
                            if (response.type === 'opaqueredirect' || response.status === 302 || response.status ===
                                0) {
                                window.location.reload();
                                return;
                            }

                            let data = {};
                            try {
                                const contentType = response.headers.get('content-type');
                                if (contentType && contentType.includes('application/json')) {
                                    data = await response.json();
                                }
                            } catch (e) {
                                console.error('Error parsing JSON:', e);
                            }

                            if (response.status === 403) {
                                if (data.type === 'sudo_required') {
                                    const verified = await promptSudo();
                                    if (verified) {
                                        return submitAction();
                                    }
                                } else {
                                    Swal.fire('Unauthorized', data.message || 'You do not have permission.', 'error');
                                }
                            } else if (!response.ok) {
                                if (response.status === 422) {
                                    let errorMsg = '<ul class="text-start">';
                                    if (data.errors) {
                                        Object.values(data.errors).forEach(err => errorMsg += `<li>${err[0]}</li>`);
                                    } else if (data.message) {
                                        errorMsg += `<li>${data.message}</li>`;
                                    } else {
                                        errorMsg += '<li>{{ __('core::profile.invalid_data') }}</li>';
                                    }
                                    errorMsg += '</ul>';
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        html: errorMsg
                                    });
                                } else if (response.status === 405) {
                                    // Method notation error - reload to recover state
                                    console.warn('Method not allowed, reloading...');
                                    window.location.reload();
                                } else {
                                    Swal.fire('Error', data.message || 'Server error occurred (' + response.status +
                                        ')', 'error');
                                }
                            } else {
                                if (data.success || response.status === 200 || response.status === 204) {
                                    Toast.fire({
                                        icon: 'success',
                                        title: data.message || '{{ __('core::profile.update_success') }}'
                                    });
                                    if (data.redirect) {
                                        window.location.href = data.redirect;
                                    } else if (form.id === 'personal-details-form' || form.id === 'enable-2fa-form' ||
                                        form.id === 'confirm-2fa-form' || form.id === 'disable-2fa-form' || form.id ===
                                        'regenerate-codes-form') {
                                        setTimeout(() => window.location.reload(), 1500);
                                    }
                                } else if (data.message) {
                                    Swal.fire('Error', data.message, 'error');
                                }
                            }
                        } catch (err) {
                            console.error('Submit Action Error:', err);
                            Swal.fire('Error', '{{ __('core::profile.unexpected_error') }}: ' + err.message, 'error');
                        } finally {

                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    };

                    await submitAction();
                }

                // Attach AJAX handlers
                ['personal-details-form', 'change-password-form', 'enable-2fa-form', 'confirm-2fa-form', 'disable-2fa-form',
                    'regenerate-codes-form'
                ].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.addEventListener('submit', handleFormSubmit);
                });

                document.getElementById('profile-img-file-input')?.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('avatar', file);

                    const submitBtn = document.querySelector('.profile-photo-edit i');
                    const originalIcon = submitBtn.className;
                    submitBtn.className = 'bx bx-loader fs-22 bx-spin';

                    fetch('{{ route('profile.avatar') }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update images (profile and header)
                                document.querySelectorAll('.user-profile-image').forEach(img => {
                                    img.src = data.avatar_url;
                                    img.classList.remove('d-none');
                                });

                                // Hide initials if visible
                                const initials = document.getElementById('user-avatar-initials');
                                if (initials) initials.classList.add('d-none');

                                Toast.fire({
                                    icon: 'success',
                                    title: data.message
                                });

                                // Add delete button if not exists
                                if (!document.getElementById('delete-avatar-btn')) {
                                    const deleteBtnHtml =
                                        `<button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 shadow" onclick="deleteAvatar()" id="delete-avatar-btn" style="width: 28px; height: 28px; padding: 0;"><i class="ri-delete-bin-line"></i></button>`;
                                    document.querySelector('.profile-user').insertAdjacentHTML('beforeend',
                                        deleteBtnHtml);
                                }
                            } else {
                                Swal.fire('Upload Failed', data.message || 'Error uploading avatar', 'error');
                            }
                        })
                        .finally(() => submitBtn.className = originalIcon);
                });


                function deleteAvatar() {
                    const url = '{{ route('profile.avatar.delete') }}';
                    const method = 'DELETE';

                    Swal.fire({
                        title: '{{ __('core::profile.remove_avatar') }}',
                        text: '{{ __('core::profile.confirm_action') }}',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#f06548',
                        confirmButtonText: '{{ __('core::profile.remove_avatar') }}'
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            try {
                                const response = await fetch(url, {
                                    method: method,
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });

                                const data = await response.json();
                                if (data.success) {
                                    // Hide images
                                    document.querySelectorAll('.user-profile-image').forEach(img => {
                                        img.classList.add('d-none');
                                        img.src = '';
                                    });

                                    // Show/Update initials
                                    const initialsContainer = document.getElementById('user-avatar-initials');
                                    if (initialsContainer) {
                                        initialsContainer.classList.remove('d-none');
                                        initialsContainer.querySelector('span').textContent = data.initials;
                                    } else {
                                        // Create initials container if it doesn't exist
                                        const html = `
                                <div class="avatar-xl img-thumbnail rounded-circle bg-secondary d-flex align-items-center justify-content-center shadow mx-auto" id="user-avatar-initials">
                                    <span class="fs-24 fw-bold text-white">${data.initials}</span>
                                </div>`;
                                        document.querySelector('.profile-user').insertAdjacentHTML('afterbegin', html);
                                    }

                                    // Remove delete button
                                    document.getElementById('delete-avatar-btn')?.remove();

                                    Toast.fire({
                                        icon: 'success',
                                        title: data.message
                                    });

                                    // Update header avatar too if applicable
                                    const headerAvatar = document.querySelector(
                                        '.header-profile-user'); // Adjust selector as needed
                                    if (headerAvatar) {
                                        // If header uses initials, update it. If it uses img, maybe replace with initials too.
                                        // For simplicity, we can let user handle topbar via global state or just reload topbar part
                                    }
                                } else {
                                    Swal.fire('Error', data.message, 'error');
                                }
                            } catch (err) {
                                Swal.fire('Error', 'An error occurred.', 'error');
                            }
                        }
                    });
                }


                /**
                 * Generic action handler (for buttons not in forms)
                 */
                async function performAction(url, method = 'POST', confirmation = null) {
                    if (confirmation) {
                        const result = await Swal.fire({
                            title: confirmation.title,
                            text: confirmation.text,
                            icon: confirmation.icon || 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#f06548',
                            confirmButtonText: confirmation.confirmText || 'Yes',
                            cancelButtonText: '{{ __('core::profile.cancel') }}'
                        });
                        if (!result.isConfirmed) return;
                    }

                    const runAction = async () => {
                        try {
                            // Show loader
                            Swal.fire({
                                title: '{{ __('core::profile.please_wait') }}',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            const response = await fetch(url, {
                                method: method,
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                redirect: 'manual'
                            });

                            // Handle redirects (e.g., if sudo middleware redirects to full page)
                            if (response.type === 'opaqueredirect' || response.status === 302 || response.status ===
                                0) {
                                Swal.close();
                                if (await promptSudo()) return runAction();
                                return;
                            }

                            if (response.status === 403) {
                                const data = await response.json().catch(() => ({}));
                                if (data.type === 'sudo_required') {
                                    // Sudo required - close current swal first
                                    Swal.close();
                                    if (await promptSudo()) return runAction();
                                    return;
                                } else {
                                    Swal.fire('Unauthorized', data.message || 'Access Denied', 'error');
                                    return;
                                }
                            }

                            const data = await response.json().catch(() => ({}));

                            if (response.ok) {
                                if (data.success || response.status === 200 || response.status === 204) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: data.message || 'Action completed successfully.',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    setTimeout(() => window.location.reload(), 2000);
                                } else {
                                    Swal.fire('Error', data.message || 'Unknown error occurred.', 'error');
                                }
                            } else {
                                if (response.status === 405) {
                                    window.location.reload();
                                } else {
                                    Swal.fire('Error', data.message || 'Server error (' + response.status + ')',
                                        'error');
                                }
                            }
                        } catch (err) {
                            console.error('Action error:', err);
                            Swal.fire('Error', '{{ __('core::profile.unexpected_error') }}', 'error');
                        }
                    };

                    await runAction();
                }

                function terminateSession(id) {
                    performAction(`{{ url('profile/sessions') }}/${id}`, 'DELETE', {
                        title: '{{ __('core::profile.confirm_terminate_session') }}',
                        text: "{{ __('core::profile.sessions_tip') }}",
                        confirmText: '{{ __('core::profile.terminate') }}'
                    });
                }

                function terminateOtherSessions() {
                    performAction(`{{ route('profile.sessions.terminate-others') }}`, 'DELETE', {
                        title: '{{ __('core::profile.confirm_terminate_others') }}',
                        text: "{{ __('core::profile.sessions_tip') }}",
                        confirmText: '{{ __('core::profile.terminate_others') }}'
                    });
                }

                function requestDataExport() {
                    performAction('{{ route('profile.export') }}', 'POST', {
                        title: '{{ __('core::profile.request_data_export') }}',
                        text: "{{ __('core::profile.confirm_data_export') }}",
                        icon: 'info',
                        confirmText: '{{ __('core::profile.confirm') }}'
                    });
                }

                function scheduleAccountDeletion() {
                    performAction('{{ route('profile.delete') }}', 'DELETE', {
                        title: '{{ __('core::profile.delete_account') }}',
                        text: "{{ __('core::profile.confirm_delete_account') }}",
                        confirmText: '{{ __('core::profile.delete_my_account') }}'
                    });
                }

                function cancelAccountDeletion() {
                    performAction('{{ route('profile.delete.cancel') }}', 'POST', {
                        title: '{{ __('core::profile.cancel_deletion') }}',
                        text: '{{ __('core::profile.confirm') }}',
                        confirmText: '{{ __('core::profile.confirm') }}'
                    });
                }

                // Load more activities
                document.getElementById('load-more-activities')?.addEventListener('click', function() {
                    const page = this.getAttribute('data-next-page');
                    const originalText = this.innerHTML;
                    this.disabled = true;
                    this.innerHTML = '<i class="bx bx-loader bx-spin"></i>';

                    fetch(`{{ route('profile.index') }}?page=${page}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.html) {
                                document.getElementById('activity-timeline').insertAdjacentHTML('beforeend', data.html);
                                if (data.hasMore) this.setAttribute('data-next-page', data.nextPage);
                                else this.closest('.text-center').remove();
                            }
                        })
                        .finally(() => {
                            this.disabled = false;
                            this.innerHTML = originalText;
                        });
                });

                function deleteExport(filename) {
                    performAction(`{{ url('profile/export') }}/${filename}`, 'DELETE', {
                        title: '{{ __('core::profile.delete') }}',
                        text: '{{ __('core::profile.confirm_action') }}',
                        confirmText: '{{ __('core::profile.delete') }}'
                    });
                }

                function saveNotificationPreferences() {
                    handleFormSubmit({
                        target: document.getElementById('notification-preferences-form'),
                        preventDefault: () => {}
                    });
                }
            </script>
        @endpush
    @endsection
