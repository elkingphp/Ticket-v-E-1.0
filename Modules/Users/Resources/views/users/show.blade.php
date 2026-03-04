@extends('core::layouts.master')

@section('title', __('users::users.user_details'))

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 bg-transparent pt-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0 fs-18 fw-bold text-primary">{{ __('users::users.user_details') }}</h5>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('users.index') }}" class="btn btn-soft-secondary btn-sm">
                                <i class="ri-arrow-go-back-line align-bottom me-1"></i>
                                {{ __('users::users.back_to_list' ?? 'Back') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row justify-content-center">
                        <div class="col-xl-3 col-lg-4">
                            <div class="card border-0 bg-light bg-opacity-50 h-100">
                                <div class="card-body text-center p-4">
                                    <div class="position-relative d-inline-block mb-3">
                                        @if ($user->avatar)
                                            <img src="{{ asset('storage/' . $user->avatar) }}" alt=""
                                                class="avatar-lg rounded-circle shadow">
                                        @else
                                            <div class="avatar-lg mx-auto">
                                                <span
                                                    class="avatar-title rounded-circle bg-primary-subtle text-primary fs-32 fw-bold shadow">
                                                    {{ $user->initials }}
                                                </span>
                                            </div>
                                        @endif
                                        <span
                                            class="position-absolute bottom-0 end-0 p-1 bg-success border border-2 border-white rounded-circle"
                                            title="Online"></span>
                                    </div>
                                    <h5 class="fs-16 mb-1 fw-bold">{{ $user->full_name }}</h5>
                                    <p class="text-muted mb-3">@<span>{{ $user->username }}</span></p>

                                    <div class="d-flex flex-wrap gap-1 justify-content-center mb-4">
                                        @foreach ($user->roles as $role)
                                            <span
                                                class="badge rounded-pill bg-info-subtle text-info">{{ $role->display_name ?? $role->name }}</span>
                                        @endforeach
                                    </div>

                                    <div class="row g-2 border-top pt-3 mt-3">
                                        <div class="col-6">
                                            <p class="text-muted mb-0 fs-12">{{ __('users::users.status') }}</p>
                                            @if ($user->status == 'active')
                                                <span
                                                    class="badge bg-success-subtle text-success text-uppercase fs-10">{{ __('users::users.active') }}</span>
                                            @else
                                                <span
                                                    class="badge bg-danger-subtle text-danger text-uppercase fs-10">{{ __('users::users.blocked') }}</span>
                                            @endif
                                        </div>
                                        <div class="col-6">
                                            <p class="text-muted mb-0 fs-12">
                                                {{ __('users::users.security') ?? 'Security' }}</p>
                                            @php $security = $user->security_status; @endphp
                                            <span
                                                class="badge {{ $security['class'] }} fs-10">{{ $security['label'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-9 col-lg-8 mt-4 mt-lg-0">
                            <div class="card border-0 bg-transparent shadow-none">
                                <div class="card-body p-0">
                                    <nav>
                                        <div class="nav nav-tabs nav-tabs-custom nav-success mb-4" id="nav-tab"
                                            role="tablist">
                                            <button class="nav-link active" id="nav-info-tab" data-bs-toggle="tab"
                                                data-bs-target="#nav-info" type="button"
                                                role="tab">{{ __('users::users.personal_details') }}</button>
                                            @can('audit.view')
                                                <a href="{{ route('audit.index', ['user_id' => $user->id]) }}"
                                                    class="nav-link">{{ __('users::users.activity_log') }}</a>
                                            @endcan
                                        </div>
                                    </nav>

                                    <div class="tab-content" id="nav-tabContent">
                                        <div class="tab-pane fade show active" id="nav-info" role="tabpanel">
                                            <div class="row g-4">
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-light bg-opacity-25 rounded border h-100">
                                                        <h6 class="text-muted text-uppercase fw-semibold mb-2 fs-12">
                                                            {{ __('users::users.email') }}</h6>
                                                        <p class="mb-0 fw-medium"><i
                                                                class="ri-mail-line me-2 text-primary"></i>
                                                            {{ $user->email }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-light bg-opacity-25 rounded border h-100">
                                                        <h6 class="text-muted text-uppercase fw-semibold mb-2 fs-12">
                                                            {{ __('users::users.phone') }}</h6>
                                                        <p class="mb-0 fw-medium"><i
                                                                class="ri-phone-line me-2 text-primary"></i>
                                                            {{ $user->phone ?? '---' }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-light bg-opacity-25 rounded border h-100">
                                                        <h6 class="text-muted text-uppercase fw-semibold mb-2 fs-12">
                                                            {{ __('users::users.joined_at') }}</h6>
                                                        <p class="mb-0 fw-medium"><i
                                                                class="ri-calendar-line me-2 text-primary"></i>
                                                            {{ $user->created_at->format('d M, Y H:i') }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-light bg-opacity-25 rounded border h-100">
                                                        <h6 class="text-muted text-uppercase fw-semibold mb-2 fs-12">
                                                            {{ __('users::users.last_login') }}</h6>
                                                        <p class="mb-0 fw-medium"><i
                                                                class="ri-time-line me-2 text-primary"></i>
                                                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : '---' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            @can('users.edit')
                                                <div class="mt-5 border-top pt-4">
                                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">
                                                        <i class="ri-pencil-fill align-bottom me-1"></i>
                                                        {{ __('users::users.edit_user') }}
                                                    </a>
                                                </div>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
