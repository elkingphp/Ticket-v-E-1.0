# System Capabilities

The system possesses a set of enterprise features ready for use:

### 1. Modularized Core
* Complete separation of each function into an independent "module".
* Independent assets and databases for each module.
* Dependency management between modules to prevent system failure.

### 2. Dynamic Settings System
* Store settings in the database with Redis support for extreme speed.
* Automatic encryption for sensitive values (e.g., API keys).
* Singleton pattern ensures settings are loaded only once per request.

### 3. Smart Audit Logs & Activity
* Automatic tracking of all operations (Create, Update, Delete).
* Integrated with Redis queues to ensure no impact on system speed.
* Automatic masking of sensitive data before logging.

### 4. Advanced User System
* Support for Two-Factor Authentication (2FA) via TOTP.
* User status management (Active, Pending, Blocked) with documented reasons.
* Full support for multi-languages, local timezones, and theme modes (Dark/Light).

### 5. Backup & Restore
* Create backups for database and files with a single click.
* System restoration with automatic maintenance mode and a pre-restore safety backup.

### 6. Modern Frontend UI
* Full support for Velzon UI with advanced RTL system.
* Smart asset loading (Vite Multi-entry) including only what the current module needs.
