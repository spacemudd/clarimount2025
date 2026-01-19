# ZKTeco /iclock/cdata Endpoint Testing

## Overview

This document describes how to test the ZKTeco `/iclock/cdata` endpoint that receives attendance log data from ZKTeco fingerprint devices.

## Endpoint Details

- **URL**: `/iclock/cdata`
- **Method**: `POST`
- **Authentication**: None required (public endpoint)
- **Content-Type**: `text/plain` (raw body)

## Query Parameters

- `SN` (required): Device serial number (e.g., `CQUG243160247`)
- `table` (required): Must be `ATTLOG` for attendance log processing
- `Stamp` (optional): Timestamp from device (e.g., `9999`)

## Request Body Format

The request body contains TAB-separated lines. Each line represents one attendance record.

### Line Format

Each line is separated by TAB characters (`\t`) with the following structure:

```
device_pin\tpunch_time\t[other_fields]\tverify_mode\t[more_fields]
```

### Example Request

**URL**: `POST /iclock/cdata?SN=CQUG243160247&table=ATTLOG&Stamp=9999`

**Body** (single line example):
```
18	2026-01-18 15:31:30	255	1	0	0	0	255	0	0	1278
```

**Body** (multiple lines example):
```
18	2026-01-18 15:31:30	255	1	0	0	0	255	0	0	1278
18	2026-01-18 16:45:20	255	1	0	0	0	255	0	0	1278
19	2026-01-18 09:00:15	255	0	0	0	0	255	0	0	1279
```

### Field Mapping

- **Column 0**: `device_pin` - Employee PIN in the device (e.g., `18`)
- **Column 1**: `punch_time` - Timestamp in format `YYYY-MM-DD HH:MM:SS` (e.g., `2026-01-18 15:31:30`)
- **Column 2**: Usually `255` (status code)
- **Column 3**: `verify_mode` - Verification mode (e.g., `1` = fingerprint, `0` = password, etc.)
- **Columns 4+**: Additional device-specific fields

## Expected Response

- **Status Code**: `200 OK`
- **Content-Type**: `text/plain`
- **Body**: `OK`

## Testing with cURL

### Single Record

```bash
curl -X POST "http://your-domain.com/iclock/cdata?SN=CQUG243160247&table=ATTLOG&Stamp=9999" \
  -H "Content-Type: text/plain" \
  -d "18	2026-01-18 15:31:30	255	1	0	0	0	255	0	0	1278"
```

### Multiple Records

```bash
curl -X POST "http://your-domain.com/iclock/cdata?SN=CQUG243160247&table=ATTLOG&Stamp=9999" \
  -H "Content-Type: text/plain" \
  -d "18	2026-01-18 15:31:30	255	1	0	0	0	255	0	0	1278
18	2026-01-18 16:45:20	255	1	0	0	0	255	0	0	1278
19	2026-01-18 09:00:15	255	0	0	0	0	255	0	0	1279"
```

## Testing with Postman

1. **Method**: `POST`
2. **URL**: `http://your-domain.com/iclock/cdata?SN=CQUG243160247&table=ATTLOG&Stamp=9999`
3. **Headers**:
   - `Content-Type: text/plain`
4. **Body** (raw, text):
   ```
   18	2026-01-18 15:31:30	255	1	0	0	0	255	0	0	1278
   ```

## Data Storage

When a request with `table=ATTLOG` is received, the system:

1. **Creates/Updates Device**: Records device in `zk_devices` table with serial number
2. **Stores Raw Data**: Each line is stored in `zk_attendance_raw` table
3. **Creates Daily Summary**: Updates `zk_daily_attendance` table with:
   - First punch of the day
   - Last punch of the day
   - Total punch count
   - Verification modes

## Timezone Handling

- **Input**: Timestamps are assumed to be in `Asia/Riyadh` timezone
- **Storage**: All timestamps are converted to `UTC` before storage
- **Daily Summary**: `att_date` is calculated from the original timezone

## Duplicate Prevention

The system prevents duplicate records using a unique constraint on:
- `device_id`
- `device_pin`
- `punch_time`

If a duplicate is detected, it is silently skipped (no error).

## Error Handling

- **Invalid Lines**: Lines that cannot be parsed are logged as warnings but do not stop processing
- **Missing SN**: If `SN` parameter is missing, a warning is logged but request continues
- **Database Errors**: Errors are logged but do not fail the entire request

## Verification

After sending a test request, you can verify the data was stored by:

1. Checking `zk_devices` table for the device
2. Checking `zk_attendance_raw` table for raw records
3. Checking `zk_daily_attendance` table for daily summaries
4. Checking Laravel logs (`storage/logs/laravel.log` or daily logs)

## Notes

- The endpoint accepts both `GET` and `POST` methods (as configured in routes)
- Empty lines in the body are automatically filtered
- The system maintains backward compatibility with existing logging functionality
