@extends('core::layouts.master')

@section('title', __('users::roles.roles_management'))

@push('styles')
    <style>
        .bulk-btn {
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .bulk-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        #bulkActionToolbar .card {
            background: linear-gradient(to right, #405189, #0ab39c) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        .role-card {
            transition: all 0.3s ease;
        }

        .role-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-xl-4 col-md-6">
            <div class="card card-animate border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                {{ __('users::roles.total_roles') }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value"
                                    data-target="{{ $roles->count() }}">{{ $roles->count() }}</span></h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle rounded fs-3">
                                <i class="ri-shield-user-line text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">{{ __('users::roles.roles_list') }}</h4>
                    <div class="flex-shrink-0">
                        @can('roles.manage')
                            <a href="{{ route('roles.create') }}" class="btn btn-success">
                                <i class="ri-add-line align-bottom me-1"></i> {{ __('users::roles.add_new_role') }}
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('roles.bulk-actions') }}" method="POST" id="bulkActionForm">
                        @csrf
                        <!-- Bulk Actions Toolbar (Premium Floating Design) -->
                        <div id="bulkActionToolbar"
                            style="display: none; position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 1050; min-width: 400px;">
                            <div
                                class="card border-0 shadow-lg bg-primary text-white rounded-pill overflow-hidden animate__animated animate__fadeInUp">
                                <div class="card-body py-2 px-4">
                                    <div class="hstack gap-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs me-2">
                                                <div class="avatar-title bg-white rounded-circle text-primary">
                                                    <span id="selectedCount" class="fw-bold">0</span>
                                                </div>
                                            </div>
                                            <span class="fw-medium">{{ __('users::roles.selected') }}</span>
                                        </div>
                                        <div class="vr bg-white bg-opacity-20 my-2"></div>
                                        <div class="hstack gap-2">
                                            <button type="submit" name="action" value="delete"
                                                class="btn btn-link text-white text-decoration-none px-2 bulk-btn"
                                                onclick="return confirm('{{ __('users::roles.bulk_delete_confirm') }}')">
                                                <i class="ri-delete-bin-5-fill align-bottom me-1 fs-16 text-danger"></i>
                                                {{ __('users::roles.delete_selected') }}
                                            </button>
                                        </div>
                                        <button type="button"
                                            class="btn btn-sm btn-icon btn-ghost-light rounded-circle ms-auto"
                                            onclick="unselectAll()">
                                            <i class="ri-close-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive table-card">
                            <table class="table table-hover align-middle table-nowrap mb-0" id="rolesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 50px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="checkAll">
                                            </div>
                                        </th>
                                        <th scope="col">{{ __('users::roles.name') }}</th>
                                        <th scope="col">{{ __('users::roles.guard') }}</th>
                                        <th scope="col">{{ __('users::roles.permissions_count') }}</th>
                                        <th scope="col">{{ __('users::roles.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roles as $role)
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input child-check" type="checkbox"
                                                        name="ids[]" value="{{ $role->id }}"
                                                        {{ $role->name === 'super-admin' ? 'disabled' : '' }}>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs flex-shrink-0 me-2">
                                                        <div class="avatar-title bg-soft-info text-info rounded-circle">
                                                            {{ substr($role->display_name ?? $role->name, 0, 1) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <span
                                                            class="fw-medium d-block">{{ $role->display_name ?? $role->name }}</span>
                                                        @if ($role->description)
                                                            <small class="text-muted d-block text-truncate"
                                                                style="max-width: 200px;">{{ $role->description }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-light text-dark">{{ $role->guard_name }}</span></td>
                                            <td>
                                                <span
                                                    class="badge bg-info-subtle text-info">{{ $role->permissions->count() }}</span>
                                            </td>
                                            <td>
                                                @can('roles.manage')
                                                    <div class="hstack gap-2">
                                                        <a href="{{ route('roles.edit', $role->id) }}"
                                                            class="btn btn-sm btn-soft-primary" data-bs-toggle="tooltip"
                                                            title="{{ __('users::roles.edit') }}">
                                                            <i class="ri-edit-2-line"></i>
                                                        </a>
                                                        @if ($role->name !== 'super-admin')
                                                            <button type="button" class="btn btn-sm btn-soft-danger"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('users::roles.delete') }}"
                                                                onclick="if(confirm('{{ __('users::roles.are_you_sure') }}')) document.getElementById('deleteForm{{ $role->id }}').submit();">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Individual Delete Forms -->
    @foreach ($roles as $role)
        @if ($role->name !== 'super-admin')
            <form action="{{ route('roles.destroy', $role->id) }}" method="POST" id="deleteForm{{ $role->id }}"
                class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endif
    @endforeach
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkAll = document.getElementById('checkAll');
            const childChecks = document.querySelectorAll('.child-check');
            const bulkActions = document.getElementById('bulkActionToolbar');
            const selectedCount = document.getElementById('selectedCount');

            window.updateBulkActions = function() {
                const checkedCount = document.querySelectorAll('.child-check:checked').length;
                if (selectedCount) selectedCount.textContent = checkedCount;

                if (bulkActions) {
                    if (checkedCount > 0) {
                        bulkActions.style.display = 'block';
                        bulkActions.classList.remove('animate__fadeOutDown');
                        bulkActions.classList.add('animate__fadeInUp');
                    } else {
                        bulkActions.classList.remove('animate__fadeInUp');
                        bulkActions.classList.add('animate__fadeOutDown');
                        setTimeout(() => {
                            if (document.querySelectorAll('.child-check:checked').length === 0) {
                                bulkActions.style.display = 'none';
                            }
                        }, 500);
                    }
                }
            }

            window.unselectAll = function() {
                if (checkAll) checkAll.checked = false;
                childChecks.forEach(check => {
                    check.checked = false;
                });
                updateBulkActions();
            }

            if (checkAll) {
                checkAll.addEventListener('change', function() {
                    childChecks.forEach(check => {
                        if (!check.disabled) check.checked = checkAll.checked;
                    });
                    updateBulkActions();
                });
            }

            childChecks.forEach(check => {
                check.addEventListener('change', function() {
                    updateBulkActions();
                    const allChecked = document.querySelectorAll('.child-check:checked').length ===
                        document.querySelectorAll('.child-check:not(:disabled)').length;
                    if (checkAll) checkAll.checked = allChecked;
                });
            });
        });
    </script>
@endpush
