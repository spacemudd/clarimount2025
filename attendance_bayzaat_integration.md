# Attendance Bayzat Integration System

## Overview

This document outlines the complete implementation of the attendance import and Bayzat API sync system for the IT Portal Laravel application. The system allows importing attendance data from CSV files and syncing it to Bayzat's attendance API on a per-company basis.

## System Architecture

### High-Level Flow

1. **Import Phase**: CSV attendance data is imported and validated
2. **Mapping Phase**: Fingerprint department names are mapped to companies
3. **Queue Phase**: Valid attendance records are queued for Bayzat sync
4. **Sync Phase**: Records are synced to Bayzat API in batches per company
5. **Monitoring Phase**: Track sync status, handle failures, and provide reports

### Core Components

- **Attendance Import System**: CSV processing and validation
- **Company-Department Mapping**: Link fingerprint departments to companies
- **Bayzat Configuration**: Per-company API settings
- **Sync Queue System**: Background job processing with retry logic
- **Monitoring Dashboard**: Status tracking and error reporting

## Database Schema

### 1. Companies Table Enhancement

```sql
-- Add fingerprint_report_name to existing companies table
ALTER TABLE companies ADD COLUMN fingerprint_report_name VARCHAR(255) NULL AFTER slug;
ALTER TABLE companies ADD INDEX idx_fingerprint_report_name (fingerprint_report_name);
```

**Purpose**: Map fingerprint report department names (e.g., "AdvLine") to actual companies (e.g., "Advanced Line for Technology").

### 2. Bayzat Configurations

```sql
CREATE TABLE bayzat_configs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    api_key TEXT NOT NULL, -- Encrypted
    api_url VARCHAR(255) DEFAULT 'https://integration.bayzat.com/attendance',
    is_enabled BOOLEAN DEFAULT TRUE,
    sync_frequency ENUM('manual', 'hourly', 'daily') DEFAULT 'manual',
    last_sync_at TIMESTAMP NULL,
    settings JSON NULL, -- Additional configuration
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_company_config (company_id)
);
```

**Purpose**: Store Bayzat API credentials and settings per company.

### 3. Attendance Imports

```sql
CREATE TABLE attendance_imports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    total_records INT DEFAULT 0,
    processed_records INT DEFAULT 0,
    successful_records INT DEFAULT 0,
    failed_records INT DEFAULT 0,
    validation_errors JSON NULL,
    unmapped_departments JSON NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    team_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    INDEX idx_status_team (status, team_id)
);
```

**Purpose**: Track attendance import batches and their processing status.

### 4. Attendance Import Records

```sql
CREATE TABLE attendance_import_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attendance_import_id BIGINT UNSIGNED NOT NULL,
    employee_id BIGINT UNSIGNED NULL, -- NULL if employee not found
    company_id BIGINT UNSIGNED NULL, -- Mapped from department
    csv_employee_id VARCHAR(255) NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    fingerprint_department VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    weekday VARCHAR(20) NOT NULL,
    check_in TIME NULL,
    check_out TIME NULL,
    clock_in TIME NULL,
    clock_out TIME NULL,
    -- Additional attendance fields from CSV...
    bayzat_sync_status ENUM('pending', 'syncing', 'synced', 'failed', 'skipped') DEFAULT 'pending',
    bayzat_sync_at TIMESTAMP NULL,
    bayzat_sync_error TEXT NULL,
    bayzat_retry_count INT DEFAULT 0,
    bayzat_next_retry_at TIMESTAMP NULL,
    validation_errors JSON NULL,
    is_valid BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (attendance_import_id) REFERENCES attendance_imports(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_import_date (attendance_import_id, date),
    INDEX idx_employee_date (csv_employee_id, date),
    INDEX idx_bayzat_sync (bayzat_sync_status, bayzat_next_retry_at)
);
```

**Purpose**: Store individual attendance records with sync status tracking.

### 5. Bayzat Sync Batches

```sql
CREATE TABLE bayzat_sync_batches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    attendance_import_id BIGINT UNSIGNED NOT NULL,
    total_records INT DEFAULT 0,
    synced_records INT DEFAULT 0,
    failed_records INT DEFAULT 0,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (attendance_import_id) REFERENCES attendance_imports(id) ON DELETE CASCADE,
    INDEX idx_company_status (company_id, status)
);
```

**Purpose**: Track sync job batches per company.

## Implementation Details

### 1. Models

#### Company Model Enhancement
```php
// app/Models/Company.php
class Company extends Model
{
    protected $fillable = [
        'name_en', 'name_ar', 'slug', 'company_email',
        'fingerprint_report_name', // Add this
        // ... other fields
    ];

    public function bayzatConfig()
    {
        return $this->hasOne(BayzatConfig::class);
    }

    public function attendanceImportRecords()
    {
        return $this->hasMany(AttendanceImportRecord::class);
    }
}
```

#### BayzatConfig Model
```php
// app/Models/BayzatConfig.php
class BayzatConfig extends Model
{
    protected $fillable = [
        'company_id', 'api_key', 'api_url', 'is_enabled',
        'sync_frequency', 'last_sync_at', 'settings'
    ];

    protected $casts = [
        'api_key' => 'encrypted', // Laravel encryption
        'settings' => 'array',
        'is_enabled' => 'boolean',
        'last_sync_at' => 'datetime'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
```

### 2. Services

#### AttendanceImportService
```php
// app/Services/AttendanceImportService.php
class AttendanceImportService
{
    public function processImport(UploadedFile $file, int $userId, int $teamId): AttendanceImport
    {
        // 1. Store file
        // 2. Create import record
        // 3. Validate CSV structure
        // 4. Process records with validation:
        //    - Check employee_id exists in employees table
        //    - Map fingerprint_department to company via companies.fingerprint_report_name
        //    - Validate required fields
        // 5. Store all records (valid and invalid)
        // 6. Queue valid records for Bayzat sync
        // 7. Update import status
    }

    private function validateRecord(array $record, array $existingEmployeeIds, array $companyMappings): array
    {
        // Validate employee ID exists
        // Map department to company
        // Return validation result with employee_id and company_id
    }

    private function queueForBayzatSync(AttendanceImport $import): void
    {
        // Group valid records by company
        // Create BayzatSyncBatch for each company
        // Dispatch sync jobs
    }
}
```

#### BayzatSyncService
```php
// app/Services/BayzatSyncService.php
class BayzatSyncService
{
    public function syncBatch(BayzatSyncBatch $batch): void
    {
        // 1. Get company's Bayzat config
        // 2. Get pending records for this batch
        // 3. Process in chunks of 20 (Bayzat limit)
        // 4. For each chunk:
        //    - Transform records to Bayzat format
        //    - Send API request
        //    - Handle response/errors
        //    - Update record sync status
        // 5. Respect 1-second rate limit between calls
        // 6. Update batch status
    }

    private function transformToByazatFormat(AttendanceImportRecord $record): array
    {
        // Transform to Bayzat API format:
        // {
        //   "records": [
        //     {
        //       "empId": "123",
        //       "type": "checkIn",
        //       "time": "2023-04-30 09:00:00"
        //     }
        //   ]
        // }
    }

    private function sendToBayzat(BayzatConfig $config, array $records): array
    {
        // Make HTTP request to Bayzat API
        // Handle rate limiting, timeouts, errors
        // Return response with success/failure status
    }
}
```

### 3. Jobs

#### ProcessBayzatSync Job
```php
// app/Jobs/ProcessBayzatSync.php
class ProcessBayzatSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public BayzatSyncBatch $batch) {}

    public function handle(BayzatSyncService $syncService): void
    {
        $syncService->syncBatch($this->batch);
    }

    public function failed(Throwable $exception): void
    {
        // Mark batch as failed
        // Log error
        // Optionally retry later
    }
}
```

#### RetryFailedBayzatSync Job
```php
// app/Jobs/RetryFailedBayzatSync.php
class RetryFailedBayzatSync implements ShouldQueue
{
    // Handles retry logic for failed sync records
    // Exponential backoff: 5min, 30min, 2hr, 8hr, 24hr
    // Max 5 retry attempts
}
```

### 4. Controllers

#### AttendanceController
```php
// app/Http/Controllers/AttendanceController.php
class AttendanceController extends Controller
{
    public function index()
    {
        // Show attendance dashboard
        // List imports with sync status
        // Company-wise sync statistics
    }

    public function import()
    {
        // Show import form
        // Process CSV upload
        // Redirect to import details
    }

    public function syncStatus(AttendanceImport $import)
    {
        // Show detailed sync status for import
        // Per-company sync progress
        // Error details and retry options
    }

    public function retrySyncBatch(BayzatSyncBatch $batch)
    {
        // Manually retry failed sync batch
        // Reset failed records to pending
        // Dispatch new sync job
    }
}
```

#### BayzatConfigController
```php
// app/Http/Controllers/BayzatConfigController.php
class BayzatConfigController extends Controller
{
    public function show(Company $company)
    {
        // Show Bayzat configuration for company
        // API key management (masked display)
        // Sync settings and history
    }

    public function store(Request $request, Company $company)
    {
        // Save/update Bayzat configuration
        // Validate API key by testing connection
        // Encrypt and store credentials
    }

    public function testConnection(Company $company)
    {
        // Test Bayzat API connection
        // Return success/failure with details
    }
}
```

### 5. Frontend Components

#### Attendance Dashboard
- Import history table with sync status indicators
- Company-wise sync statistics
- Manual sync triggers
- Error logs and retry options

#### Bayzat Configuration Page
- API key input (with show/hide toggle)
- Connection test button
- Sync frequency settings
- Last sync timestamp and status

#### Import Details Page
- Record-by-record import validation results
- Sync progress per company
- Failed record details with retry options

## API Integration Specifications

### Bayzat API Format
```json
{
  "records": [
    {
      "empId": "123",
      "type": "checkIn",
      "time": "2023-04-30 09:00:00"
    },
    {
      "empId": "123", 
      "type": "checkOut",
      "time": "2023-04-30 17:00:00"
    }
  ]
}
```

### Rate Limiting
- Maximum 1 request per second
- Maximum 20 records per request
- Implement queue delays to respect limits

### Error Handling
- HTTP 200: Success
- HTTP 400: Data structure error
- HTTP 401: Unauthorized (invalid API key)
- HTTP 429: Rate limit exceeded
- HTTP 500: Internal server error

## Configuration Management

### Environment Variables
```env
BAYZAT_DEFAULT_API_URL=https://integration.bayzat.com/attendance
BAYZAT_RATE_LIMIT_DELAY=1 # seconds between requests
BAYZAT_MAX_RECORDS_PER_REQUEST=20
BAYZAT_MAX_RETRY_ATTEMPTS=5
```

### Queue Configuration
```php
// config/queue.php
'connections' => [
    'bayzat_sync' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'bayzat-sync',
        'retry_after' => 300, // 5 minutes
        'block_for' => 0,
    ],
],
```

## Security Considerations

1. **API Key Encryption**: Use Laravel's built-in encryption for storing API keys
2. **Rate Limiting**: Implement application-level rate limiting to prevent API abuse
3. **Input Validation**: Validate all CSV data before processing
4. **Access Control**: Ensure only authorized users can manage Bayzat configurations
5. **Audit Logging**: Log all sync operations and configuration changes

## Monitoring and Alerting

### Metrics to Track
- Import success/failure rates
- Sync success/failure rates per company
- API response times and error rates
- Queue processing times
- Failed retry attempts

### Dashboard Widgets
- Recent imports status
- Company sync health indicators
- Failed records requiring attention
- API usage statistics

### Alerts
- Failed imports requiring intervention
- API key expiration or authentication failures
- High failure rates for specific companies
- Queue processing delays

## Testing Strategy

### Unit Tests
- CSV parsing and validation logic
- Bayzat API format transformation
- Rate limiting implementation
- Retry logic with exponential backoff

### Integration Tests
- End-to-end import and sync flow
- API error handling scenarios
- Queue job processing
- Database transaction integrity

### Manual Testing
- CSV file format variations
- API connectivity issues
- Large file processing
- Concurrent sync operations

## Deployment Considerations

### Queue Workers
```bash
# Start queue workers for Bayzat sync
php artisan queue:work --queue=bayzat-sync --tries=3 --timeout=300
```

### Scheduled Tasks
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Retry failed syncs every hour
    $schedule->command('bayzat:retry-failed')->hourly();
    
    // Clean up old import files monthly
    $schedule->command('attendance:cleanup-imports')->monthly();
}
```

### Storage Requirements
- Temporary CSV file storage
- Import history retention policy
- Log file rotation and cleanup

## Future Enhancements

1. **Real-time Sync Option**: WebSocket-based real-time sync for critical operations
2. **Bulk Operations**: Mass retry, mass delete, bulk configuration updates
3. **Advanced Filtering**: Filter sync records by date range, employee, or department
4. **Reporting**: Detailed analytics and reports on attendance patterns
5. **API Webhooks**: Receive notifications from Bayzat about sync status
6. **Multi-language Support**: Support for Arabic interface in Bayzat integration

## Error Recovery Procedures

### Common Issues and Solutions

1. **Invalid Employee IDs**
   - Stop import and show validation errors
   - Provide employee ID mapping interface
   - Allow manual correction and re-import

2. **Unmapped Departments**
   - Show list of unmapped departments
   - Provide quick mapping interface
   - Re-process import after mapping

3. **API Authentication Failures**
   - Test and update API keys
   - Notify company administrators
   - Pause sync until resolved

4. **Rate Limit Exceeded**
   - Automatic backoff and retry
   - Adjust queue processing speed
   - Monitor and alert on repeated occurrences

## Performance Optimization

### Database Optimization
- Proper indexing on sync status and retry fields
- Partition large tables by date
- Regular cleanup of old records

### Queue Optimization
- Separate queues for different priorities
- Optimize batch sizes based on performance testing
- Monitor queue depth and processing times

### Caching Strategy
- Cache company configurations
- Cache employee mappings
- Use Redis for session-based progress tracking

---

This comprehensive system provides a robust, scalable solution for importing attendance data and syncing it with Bayzat's API while maintaining data integrity, proper error handling, and monitoring capabilities.
