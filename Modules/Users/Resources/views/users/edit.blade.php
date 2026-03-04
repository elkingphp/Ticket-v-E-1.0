@extends('core::layouts.master')

@section('title', __('users::users.edit_user'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 bg-transparent pt-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0 fs-18 fw-bold text-primary">{{ __('users::users.edit_user') }}</h5>
                            <p class="text-muted mb-0 fs-13">
                                {{ __('users::users.edit_user_description' ?? 'Update the user profile and account settings.') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Personal Information -->
                            <div class="col-lg-12">
                                <div class="mb-4">
                                    <h6 class="fs-14 fw-bold mb-3 border-bottom pb-2"><i
                                            class="ri-user-settings-line align-middle me-1 text-primary"></i>
                                        {{ __('users::users.personal_details') }}</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="first_name"
                                                class="form-label fw-semibold">{{ __('users::users.first_name') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control border-light shadow-none bg-light bg-opacity-50 @error('first_name') is-invalid @enderror"
                                                id="first_name" name="first_name"
                                                value="{{ old('first_name', $user->first_name) }}" required>
                                            @error('first_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="last_name"
                                                class="form-label fw-semibold">{{ __('users::users.last_name') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control border-light shadow-none bg-light bg-opacity-50 @error('last_name') is-invalid @enderror"
                                                id="last_name" name="last_name"
                                                value="{{ old('last_name', $user->last_name) }}" required>
                                            @error('last_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="username"
                                                class="form-label fw-semibold">{{ __('users::users.username') }} <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text border-light bg-light">@</span>
                                                <input type="text"
                                                    class="form-control border-light shadow-none bg-light bg-opacity-50 @error('username') is-invalid @enderror"
                                                    id="username" name="username"
                                                    value="{{ old('username', $user->username) }}" required>
                                            </div>
                                            @error('username')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email"
                                                class="form-label fw-semibold">{{ __('users::users.email') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="email"
                                                class="form-control border-light shadow-none bg-light bg-opacity-50 @error('email') is-invalid @enderror"
                                                id="email" name="email" value="{{ old('email', $user->email) }}"
                                                required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Security & Account -->
                            <div class="col-lg-12">
                                <div class="mb-4 mt-2">
                                    <h6 class="fs-14 fw-bold mb-3 border-bottom pb-2"><i
                                            class="ri-shield-keyhole-line align-middle me-1 text-primary"></i>
                                        {{ __('users::users.security_and_roles' ?? 'Security & Roles') }}</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="password"
                                                class="form-label fw-semibold">{{ __('users::users.password_keep_current') }}</label>
                                            <div class="position-relative auth-pass-inputgroup">
                                                <input type="password"
                                                    class="form-control border-light shadow-none bg-light bg-opacity-50 pe-5 @error('password') is-invalid @enderror"
                                                    id="password" name="password"
                                                    placeholder="{{ __('users::users.enter_new_password_tip' ?? 'Leave blank to keep current') }}">
                                                <button
                                                    class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted shadow-none"
                                                    type="button" id="password-addon"><i
                                                        class="ri-eye-fill align-middle"></i></button>
                                            </div>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="password_confirmation"
                                                class="form-label fw-semibold">{{ __('users::users.confirm_password') }}</label>
                                            <input type="password"
                                                class="form-control border-light shadow-none bg-light bg-opacity-50"
                                                id="password_confirmation" name="password_confirmation"
                                                placeholder="{{ __('users::users.confirm_password') }}">
                                        </div>

                                        <div class="col-lg-12 mb-3">
                                            <label
                                                class="form-label fw-semibold d-block mb-3 text-muted">{{ __('users::users.status') }}</label>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <input type="radio" class="btn-check" name="status" id="statusActive"
                                                        value="active"
                                                        {{ old('status', $user->status) == 'active' ? 'checked' : '' }}>
                                                    <label
                                                        class="btn btn-outline-light border-light shadow-sm d-flex align-items-center p-3 w-100 text-start h-100 status-card status-active"
                                                        for="statusActive">
                                                        <div class="flex-shrink-0 me-3">
                                                            <div class="avatar-xs">
                                                                <div
                                                                    class="avatar-title bg-success-subtle text-success rounded-circle fs-16">
                                                                    <i class="ri-check-line"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-0 fw-bold">{{ __('users::users.active') }}</h6>
                                                            <small
                                                                class="text-muted fs-11">{{ __('users::users.active_users_help') }}</small>
                                                        </div>
                                                        <div class="status-check-icon text-success">
                                                            <i class="ri-checkbox-circle-fill fs-20"></i>
                                                        </div>
                                                    </label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="radio" class="btn-check" name="status"
                                                        id="statusBlocked" value="blocked"
                                                        {{ old('status', $user->status) == 'blocked' ? 'checked' : '' }}>
                                                    <label
                                                        class="btn btn-outline-light border-light shadow-sm d-flex align-items-center p-3 w-100 text-start h-100 status-card status-blocked"
                                                        for="statusBlocked">
                                                        <div class="flex-shrink-0 me-3">
                                                            <div class="avatar-xs">
                                                                <div
                                                                    class="avatar-title bg-danger-subtle text-danger rounded-circle fs-16">
                                                                    <i class="ri-forbid-line"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-0 fw-bold">{{ __('users::users.blocked') }}</h6>
                                                            <small
                                                                class="text-muted fs-11">{{ __('users::users.blocked_users_help') }}</small>
                                                        </div>
                                                        <div class="status-check-icon text-danger">
                                                            <i class="ri-checkbox-circle-fill fs-20"></i>
                                                        </div>
                                                    </label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="radio" class="btn-check" name="status"
                                                        id="statusInactive" value="inactive"
                                                        {{ old('status', $user->status) == 'inactive' ? 'checked' : '' }}>
                                                    <label
                                                        class="btn btn-outline-light border-light shadow-sm d-flex align-items-center p-3 w-100 text-start h-100 status-card status-inactive"
                                                        for="statusInactive">
                                                        <div class="flex-shrink-0 me-3">
                                                            <div class="avatar-xs">
                                                                <div
                                                                    class="avatar-title bg-secondary-subtle text-secondary rounded-circle fs-16">
                                                                    <i class="ri-time-line"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-0 fw-bold">{{ __('users::users.inactive') }}
                                                            </h6>
                                                            <small
                                                                class="text-muted fs-11">{{ __('users::users.inactive_users_help') }}</small>
                                                        </div>
                                                        <div class="status-check-icon text-secondary">
                                                            <i class="ri-checkbox-circle-fill fs-20"></i>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            @error('status')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-12 mb-3">
                                            <label
                                                class="form-label fw-semibold d-block mb-3 text-muted">{{ __('users::users.roles') }}
                                                <span class="text-danger">*</span></label>
                                            <div class="row g-3">
                                                @foreach ($roles as $role)
                                                    <div class="col-md-4">
                                                        <input type="checkbox" class="btn-check" name="roles[]"
                                                            id="role_{{ $role->id }}" value="{{ $role->name }}"
                                                            {{ collect(old('roles', $user->roles->pluck('name')))->contains($role->name) ? 'checked' : '' }}>
                                                        <label
                                                            class="btn btn-outline-light border-light shadow-sm d-flex align-items-center p-3 w-100 text-start h-100 status-card status-role"
                                                            for="role_{{ $role->id }}">
                                                            <div class="flex-shrink-0 me-3">
                                                                <div class="avatar-xs">
                                                                    <div
                                                                        class="avatar-title bg-info-subtle text-info rounded-circle fs-16">
                                                                        <i class="ri-shield-user-line"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-0 fw-bold">
                                                                    {{ $role->display_name ?? $role->name }}</h6>
                                                                <small
                                                                    class="text-muted fs-11">{{ $role->description ?? str_replace('_', ' ', $role->name) . ' Permission Set' }}</small>
                                                            </div>
                                                            <div class="status-check-icon text-info">
                                                                <i class="ri-checkbox-circle-fill fs-20"></i>
                                                            </div>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('roles')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            <p class="text-muted mb-0 fs-12 mt-3">
                                                <i class="ri-information-line align-middle me-1"></i>
                                                {{ __('users::users.roles_tip' ?? 'You can select one or more roles for this user.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-top text-{{ app()->getLocale() == 'ar' ? 'start' : 'end' }}">
                            <a href="{{ route('users.index') }}"
                                class="btn btn-soft-dark px-4 me-2">{{ __('users::users.cancel') }}</a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold">
                                <i class="ri-save-line align-bottom me-1"></i> {{ __('users::users.update_user') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .status-card {
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            border: 2px solid transparent !important;
            position: relative;
        }

        .status-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--vz-box-shadow-lg) !important;
            border-color: var(--vz-light) !important;
        }

        .status-check-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.2s ease-in-out;
        }

        .btn-check:checked+.status-card {
            background-color: var(--vz-light) !important;
        }

        .btn-check:checked+.status-active {
            border-color: #0ab39c !important;
        }

        .btn-check:checked+.status-blocked {
            border-color: #f06548 !important;
        }

        .btn-check:checked+.status-inactive {
            border-color: #6c757d !important;
        }

        .btn-check:checked+.status-role {
            border-color: #35b8e0 !important;
        }

        .btn-check:checked+.status-card .status-check-icon {
            opacity: 1;
            transform: scale(1);
        }

        [dir="rtl"] .status-check-icon {
            right: auto;
            left: 10px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordAddon = document.getElementById('password-addon');
            const passwordInput = document.getElementById('password');

            if (passwordAddon && passwordInput) {
                passwordAddon.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    passwordAddon.querySelector('i').classList.toggle('ri-eye-fill');
                    passwordAddon.querySelector('i').classList.toggle('ri-eye-off-fill');
                });
            }
        });
    </script>
@endpush
