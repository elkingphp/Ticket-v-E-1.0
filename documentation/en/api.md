# API Documentation

The system supports a stateless API secured and designed for fast responses.

## Standards
* **Format**: JSON only.
* **Authentication**: Support for Laravel Sanctum or JWT (depending on the selected Auth module).
* **Versioning**: Requests are organized via `/api/v1/...`.

## API Distribution
Each module contains an independent `Routes/api.php` file. These files are automatically aggregated by the core system.

## Default Response Structure
```json
{
    "success": true,
    "data": { ... },
    "message": "Operation successful",
    "meta": {
        "timestamp": "2026-02-12T..."
    }
}
```

## Error Handling
Exception handling is unified across the system to return proper HTTP codes (401, 403, 404, 422, 500) with clear messages in both Arabic and English.
