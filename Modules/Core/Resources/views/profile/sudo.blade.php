@extends('core::layouts.master')

@section('title', __('core::profile.re_authentication'))

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card mt-4">
            <div class="card-body p-4">
                <div class="text-center mt-2">
                    <h5 class="text-primary">{{ __('core::profile.secure_area') }}</h5>
                    <p class="text-muted">{{ __('core::profile.enter_password_to_proceed') }}</p>
                </div>
                <div class="p-2 mt-4">
                    <form action="{{ route('profile.sudo.confirm') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="password-input">{{ __('core::profile.password') }}</label>
                            <div class="position-relative auth-pass-inputgroup mb-3">
                                <input type="password" class="form-control pe-5 password-input" name="password" placeholder="{{ __('core::profile.enter_password') }}" id="password-input" required autofocus>
                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                            </div>
                            @error('password')
                                <div class="text-danger fs-13">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-success w-100" type="submit">{{ __('core::profile.confirm') }}</button>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <a href="{{ route('profile.index') }}" class="btn btn-link link-secondary">{{ __('core::profile.cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
            <!-- end card body -->
        </div>
        <!-- end card -->
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('password-addon').addEventListener('click', function () {
        var input = document.getElementById('password-input');
        if (input.type === "password") {
            input.type = "text";
        } else {
            input.type = "password";
        }
    });
</script>
@endpush
