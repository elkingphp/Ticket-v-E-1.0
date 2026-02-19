# Module Management

The system is managed via the `nwidart/laravel-modules` engine with custom enhancements to ensure stability.

## Core Operations
You can manage modules via `ModuleManagerService` or Artisan commands:

### 1. Create a New Module
`php artisan module:make MyModule`

### 2. Enable/Disable Modules
* Dependencies are checked automatically.
* You cannot disable a module that other active modules depend on.
* Module statuses are cached in Redis for high-speed access in the middleware.

### 3. Hooks System
When a module status changes, special events are fired:
* `ModuleBooted`: On activation.
* `ModuleInstalled`: On initial installation.
* `ModuleDisabled`: On deactivation.

### 4. Asset Management (Vite)
Assets for each module are loaded independently. Ensure you add paths to the module's `vite.config.js` to enable Multi-entry:
```javascript
export const paths = [
    'Modules/MyModule/Resources/assets/sass/app.scss',
    'Modules/MyModule/Resources/assets/js/app.js',
];
```

## Automatic Cleanup
When a module is deleted, the system automatically removes all associated Permissions to maintain database integrity.
