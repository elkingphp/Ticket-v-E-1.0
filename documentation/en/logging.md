# Audit Logs & Activity Tracking

The logging system is one of the essential security pillars of this framework.

## How it Works
1. **Automatic Capture**: Through the `LogsActivity` trait added to models, any data change is detected.
2. **Background Processing**: Event data is sent to a Redis queue (`ProcessAuditLog` job).
3. **Storage**: The record is saved in the PostgreSQL database with the following info:
   * Who performed the action (User ID).
   * Event type.
   * Changed data (Request Data).
   * IP address and Browser Agent.
   * Module where the change occurred.

## Security & Privacy
* **Data Masking**: The system automatically searches for sensitive fields (e.g., password, token) and replaces them with a static string before saving.
* **Encryption**: Data stored as JSONB is protected and indexed for fast searching.

## Log Cleanup
An Artisan command runs daily to clean old records based on settings:
`php artisan audit:cleanup`
*The retention period is determined in the system settings.*
