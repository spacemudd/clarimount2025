# Database Polling Print System

## Overview

This system uses database polling instead of real-time WebSocket connections (like Pusher) to manage print jobs. The print station automatically checks the database every 2 seconds for new print jobs and processes them in sequence.

## How It Works

### 1. Print Station Polling
- The `/print-available` page polls the database every 2 seconds
- It fetches pending print jobs using the API endpoint `/api/print-jobs/pending`
- Jobs are automatically sorted by priority (urgent > high > normal > low) and creation time
- No external dependencies required (no Pusher account needed)

### 2. Auto-Print Feature
- When enabled, the system automatically processes print jobs in sequence
- Jobs are marked as "processing" when picked up
- After sending to the printer, jobs are automatically marked as "completed" after 3 seconds
- If printing fails, jobs are marked as "failed"

### 3. Manual Control
- Print station operators can manually start/stop the polling
- Individual jobs can be processed manually when auto-print is disabled
- Jobs can be marked as completed, failed, or cancelled manually

## API Endpoints

### GET /api/print-jobs/pending
Returns all pending print jobs with asset, company, and user information.

### GET /api/print-jobs/history?limit=10
Returns recent completed/failed/cancelled print jobs.

### GET /api/print-jobs/statistics
Returns statistics for the dashboard (pending, processing, completed today, failed today).

### PATCH /api/print-jobs/{id}/status
Updates the status of a print job.
```json
{
    "status": "processing|completed|failed|cancelled",
    "error_message": "optional error message",
    "print_station_id": "optional station identifier"
}
```

### DELETE /api/print-jobs/{id}
Cancels a print job (removes it from the queue).

## Print Station Features

### Real-time Status
- Green indicator: Polling is active
- Red indicator: Polling is stopped
- Blue indicator: Auto-print is enabled

### Statistics Dashboard
- Pending jobs count
- Processing jobs count  
- Completed jobs today
- Failed jobs today

### Auto-Print Settings
- Enable/disable automatic printing
- Choose default printer or select specific printer
- JSPrintManager integration for actual printing

### Job Queues
- **Pending Queue**: Jobs waiting to be processed (sorted by priority)
- **Processing Queue**: Jobs currently being printed
- **Recent Activity**: History of completed/failed jobs

## Usage Instructions

### For Print Station Operators

1. **Open Print Station**
   - Navigate to `/print-available` on the PC with the printer
   - The system will automatically start polling for new jobs

2. **Enable Auto-Print**
   - Click "Enable" in the Auto-Print Settings section
   - Choose to use default printer or select a specific printer
   - Jobs will now be processed automatically in sequence

3. **Manual Operation**
   - If auto-print is disabled, use the play button on individual jobs
   - Mark jobs as completed/failed using the action buttons
   - Use the stop/start button to control polling

### For Mobile Users

1. **Add Assets**
   - Scan barcodes or add assets normally
   - Use the "Send to Print Machine" button instead of direct printing

2. **Monitor Status**
   - Jobs are queued with priority levels
   - Status updates automatically as jobs are processed

## Advantages Over Pusher

1. **No External Dependencies**: No need for Pusher account or API keys
2. **Simpler Setup**: Just works with the existing database
3. **More Reliable**: No network connectivity issues with WebSocket connections
4. **Cost-Effective**: No monthly fees for real-time services
5. **Easier Debugging**: All data is in the database, easy to troubleshoot

## Technical Details

### Database Polling
- Polling interval: 2 seconds (configurable in the code)
- Uses standard HTTP requests to API endpoints
- Efficient queries with proper indexing on status and company_id

### Auto-Print Logic
- Checks for new jobs every 1 second when auto-print is enabled
- Processes highest priority job first
- Prevents multiple jobs from being processed simultaneously
- Automatic completion after 3 seconds (configurable)

### Job Priority System
- **Urgent**: Red badge, highest priority
- **High**: Secondary badge
- **Normal**: Outline badge (default)
- **Low**: Secondary badge, lowest priority

## Configuration

### Polling Intervals
To change polling intervals, modify these values in `resources/js/pages/PrintStation/Index.vue`:

```javascript
// Main polling interval (job fetching)
pollingInterval = window.setInterval(refreshJobs, 2000); // 2 seconds

// Auto-print check interval
autoPrintInterval = window.setInterval(async () => {
    // Check for new jobs to auto-print
}, 1000); // 1 second
```

### Auto-Complete Timeout
To change how long the system waits before auto-completing print jobs:

```javascript
// Auto-complete after X seconds if auto-print is enabled
setTimeout(() => {
    completePrintJob(job);
}, 3000); // 3 seconds
```

## Troubleshooting

### Jobs Not Appearing
1. Check if polling is active (green indicator)
2. Verify print jobs exist in database: `SELECT * FROM print_jobs WHERE status = 'pending'`
3. Check browser console for API errors

### Auto-Print Not Working
1. Ensure JSPrintManager is installed and running
2. Check printer connection and status
3. Verify auto-print is enabled (blue indicator should be visible)

### Performance Issues
1. Consider increasing polling interval if system is slow
2. Add database indexes on frequently queried columns
3. Limit the number of jobs returned in API responses

## Migration from Pusher

The system was converted from Pusher to database polling by:

1. Removing all Echo/Pusher imports and configurations
2. Replacing real-time listeners with polling intervals
3. Removing broadcast events from the backend
4. Updating the print station UI to show polling status instead of connection status

All existing functionality remains the same, just using database polling instead of WebSocket connections. 