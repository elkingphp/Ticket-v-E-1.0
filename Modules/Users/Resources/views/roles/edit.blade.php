@extends('core::layouts.master')

@section('title', isset($role) ? __('Edit Role') : __('Create Role'))

@section('content')
<div class="row">
    <div class="col-lg-12">
        <form action="{{ isset($role) ? route('roles.update', $role->id) : route('roles.store') }}" method="POST">
            @csrf
            @if(isset($role)) @method('PUT') @endif

            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('Role Name') }}</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" 
                               value="{{ old('name', isset($role) ? $role->name : '') }}" placeholder="{{ __('Enter role name') }}" required
                               {{ isset($role) && $role->name === 'super-admin' ? 'readonly' : '' }}>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <h5 class="mt-4 mb-3">{{ __('Permissions by Module') }}</h5>
                    
                    <div class="row">
                        @foreach($permissions as $module => $modulePermissions)
                        <div class="col-md-6 mb-4">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">{{ $module }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($modulePermissions as $permission)
                                        <div class="col-md-6">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                       value="{{ $permission->id }}" id="perm-{{ $permission->id }}"
                                                       {{ in_array($permission->id, old('permissions', isset($rolePermissions) ? $rolePermissions : [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm-{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">{{ isset($role) ? __('Update Role') : __('Create Role') }}</button>
                        <a href="{{ route('roles.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
