@extends('core::layouts.master')
@section('title', __('tickets::messages.support_group_settings'))

@section('content')

    @include('modules.educational.shared.alerts')

    <div class="row justify-content-center mt-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-soft-primary border-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 avatar-sm me-3">
                            <div class="avatar-title bg-primary text-white rounded-circle fs-4">
                                <i class="ri-settings-4-line"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0 fw-bold">{{ __('tickets::messages.support_group_settings') }}</h5>
                            <p class="text-muted mb-0 small">{{ __('tickets::messages.support_group_subtitle') }}</p>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('admin.tickets.groups.index') }}"
                                class="btn btn-soft-secondary btn-sm rounded-pill px-3">
                                <i class="ri-arrow-left-line align-middle me-1"></i>
                                {{ __('tickets::messages.back_to_list') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('admin.tickets.groups.saveSettings') }}" method="POST">
                        @csrf

                        <div class="row g-4">
                            <div class="col-12">
                                <h5 class="fw-bold mb-3"><i class="ri-shield-user-line text-primary me-2"></i>
                                    {{ __('tickets::messages.support_group_roles') }}</h5>
                                <p class="text-muted mb-4 small"><i class="ri-information-line me-1"></i>
                                    {{ __('tickets::messages.support_group_roles_help') }}</p>

                                <div class="row g-3">
                                    @foreach ($roles as $role)
                                        <div class="col-xl-4 col-md-6">
                                            <label
                                                class="card border border-2 shadow-none mb-0 cursor-pointer role-card transition-all w-100 {{ is_array($selectedRoles) && in_array($role->id, $selectedRoles) ? 'border-primary bg-soft-primary' : 'border-light bg-light' }}"
                                                for="role_{{ $role->id }}">
                                                <div class="card-body p-3 d-flex align-items-center">
                                                    <div class="form-check form-check-primary me-3 mb-0">
                                                        <input class="form-check-input role-checkbox fs-5" type="checkbox"
                                                            name="roles[]" id="role_{{ $role->id }}"
                                                            value="{{ $role->id }}"
                                                            {{ is_array($selectedRoles) && in_array($role->id, $selectedRoles) ? 'checked' : '' }}>
                                                    </div>
                                                    <div class="avatar-sm flex-shrink-0 me-3">
                                                        <div
                                                            class="avatar-title bg-white text-primary rounded-circle shadow-sm border border-light fw-bold fs-5">
                                                            {{ mb_substr($role->name, 0, 1) }}
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <h6 class="mb-0 fw-bold text-truncate text-dark">
                                                            {{ $role->name }}</h6>
                                                        <span
                                                            class="text-muted small">{{ __('tickets::messages.member_candidate') }}</span>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                @if (count($roles) == 0)
                                    <div class="text-center py-4 text-muted">
                                        <i class="ri-shield-keyhole-line fs-1 d-block mb-2"></i>
                                        {{ __('tickets::messages.no_data_found') }}
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-12 mt-4 border-top pt-4">
                                <div class="d-flex justify-content-end">
                                    <div class="hstack gap-2">
                                        <a href="{{ route('admin.tickets.groups.index') }}"
                                            class="btn btn-soft-secondary px-4">{{ __('tickets::messages.cancel') }}</a>
                                        <button type="submit" class="btn btn-primary px-5 shadow-sm fw-bold">
                                            <i class="ri-save-line align-bottom me-1"></i>
                                            {{ __('tickets::messages.save') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .card-header.bg-soft-primary {
            background-color: rgba(64, 81, 137, 0.05);
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .transition-all {
            transition: all 0.2s ease-in-out;
        }

        .role-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05) !important;
            border-color: #ced4da !important;
        }

        .role-card.border-primary {
            border-color: var(--vz-primary) !important;
        }

        .role-card.bg-soft-primary {
            background-color: rgba(64, 81, 137, 0.05) !important;
        }

        .form-check-input {
            cursor: pointer;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.role-checkbox');

            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const card = this.closest('.role-card');

                    if (this.checked) {
                        card.classList.remove('border-light', 'bg-light');
                        card.classList.add('border-primary', 'bg-soft-primary');
                    } else {
                        card.classList.remove('border-primary', 'bg-soft-primary');
                        card.classList.add('border-light', 'bg-light');
                    }
                });
            });
        });
    </script>
@endpush
