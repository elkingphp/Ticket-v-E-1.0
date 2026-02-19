@extends('core::layouts.auth')

@section('title', __('Two-Factor Challenge'))

@section('content')
<div class="text-center">
    <h5 class="text-primary">{{ __('Two-Factor Verification') }}</h5>
    <p class="text-muted">{{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}</p>

    <div class="mt-4">
        <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#0ab39c" class="avatar-xl"></lord-icon>
    </div>
</div>

<div class="mt-4" x-data="{ recovery: false }">
    <form method="POST" action="{{ route('two-factor.login') }}">
        @csrf

        <div class="mb-3" x-show="!recovery">
            <label for="code" class="form-label">{{ __('Authentication Code') }}</label>
            <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" inputmode="numeric" autofocus x-ref="code" autocomplete="one-time-code">
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3" x-show="recovery" x-cloak>
            <label for="recovery_code" class="form-label">{{ __('Recovery Code') }}</label>
            <input type="text" class="form-control @error('recovery_code') is-invalid @enderror" id="recovery_code" name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code">
            @error('recovery_code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end mt-2">
            <button type="button" class="btn btn-link p-0 text-muted" x-show="!recovery" @click="recovery = true; $nextTick(() => $refs.recovery_code.focus())">
                {{ __('Use a recovery code') }}
            </button>

            <button type="button" class="btn btn-link p-0 text-muted" x-show="recovery" x-cloak @click="recovery = false; $nextTick(() => $refs.code.focus())">
                {{ __('Use an authentication code') }}
            </button>
        </div>

        <div class="mt-4">
            <button class="btn btn-success w-100" type="submit">{{ __('Log in') }}</button>
        </div>
    </form>
</div>

<div class="mt-5 text-center">
    <p class="mb-0">{{ __("Can't access your phone?") }} <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-underline"> {{ __('Back to Login') }} </a> </p>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
