@extends('core::layouts.auth')

@section('title', __('Sign Up'))

@section('content')
<div>
    <h5 class="text-primary">{{ __('Create New Account') }}</h5>
    <p class="text-muted">{{ __('Get your free Digilians account now.') }}</p>
</div>

<div class="mt-4">
    <form action="{{ route('register') }}" method="POST" class="needs-validation" novalidate>
        @csrf

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="first_name" class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}" placeholder="{{ __('Enter first name') }}" required autofocus>
                    @error('first_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="last_name" class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}" placeholder="{{ __('Enter last name') }}" required>
                    @error('last_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="username" class="form-label">{{ __('Username') }} <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" placeholder="{{ __('Enter username') }}" required>
            @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="useremail" class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="useremail" name="email" value="{{ old('email') }}" placeholder="{{ __('Enter email address') }}" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password-input">{{ __('Password') }} <span class="text-danger">*</span></label>
            <div class="position-relative auth-pass-inputgroup">
                <input type="password" class="form-control pe-5 password-input @error('password') is-invalid @enderror" onpaste="return false" placeholder="{{ __('Enter password') }}" id="password-input" name="password" required>
                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="password_confirmation">{{ __('Confirm Password') }} <span class="text-danger">*</span></label>
            <div class="position-relative auth-pass-inputgroup">
                <input type="password" class="form-control pe-5 password-input" onpaste="return false" placeholder="{{ __('Confirm password') }}" id="password_confirmation" name="password_confirmation" required>
            </div>
        </div>

        <div class="mb-4">
            <p class="mb-0 fs-12 text-muted fst-italic">{{ __('By registering you agree to the Digilians') }} <a href="#" class="text-primary text-decoration-underline fst-normal fw-medium">{{ __('Terms of Use') }}</a></p>
        </div>

        <div class="mt-4">
            <button class="btn btn-success w-100" type="submit">{{ __('Sign Up') }}</button>
        </div>

        <div class="mt-4 text-center">
            <div class="signin-other-title">
                <h5 class="fs-13 mb-4 title text-muted">{{ __('Create account with') }}</h5>
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
    <p class="mb-0">{{ __('Already have an account ?') }} <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-underline"> {{ __('Signin') }} </a> </p>
</div>
@endsection
