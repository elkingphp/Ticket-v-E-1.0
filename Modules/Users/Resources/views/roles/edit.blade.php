@extends('core::layouts.master')

@section('title', isset($role) ? __('users::roles.edit_role') : __('users::roles.create_role'))

@push('styles')
    <style>
        .perm-card {
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid var(--vz-border-color) !important;
            position: relative;
            background: #fff;
            border-radius: 10px;
        }

        .perm-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            border-color: var(--vz-primary-rgb, 0.5) !important;
        }

        .btn-check:checked+.perm-card {
            background-color: rgba(var(--vz-primary-rgb), 0.03) !important;
            border-color: var(--vz-primary) !important;
            box-shadow: 0 0 0 0.2rem rgba(var(--vz-primary-rgb), 0.1) !important;
        }

        .action-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 1.2rem;
        }

        .icon-view {
            background: rgba(53, 119, 241, 0.1);
            color: #3577f1;
        }

        .icon-create {
            background: rgba(5, 176, 133, 0.1);
            color: #05b085;
        }

        .icon-edit {
            background: rgba(255, 191, 0, 0.1);
            color: #ffbf00;
        }

        .icon-delete {
            background: rgba(240, 101, 72, 0.1);
            color: #f06548;
        }

        .icon-manage {
            background: rgba(105, 94, 239, 0.1);
            color: #695eef;
        }

        .perm-check-icon {
            position: absolute;
            top: 12px;
            right: 12px;
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 2;
        }

        .btn-check:checked+.perm-card .perm-check-icon {
            opacity: 1;
            transform: scale(1);
        }

        [dir="rtl"] .perm-check-icon {
            right: auto;
            left: 12px;
        }

        .module-group {
            border-radius: 16px;
            overflow: hidden;
            border: none;
            margin-bottom: 2.5rem;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .module-header {
            background: #f8f9fa;
            padding: 1.25rem 1.75rem;
            border-bottom: 1px solid #edf2f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .resource-title {
            position: relative;
            padding-left: 1rem;
            margin-bottom: 1.5rem;
        }

        .resource-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 18px;
            background: var(--vz-primary);
            border-radius: 2px;
        }

        [dir="rtl"] .resource-title {
            padding-left: 0;
            padding-right: 1rem;
        }

        [dir="rtl"] .resource-title::before {
            left: auto;
            right: 0;
        }

        .hr-dotted {
            border: none;
            border-top: 2px dotted #CCCCCC;
            opacity: 1;
            margin: 2rem 0;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ isset($role) ? route('roles.update', $role->id) : route('roles.store') }}" method="POST">
                @csrf
                @if (isset($role))
                    @method('PUT')
                @endif

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">{{ __('users::roles.role_details') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="display_name" class="form-label fw-bold">{{ __('users::roles.role_name') }}
                                        <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-light text-muted"><i
                                                class="ri-shield-keyhole-line"></i></span>
                                        <input type="text"
                                            class="form-control @error('display_name') is-invalid @enderror"
                                            id="display_name" name="display_name"
                                            value="{{ old('display_name', isset($role) ? $role->display_name : '') }}"
                                            placeholder="{{ __('users::roles.enter_role_name') }}" required>
                                    </div>
                                    @error('display_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @if (isset($role) && $role->name === 'super-admin')
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label
                                            class="form-label fw-bold text-muted">{{ __('users::roles.technical_name') }}</label>
                                        <input type="text" class="form-control bg-light border-light text-muted"
                                            value="{{ $role->name }}" readonly disabled>
                                        <small class="text-info">{{ __('users::roles.protected_role_warning') }}</small>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">{{ __('users::roles.description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                rows="3" placeholder="{{ __('users::roles.enter_role_description') }}">{{ old('description', isset($role) ? $role->description : '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="hr-dotted">

                        <h5 class="mb-4 d-flex align-items-center">
                            <i class="ri-lock-2-line text-primary me-2"></i>
                            {{ __('users::roles.permissions_by_module') }}
                        </h5>

                        <div class="row">
                            @foreach ($permissions as $module => $resources)
                                <div class="col-12">
                                    <div class="module-group shadow-sm">
                                        <div class="module-header">
                                            <h6 class="mb-0 fw-bold text-uppercase text-primary">
                                                <i class="ri-stack-line align-middle me-1"></i>
                                                {{ $moduleLabels[$module] ?? $module }}
                                            </h6>
                                            <div class="form-check form-switch form-switch-right form-switch-md">
                                                <label
                                                    class="form-check-label text-muted fs-12">{{ __('users::roles.select_all') }}</label>
                                                <input class="form-check-input select-all-module" type="checkbox"
                                                    data-module="{{ Str::slug($module) }}">
                                            </div>
                                        </div>
                                        <div class="card-body p-4">
                                            @foreach ($resources as $resource => $modulePermissions)
                                                <div class="col-12 mt-2">
                                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                                        <h6 class="resource-title fw-bold fs-14 text-dark mb-0">
                                                            {{ $resourceLabels[$resource] ?? ucfirst(str_replace(['.', '_'], ' ', $resource)) }}
                                                        </h6>
                                                        <div class="form-check">
                                                            <input class="form-check-input select-all-resource"
                                                                type="checkbox"
                                                                data-resource="{{ Str::slug($module . '-' . $resource) }}"
                                                                id="select-all-{{ Str::slug($module . '-' . $resource) }}">
                                                            <label class="form-check-label fs-11 text-muted"
                                                                for="select-all-{{ Str::slug($module . '-' . $resource) }}">
                                                                {{ __('users::roles.select_all') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row g-3 mb-5">
                                                    @foreach ($modulePermissions as $permission)
                                                        @php
                                                            $action = last(explode('.', $permission->name));
                                                            $iconClass = 'ri-shield-user-line';
                                                            $typeClass = 'icon-manage';

                                                            if (Str::contains($action, ['view', 'access', 'report'])) {
                                                                $iconClass = 'ri-eye-line';
                                                                $typeClass = 'icon-view';
                                                            } elseif (
                                                                Str::contains($action, ['create', 'store', 'import'])
                                                            ) {
                                                                $iconClass = 'ri-add-circle-line';
                                                                $typeClass = 'icon-create';
                                                            } elseif (Str::contains($action, ['edit', 'update'])) {
                                                                $iconClass = 'ri-edit-2-line';
                                                                $typeClass = 'icon-edit';
                                                            } elseif (Str::contains($action, ['delete', 'destroy'])) {
                                                                $iconClass = 'ri-delete-bin-line';
                                                                $typeClass = 'icon-delete';
                                                            } else {
                                                                $iconClass = 'ri-settings-4-line';
                                                                $typeClass = 'icon-manage';
                                                            }

                                                            // Direct flat map lookup (built by controller)
                                                            $pTrans = $permissionMap[$permission->name] ?? null;
                                                            $hasTrans = is_array($pTrans) && isset($pTrans['title']);
                                                            $title = $hasTrans ? $pTrans['title'] : $permission->name;
                                                            $desc = $hasTrans
                                                                ? $pTrans['description']
                                                                : str_replace('_', ' ', $permission->name);
                                                            $desc = preg_replace(
                                                                '/\[([^\]]+)\]\(([^\)]+)\)/',
                                                                '<a href="$2" class="text-primary text-decoration-underline" onclick="event.stopPropagation();">$1</a>',
                                                                e($desc),
                                                            );
                                                        @endphp
                                                        <div class="col-xxl-3 col-lg-4 col-md-6">
                                                            <input type="checkbox"
                                                                class="btn-check perm-checkbox-{{ Str::slug($module) }} perm-checkbox-resource-{{ Str::slug($module . '-' . $resource) }}"
                                                                name="permissions[]" value="{{ $permission->id }}"
                                                                id="perm-{{ $permission->id }}"
                                                                {{ in_array($permission->id, old('permissions', isset($rolePermissions) ? $rolePermissions : [])) ? 'checked' : '' }}>
                                                            <label
                                                                class="btn perm-card p-3 shadow-none w-100 text-start h-100"
                                                                for="perm-{{ $permission->id }}">

                                                                <div class="{{ $typeClass }} action-icon">
                                                                    <i class="{{ $iconClass }}"></i>
                                                                </div>

                                                                <div class="flex-grow-1">
                                                                    <h6 class="mb-1 fs-13 fw-bold text-dark">
                                                                        {{ $title }}
                                                                    </h6>
                                                                    <p class="text-muted fs-11 mb-0 line-clamp-2"
                                                                        style="font-weight: 400; line-height: 1.4;">
                                                                        {!! $desc !!}
                                                                    </p>
                                                                </div>

                                                                <div class="perm-check-icon text-primary">
                                                                    <i class="ri-checkbox-circle-fill fs-22"></i>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @error('permissions')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        <div class="mt-4 pt-4 border-top text-{{ app()->getLocale() == 'ar' ? 'start' : 'end' }}">
                            <a href="{{ route('roles.index') }}"
                                class="btn btn-soft-dark px-4 me-2">{{ __('users::roles.cancel') }}</a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold">
                                <i class="ri-save-line align-bottom me-1"></i>
                                {{ isset($role) ? __('users::roles.save') : __('users::roles.create_role') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select all for module functionality
            const selectAllCheckboxes = document.querySelectorAll('.select-all-module');

            selectAllCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const moduleSlug = this.getAttribute('data-module');
                    const modulePermissions = document.querySelectorAll('.perm-checkbox-' +
                        moduleSlug);

                    modulePermissions.forEach(perm => {
                        perm.checked = this.checked;
                    });
                });
            });

            // Update select-all state based on individual checkboxes
            const allPermCheckboxes = document.querySelectorAll('input[name="permissions[]"]');
            allPermCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const classList = Array.from(this.classList);
                    const moduleClass = classList.find(c => c.startsWith('perm-checkbox-'));
                    if (moduleClass) {
                        const moduleSlug = moduleClass.replace('perm-checkbox-', '');
                        const masterCheckbox = document.querySelector(
                            '.select-all-module[data-module="' + moduleSlug + '"]');
                        if (masterCheckbox) {
                            const siblings = document.querySelectorAll('.' + moduleClass);
                            const allChecked = Array.from(siblings).every(s => s.checked);
                            masterCheckbox.checked = allChecked;
                        }
                    }
                });
            });

            // Select all for resource functionality
            const selectAllResourceCheckboxes = document.querySelectorAll('.select-all-resource');
            selectAllResourceCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const resourceSlug = this.getAttribute('data-resource');
                    const checkboxes = document.querySelectorAll(
                        `.perm-checkbox-resource-${resourceSlug}`);
                    checkboxes.forEach(cb => {
                        cb.checked = this.checked;
                    });
                });
            });

            // Initial state check for master checkboxes
            selectAllCheckboxes.forEach(checkbox => {
                const moduleSlug = checkbox.getAttribute('data-module');
                const siblings = document.querySelectorAll('.perm-checkbox-' + moduleSlug);
                const allChecked = Array.from(siblings).every(s => s.checked);
                if (siblings.length > 0) checkbox.checked = allChecked;
            });

            selectAllResourceCheckboxes.forEach(checkbox => {
                const resourceSlug = checkbox.getAttribute('data-resource');
                const siblings = document.querySelectorAll(`.perm-checkbox-resource-${resourceSlug}`);
                const allChecked = Array.from(siblings).every(s => s.checked);
                if (siblings.length > 0) checkbox.checked = allChecked;
            });
        });
    </script>
@endpush
