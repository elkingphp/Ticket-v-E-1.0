@extends('core::layouts.auth')

@section('title', __('Forgot Password'))

@section('content')
<div>
    <h5 class="text-primary">{{ __('Forgot Password?') }}</h5>
    <p class="text-muted">{{ __('Reset password with Digilians') }}</p>

    <div class="text-center mt-4">
        <lord-icon src="https://cdn.lordicon.com/rhvddzym.json" trigger="loop" colors="primary:#0ab39c" class="avatar-xl"></lord-icon>
    </div>
</div>

<div class="alert alert-borderless alert-warning text-center mb-2 mx-2" role="alert">
    {{ __('Enter your email and instructions will be sent to you!') }}
</div>

<div class="mt-4">
    <form action="{{ route('password.email') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="form-label">{{ __('Email') }}</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="{{ __('Enter email') }}" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        @if (session('status'))
            <div class="alert alert-success text-center mb-4" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <div class="text-center mt-4">
            <button class="btn btn-success w-100" type="submit">{{ __('Send Reset Link') }}</button>
        </div>
    </form>
</div>

<div class="mt-5 text-center">
    <p class="mb-0">{{ __('Wait, I remember my password...') }} <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-underline"> {{ __('Click here') }} </a> </p>
</div>
@endsection
