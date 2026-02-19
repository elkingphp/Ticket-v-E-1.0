# Roles & Permissions System

The system relies on the `spatie/laravel-permission` package with deep customizations to support modular architecture.

## Key Features
* **Modular Permissions**: Each permission is linked to a specific module (`module` column in the permissions table).
* **Granular Control**: Control operations (View, Add, Edit, Delete) via Policies.
* **Role-Based Access**: Distribute permissions through roles.

## Policies
Policies are used within the Application layer of each module. Example:
* `UserPolicy`: Controls member management.
* `SystemPolicy`: Controls system settings.

## Usage in Code
Inside Blade views:
```php
@can('users.create')
    <!-- Add button -->
@endcan
```

Inside Controllers:
```php
$this->authorize('view', $user);
```

## Automatic Update
When a new module is activated, it is recommended to run its migrations, which should contain the necessary permissions for that module.
