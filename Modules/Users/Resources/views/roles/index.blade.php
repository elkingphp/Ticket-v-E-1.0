@extends('core::layouts.master')

@section('title', __('users::roles.roles_management'))

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">{{ __('users::roles.roles_list') }}</h4>
                <div class="flex-shrink-0">
                    @can('manage roles')
                    <a href="{{ route('roles.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> {{ __('users::roles.add_new_role') }}
                    </a>
                    @endcan
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive table-card">
                    <table class="table table-nowrap table-striped-columns mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">{{ __('users::roles.id') }}</th>
                                <th scope="col">{{ __('users::roles.name') }}</th>
                                <th scope="col">{{ __('users::roles.guard') }}</th>
                                <th scope="col">{{ __('users::roles.permissions_count') }}</th>
                                <th scope="col">{{ __('users::roles.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                            <tr>
                                <td>{{ $role->id }}</td>
                                <td>{{ $role->name }}</td>
                                <td>{{ $role->guard_name }}</td>
                                <td>
                                    <span class="badge bg-info-subtle text-info">{{ $role->permissions->count() }}</span>
                                </td>
                                <td>
                                    @can('manage roles')
                                    <div class="d-flex gap-2">
                                        <div class="edit">
                                            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-primary edit-item-btn">{{ __('users::roles.edit') }}</a>
                                        </div>
                                        @if($role->name !== 'super-admin')
                                        <div class="remove">
                                            <form action="{{ route('roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('{{ __('users::roles.are_you_sure') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger remove-item-btn">{{ __('users::roles.delete') }}</button>
                                            </form>
                                        </div>
                                        @endif
                                    </div>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
