@extends('core::layouts.master')

@section('title', __('users::users.users_management'))

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0 mt-3">
                <div class="row g-4 align-items-center">
                    <div class="col-sm">
                        <div>
                            <h5 class="card-title mb-0">{{ __('users::users.users_list') }}</h5>
                        </div>
                    </div>
                    <div class="col-sm-auto">
                        <div class="hstack gap-2">
                            @can('view users')
                                <a href="{{ route('users.export') }}" class="btn btn-info"><i class="ri-file-download-line align-bottom me-1"></i> {{ __('users::users.export_csv') }}</a>
                            @endcan
                            @can('create users')
                                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                                    <i class="ri-file-upload-line align-bottom me-1"></i> {{ __('users::users.import_csv') }}
                                </button>
                                <a href="{{ route('users.create') }}" class="btn btn-success add-btn"><i class="ri-add-line align-bottom me-1"></i> {{ __('users::users.add_user') }}</a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Modal -->
            <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="importModalLabel">{{ __('users::users.import_users') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="file" class="form-label">{{ __('users::users.csv_file') }}</label>
                                    <input type="file" class="form-control" id="file" name="file" required accept=".csv,.xlsx">
                                </div>
                                <div class="mb-3">
                                    <a href="{{ route('users.download-template') }}" class="text-primary text-decoration-underline">
                                        <i class="ri-download-cloud-2-line align-middle me-1"></i> {{ __('users::users.download_template') }}
                                    </a>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('users::users.cancel') }}</button>
                                <button type="submit" class="btn btn-primary">{{ __('users::users.import') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Filter Form -->
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <form method="GET" action="{{ route('users.index') }}">
                            <div class="row g-3">
                                <div class="col-xxl-3 col-sm-6">
                                    <div class="search-box">
                                        <input type="text" class="form-control search" name="search" value="{{ request('search') }}" placeholder="{{ __('users::users.search_placeholder') }}">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-xxl-2 col-sm-4">
                                    <div>
                                        <select class="form-select" name="status">
                                            <option value="">{{ __('users::users.all_statuses') }}</option>
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('users::users.active') }}</option>
                                            <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>{{ __('users::users.blocked') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xxl-2 col-sm-4">
                                    <div>
                                        <select class="form-select" name="role">
                                            <option value="">{{ __('users::users.all_roles') }}</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xxl-1 col-sm-4">
                                    <div>
                                        <button type="submit" class="btn btn-primary w-100"> <i class="ri-search-line me-1 align-bottom"></i> {{ __('users::users.filter') }}</button>
                                    </div>
                                </div>
                                <div class="col-xxl-1 col-sm-4">
                                    <div>
                                        <a href="{{ route('users.index') }}" class="btn btn-light w-100"> {{ __('users::users.reset') }}</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <form action="{{ route('users.bulk-actions') }}" method="POST" id="bulkActionForm">
                    @csrf
                    <!-- Bulk Actions Toolbar -->
                    <div class="row mb-3" id="bulkActionToolbar" style="display: none;">
                        <div class="col-lg-12">
                            <div class="hstack gap-2">
                                <span class="fw-semibold me-2" id="selectedCount">0 {{ __('users::users.selected') }}</span>
                                <button type="submit" name="action" value="activate" class="btn btn-sm btn-soft-success"><i class="ri-check-double-line align-bottom me-1"></i> {{ __('users::users.activate_selected') }}</button>
                                <button type="submit" name="action" value="block" class="btn btn-sm btn-soft-warning"><i class="ri-prohibited-line align-bottom me-1"></i> {{ __('users::users.block_selected') }}</button>
                                <button type="submit" name="action" value="delete" class="btn btn-sm btn-soft-danger" onclick="return confirm('{{ __('users::users.bulk_delete_confirm') }}')"><i class="ri-delete-bin-line align-bottom me-1"></i> {{ __('users::users.delete_selected') }}</button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive table-card mb-1">
                        <table class="table align-middle table-nowrap" id="usersTable">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th scope="col" style="width: 50px;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                                        </div>
                                    </th>
                                    <th class="sort" data-sort="user">{{ __('users::users.user') }}</th>
                                    <th class="sort" data-sort="email">{{ __('users::users.email') }}</th>
                                    <th class="sort" data-sort="role">{{ __('users::users.roles') }}</th>
                                    <th class="sort" data-sort="status">{{ __('users::users.status') }}</th>
                                    <th class="sort" data-sort="security">{{ __('users::users.security_status') }}</th>
                                    <th class="sort" data-sort="joined_at">{{ __('users::users.joined_at') }}</th>
                                    <th class="sort" data-sort="action">{{ __('users::users.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list form-check-all">
                                @foreach($users as $user)
                                    <tr>
                                        <th scope="row">
                                            <div class="form-check">
                                                <input class="form-check-input child-check" type="checkbox" name="ids[]" value="{{ $user->id }}">
                                            </div>
                                        </th>
                                        <td class="user">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    @if($user->avatar)
                                                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="" class="avatar-xs rounded-circle">
                                                    @else
                                                        <div class="avatar-xs">
                                                            <span class="avatar-title rounded-circle bg-primary-subtle text-primary fs-14">
                                                                {{ $user->initials }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1 ms-2 name">{{ $user->full_name }}</div>
                                            </div>
                                        </td>
                                        <td class="email">{{ $user->email }}</td>
                                        <td class="role">
                                            @foreach($user->roles as $role)
                                                <span class="badge bg-primary-subtle text-primary">{{ $role->name }}</span>
                                            @endforeach
                                        </td>
                                        <td class="status">
                                            @if($user->status == 'active')
                                                <span class="badge bg-success-subtle text-success text-uppercase">{{ __('users::users.active') }}</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger text-uppercase">{{ __('users::users.blocked') }}</span>
                                            @endif
                                        </td>
                                        <td class="security">
                                            @php $security = $user->security_status; @endphp
                                            <span class="badge {{ $security['class'] }}">{{ $security['label'] }}</span>
                                        </td>
                                        <td class="joined_at">{{ $user->created_at->format('d M, Y') }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                @can('view audit logs')
                                                    <a href="{{ route('audit.index', ['user_id' => $user->id]) }}" class="btn btn-sm btn-ghost-info" title="{{ __('users::users.view_logs') }}">
                                                        <i class="ri-history-line fs-16"></i>
                                                    </a>
                                                @endcan
                                                @can('edit users')
                                                    <div class="edit">
                                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-success edit-item-btn">{{ __('users::users.edit') }}</a>
                                                    </div>
                                                @endcan
                                                @can('delete users')
                                                    <div class="remove">
                                                        <button type="submit" form="deleteForm{{ $user->id }}" class="btn btn-sm btn-danger remove-item-btn" onclick="return confirm('{{ __('users::users.delete_confirm') }}')">{{ __('users::users.remove') }}</button>
                                                    </div>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-3">
                        {{ $users->links() }}
                    </div>
                </form>

                <!-- Individual Delete Forms -->
                @foreach($users as $user)
                    @can('delete users')
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" id="deleteForm{{ $user->id }}" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endcan
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('checkAll');
        const childChecks = document.querySelectorAll('.child-check');
        const bulkActions = document.getElementById('bulkActionToolbar');
        const selectedCount = document.getElementById('selectedCount');
        
        function updateBulkActions() {
            const checkedCount = document.querySelectorAll('.child-check:checked').length;
            selectedCount.textContent = checkedCount + ' {{ __('users::users.selected') }}';
            bulkActions.style.display = checkedCount > 0 ? 'block' : 'none';
        }

        if(checkAll) {
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
                const allChecked = document.querySelectorAll('.child-check:checked').length === childChecks.length;
                if(checkAll) checkAll.checked = allChecked;
            });
        });
    });
</script>
@endpush
