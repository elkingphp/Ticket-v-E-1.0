@extends('core::layouts.master')

@section('title', __('users::users.permissions_management'))

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0 mt-3">
                <div class="row g-4 align-items-center">
                    <div class="col-sm">
                        <h5 class="card-title mb-0">{{ __('users::users.permissions_list') }}</h5>
                    </div>
                    <div class="col-sm-auto">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPermissionModal">
                            <i class="ri-add-line align-bottom me-1"></i> {{ __('users::users.add_permission') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @foreach($permissions as $module => $modulePermissions)
                    <div class="mb-4">
                        <h6 class="text-primary text-uppercase">{{ $module }}</h6>
                        <div class="row">
                            @foreach($modulePermissions as $permission)
                                <div class="col-md-3 mb-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                                        <span>{{ $permission->name }}</span>
                                        <form action="{{ route('permissions.destroy', $permission->id) }}" method="POST" onsubmit="return confirm('{{ __('users::users.delete_confirm') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-0"><i class="ri-delete-bin-line"></i></button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Add Permission Modal -->
<div class="modal fade" id="addPermissionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('permissions.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('users::users.add_permission') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('users::users.permission_name') }}</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="e.g., view users" required>
                    </div>
                    <div class="mb-3">
                        <label for="module" class="form-label">{{ __('users::users.module') }}</label>
                        <input type="text" class="form-control" id="module" name="module" placeholder="e.g., Core" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('users::users.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('users::users.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
