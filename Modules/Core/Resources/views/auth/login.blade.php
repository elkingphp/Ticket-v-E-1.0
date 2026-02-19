@extends('core::layouts.auth')

@section('title', __('core::auth.sign_in'))

@section('content')
<div>
    <h5 class="text-primary">{{ __('core::auth.welcome_back') }}</h5>
    <p class="text-muted">{{ __('core::auth.sign_in_to_continue') }}</p>
</div>

<div class="mt-4">
    <form action="{{ route('login') }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label for="username" class="form-label">{{ __('core::auth.email') }}</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="username" name="email" value="{{ old('email') }}" placeholder="{{ __('core::auth.enter_email') }}" required autofocus>
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="float-end">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-muted">{{ __('core::auth.forgot_password') }}</a>
                @endif
            </div>
            <label class="form-label" for="password-input">{{ __('core::auth.password') }}</label>
            <div class="position-relative auth-pass-inputgroup mb-3">
                <input type="password" class="form-control pe-5 password-input @error('password') is-invalid @enderror" placeholder="{{ __('core::auth.enter_password') }}" id="password-input" name="password" required>
                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" value="" id="auth-remember-check" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label" for="auth-remember-check">{{ __('core::auth.remember_me') }}</label>
        </div>

        <div class="mt-4">
            <button class="btn btn-success w-100" type="submit">{{ __('core::auth.sign_in') }}</button>
        </div>

        <div class="mt-4 text-center">
            <div class="signin-other-title">
                <h5 class="fs-13 mb-4 title">{{ __('core::auth.sign_in_with') }}</h5>
            </div>

            <div>
                <button type="button" class="btn btn-primary btn-icon waves-effect waves-light"><i class="ri-facebook-fill fs-16"></i></button>
                <button type="button" class="btn btn-danger btn-icon waves-effect waves-light"><i class="ri-google-fill fs-16"></i></button>
                <button type="button" class="btn btn-dark btn-icon waves-effect waves-light"><i class="ri-github-fill fs-16"></i></button>
                <button type="button" class="btn btn-info btn-icon waves-effect waves-light"><i class="ri-twitter-fill fs-16"></i></button>
            </div>
        </div>

    </form>
</div>

<div class="mt-5 text-center">
    <p class="mb-0">{{ __("core::auth.dont_have_account") }} <a href="{{ route('register') }}" class="fw-semibold text-primary text-decoration-underline"> {{ __('core::auth.signup') }} </a> </p>
</div>
@endsection