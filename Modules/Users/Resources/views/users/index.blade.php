@extends('core::layouts.master')

@section('title', __('users::users.users_management'))

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
    </style>
@endpush
@section('content')
    <div class="row">
        <!-- Statistics Widgets -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                {{ __('users::users.total_users') }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $stats['total'] }}</h4>
                            <span class="badge bg-primary-subtle text-primary">{{ __('users::users.users') }}</span>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle rounded fs-3" data-bs-toggle="tooltip"
                                title="{{ __('users::users.total_users_help' ?? 'All registered users in the system') }}">
                                <i class="ri-user-line text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                {{ __('users::users.active_users') }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $stats['active'] }}</h4>
                            <span class="badge bg-success-subtle text-success">{{ __('users::users.active') }}</span>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle rounded fs-3" data-bs-toggle="tooltip"
                                title="{{ __('users::users.active_users_help') }}">
                                <i class="ri-user-follow-line text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                {{ __('users::users.blocked_users') }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $stats['blocked'] }}</h4>
                            <span class="badge bg-danger-subtle text-danger">{{ __('users::users.blocked') }}</span>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-danger-subtle rounded fs-3" data-bs-toggle="tooltip"
                                title="{{ __('users::users.blocked_users_help') }}">
                                <i class="ri-user-forbid-line text-danger"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                {{ __('users::users.new_users_this_month') }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $stats['new_this_month'] }}</h4>
                            <span class="badge bg-info-subtle text-info">{{ __('users::users.joined_at') }}</span>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-info-subtle rounded fs-3" data-bs-toggle="tooltip"
                                title="{{ __('users::users.new_this_month_help') }}">
                                <i class="ri-user-add-line text-info"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if (session('success') || session('error'))
        <div class="row">
            <div class="col-12">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                        <i class="ri-checkbox-circle-line me-2 align-middle"></i>
                        <strong>{{ session('success') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                        <i class="ri-error-warning-line me-2 align-middle"></i>
                        <strong>{{ session('error') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 mt-3 bg-transparent">
                    <div class="row g-4 align-items-center">
                        <div class="col-sm">
                            <h5 class="card-title mb-0 fs-18 fw-bold">{{ __('users::users.users_list') }}</h5>
                        </div>
                        <div class="col-sm-auto">
                            <div class="hstack gap-2">
                                @can('users.view')
                                    <a href="{{ route('users.export') }}" class="btn btn-soft-info"><i
                                            class="ri-file-download-line align-bottom me-1"></i>
                                        {{ __('users::users.export_csv') }}</a>
                                @endcan
                                @can('users.create')
                                    <button type="button" class="btn btn-soft-secondary" data-bs-toggle="modal"
                                        data-bs-target="#importModal">
                                        <i class="ri-file-upload-line align-bottom me-1"></i>
                                        {{ __('users::users.import_csv') }}
                                    </button>
                                    <a href="{{ route('users.create') }}" class="btn btn-primary add-btn"><i
                                            class="ri-add-line align-bottom me-1"></i> {{ __('users::users.add_user') }}</a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-0">
                    <!-- Filter Form -->
                    <div class="p-3 mb-4 bg-light bg-opacity-50 rounded-3">
                        <form method="GET" action="{{ route('users.index') }}">
                            <div class="row g-3">
                                <div class="col-xxl-4 col-sm-6">
                                    <div class="search-box">
                                        <input type="text" class="form-control border-light shadow-none bg-white"
                                            name="search" value="{{ request('search') }}"
                                            placeholder="{{ __('users::users.search_placeholder') }}">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-xxl-2 col-sm-6">
                                    <select class="form-select border-light shadow-none bg-white" name="status">
                                        <option value="">{{ __('users::users.all_statuses') }}</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                            {{ __('users::users.active') }}</option>
                                        <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>
                                            {{ __('users::users.blocked') }}</option>
                                    </select>
                                </div>
                                <div class="col-xxl-2 col-sm-6">
                                    <select class="form-select border-light shadow-none bg-white" name="role">
                                        <option value="">{{ __('users::users.all_roles') }}</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}"
                                                {{ request('role') == $role->name ? 'selected' : '' }}>
                                                {{ $role->display_name ?? $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xxl-4 col-sm-6 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-grow-1"> <i
                                            class="ri-filter-3-line me-1 align-bottom"></i>
                                        {{ __('users::users.filter') }}</button>
                                    <a href="{{ route('users.index') }}" class="btn btn-soft-dark"> <i
                                            class="ri-refresh-line align-bottom"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>



                    <form action="{{ route('users.bulk-actions') }}" method="POST" id="bulkActionForm">
                        @csrf
                        <!-- Bulk Actions Toolbar (Premium Floating Design) -->
                        <div id="bulkActionToolbar"
                            style="display: none; position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 1050; min-width: 500px;">
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
                                            <span class="fw-medium">{{ __('users::users.selected') }}</span>
                                        </div>
                                        <div class="vr bg-white bg-opacity-20 my-2"></div>
                                        <div class="hstack gap-2">
                                            <button type="submit" name="action" value="activate"
                                                class="btn btn-link text-white text-decoration-none px-2 bulk-btn">
                                                <i
                                                    class="ri-checkbox-circle-fill align-bottom me-1 fs-16 text-success"></i>
                                                {{ __('users::users.activate_selected') }}
                                            </button>
                                            <button type="submit" name="action" value="block"
                                                class="btn btn-link text-white text-decoration-none px-2 bulk-btn">
                                                <i class="ri-forbid-2-fill align-bottom me-1 fs-16 text-warning"></i>
                                                {{ __('users::users.block_selected') }}
                                            </button>
                                            <button type="submit" name="action" value="delete"
                                                class="btn btn-link text-white text-decoration-none px-2 bulk-btn"
                                                onclick="return confirm('{{ __('users::users.bulk_delete_confirm') }}')">
                                                <i class="ri-delete-bin-5-fill align-bottom me-1 fs-16 text-danger"></i>
                                                {{ __('users::users.delete_selected') }}
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
                            <table class="table table-hover align-middle table-nowrap mb-0" id="usersTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col" style="width: 50px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="checkAll"
                                                    value="option">
                                            </div>
                                        </th>
                                        <th class="sort" data-sort="user">{{ __('users::users.user') }}</th>
                                        <th class="sort" data-sort="email">{{ __('users::users.email') }}</th>
                                        <th class="sort" data-sort="role">{{ __('users::users.roles') }}</th>
                                        <th class="sort" data-sort="status">{{ __('users::users.status') }}</th>
                                        <th class="sort" data-sort="security">{{ __('users::users.security_status') }}
                                        </th>
                                        <th class="sort" data-sort="joined_at">{{ __('users::users.joined_at') }}</th>
                                        <th class="text-end px-4">{{ __('users::users.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="list form-check-all">
                                    @foreach ($users as $user)
                                        <tr>
                                            <th scope="row">
                                                <div class="form-check">
                                                    <input class="form-check-input child-check" type="checkbox"
                                                        name="ids[]" value="{{ $user->id }}">
                                                </div>
                                            </th>
                                            <td class="user">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        @if ($user->avatar)
                                                            <img src="{{ asset('storage/' . $user->avatar) }}"
                                                                alt="" class="avatar-sm rounded-circle shadow-sm">
                                                        @else
                                                            <div class="avatar-sm flex-shrink-0">
                                                                <span
                                                                    class="avatar-title rounded-circle bg-primary-subtle text-primary fs-14 fw-bold shadow-sm">
                                                                    {{ $user->initials }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="fs-14 mb-1 fw-bold">{{ $user->full_name }}</h6>
                                                        <p class="text-muted mb-0 fs-12">
                                                            @<span>{{ $user->username }}</span></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="email">
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-mail-line me-1 text-muted"></i>
                                                    {{ $user->email }}
                                                </div>
                                            </td>
                                            <td class="role">
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach ($user->roles as $role)
                                                        <span
                                                            class="badge rounded-pill bg-info-subtle text-info">{{ $role->display_name ?? $role->name }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="status">
                                                @if ($user->status == 'active')
                                                    <span
                                                        class="badge rounded-pill bg-success-subtle text-success text-uppercase">
                                                        <i class="ri-checkbox-circle-fill align-middle me-1"></i>
                                                        {{ __('users::users.active') }}
                                                    </span>
                                                @else
                                                    <span
                                                        class="badge rounded-pill bg-danger-subtle text-danger text-uppercase">
                                                        <i class="ri-close-circle-fill align-middle me-1"></i>
                                                        {{ __('users::users.blocked') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="security">
                                                @php $security = $user->security_status; @endphp
                                                <span class="badge rounded-pill {{ $security['class'] }}">
                                                    <i class="ri-shield-check-line align-middle me-1"></i>
                                                    {{ $security['label'] }}
                                                </span>
                                            </td>
                                            <td class="joined_at">
                                                <span class="text-muted">{{ $user->created_at->format('d M, Y') }}</span>
                                            </td>
                                            <td class="text-end px-4">
                                                <div class="dropdown d-inline-block">
                                                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ri-more-fill align-middle"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a href="{{ route('users.show', $user->id) }}"
                                                                class="dropdown-item"><i
                                                                    class="ri-eye-line align-bottom me-2 text-muted"></i>
                                                                {{ __('users::users.view') }}</a></li>
                                                        @can('users.edit')
                                                            <li><a href="{{ route('users.edit', $user->id) }}"
                                                                    class="dropdown-item edit-item-btn"><i
                                                                        class="ri-pencil-fill align-bottom me-2 text-muted"></i>
                                                                    {{ __('users::users.edit') }}</a></li>
                                                        @endcan
                                                        @can('audit.view')
                                                            <li><a href="{{ route('audit.index', ['user_id' => $user->id]) }}"
                                                                    class="dropdown-item"><i
                                                                        class="ri-history-line align-bottom me-2 text-muted"></i>
                                                                    {{ __('users::users.view_logs') }}</a></li>
                                                        @endcan
                                                        @can('users.delete')
                                                            <li>
                                                                <button type="submit" form="deleteForm{{ $user->id }}"
                                                                    class="dropdown-item remove-item-btn"
                                                                    onclick="return confirm('{{ __('users::users.delete_confirm') }}')">
                                                                    <i
                                                                        class="ri-delete-bin-fill align-bottom me-2 text-muted"></i>
                                                                    {{ __('users::users.remove') }}
                                                                </button>
                                                            </li>
                                                        @endcan
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Showing <span class="fw-semibold">{{ $users->firstItem() }}</span> to <span
                                class="fw-semibold">{{ $users->lastItem() }}</span> of <span
                                class="fw-semibold">{{ $users->total() }}</span> results
                        </div>
                        <div>
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header p-3 bg-soft-info">
                    <h5 class="modal-title" id="importModalLabel">{{ __('users::users.import_users') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-4 text-center">
                            <div class="avatar-lg mx-auto mb-3">
                                <div class="avatar-title bg-light text-primary rounded-circle fs-24">
                                    <i class="ri-file-excel-2-line"></i>
                                </div>
                            </div>
                            <p class="text-muted">
                                {{ __('users::users.import_csv_description' ?? 'Upload your CSV file to import multiple users at once.') }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <label for="file" class="form-label fw-bold">{{ __('users::users.csv_file') }}</label>
                            <input type="file" class="form-control" id="file" name="file" required
                                accept=".csv,.xlsx">
                        </div>
                        <div class="p-3 bg-light rounded-2 border border-dashed border-primary">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="ri-information-line text-primary fs-20"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <a href="{{ route('users.download-template') }}"
                                        class="fw-medium text-primary text-decoration-underline">
                                        {{ __('users::users.download_template') }}
                                    </a>
                                    <p class="text-muted mb-0 fs-12">Use this template for correct formatting.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">{{ __('users::users.cancel') }}</button>
                        <button type="submit" class="btn btn-primary px-4">{{ __('users::users.import') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Individual Delete Forms -->
    @foreach ($users as $user)
        @can('users.delete')
            <form action="{{ route('users.destroy', $user->id) }}" method="POST" id="deleteForm{{ $user->id }}"
                class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endcan
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
                        check.checked = checkAll.checked;
                    });
                    updateBulkActions();
                });
            }

            childChecks.forEach(check => {
                check.addEventListener('change', function() {
                    updateBulkActions();
                    const allChecked = document.querySelectorAll('.child-check:checked').length ===
                        childChecks.length;
                    if (checkAll) checkAll.checked = allChecked;
                });
            });
        });
    </script>
@endpush
