# ZKTeko Fingerprint Device Webhook Integration

This document describes how to integrate your ZKTeko fingerprint device with the IT Portal system using webhooks.

## Overview

The system now includes a webhook endpoint that can receive attendance data from ZKTeko fingerprint devices. When an employee logs in or out using the fingerprint device, it sends data to our webhook endpoint, which then processes and logs the attendance information.

## Webhook Endpoints

### Main Webhook Endpoint
- **URL**: `/api/webhook/fp`
- **Method**: `POST`
- **Authentication**: None required (public endpoint)

### Test Endpoint
- **URL**: `/api/webhook/fp/test`
- **Method**: `GET`
- **Purpose**: Verify the webhook endpoint is working

## Expected Webhook Payload

The webhook expects the following data structure (adjust based on your ZKTeko device configuration):

```json
{
    "employee_id": "12345",
    "timestamp": "2025-01-20T10:30:00Z",
    "type": "in", // or "out"
    "device_id": "DEVICE001",
    "location": "Main Office",
    "temperature": "36.5" // optional, if device supports it
}
```

### Field Descriptions

- **employee_id**: Unique identifier for the employee (required)
- **timestamp**: When the fingerprint scan occurred (required)
- **type**: Type of attendance event - "in" for check-in, "out" for check-out (required)
- **device_id**: Identifier for the fingerprint device (required)
- **location**: Physical location of the device (optional)
- **temperature**: Body temperature reading if device supports it (optional)

## Configuration

### 1. ZKTeko Device Setup

Configure your ZKTeko device to send webhooks to:
```
https://yourdomain.com/api/webhook/fp
```

### 2. Webhook Settings

- **HTTP Method**: POST
- **Content Type**: application/json
- **Retry on Failure**: Enable (recommended)
- **Timeout**: 30 seconds (recommended)

### 3. Data Mapping

Ensure your ZKTeko device sends data in the expected format. You may need to configure:
- Employee ID mapping
- Timestamp format
- Event type mapping (in/out)
- Device identification

## Logging

All webhook activity is logged to a dedicated log file:
- **File**: `storage/logs/zkteko.log`
- **Rotation**: Daily (keeps 30 days)
- **Level**: Debug and above

### Log Entries Include

- Incoming webhook data
- Parsed attendance information
- Processing errors
- Test endpoint calls

## Testing

### 1. Test the Endpoint

```bash
curl -X GET https://yourdomain.com/api/webhook/fp/test
```

Expected response:
```json
{
    "status": "success",
    "message": "ZKTeko webhook endpoint is working",
    "timestamp": "2025-01-20T10:30:00Z"
}
```

### 2. Test with Sample Data

```bash
curl -X POST https://yourdomain.com/api/webhook/fp \
  -H "Content-Type: application/json" \
  -d '{
    "employee_id": "12345",
    "timestamp": "2025-01-20T10:30:00Z",
    "type": "in",
    "device_id": "DEVICE001"
  }'
```

Expected response: `OK`

## Monitoring

### 1. Check Logs

Monitor the ZKTeko logs for activity:
```bash
tail -f storage/logs/zkteko.log
```

### 2. Health Check

Use the health endpoint to verify system status:
```bash
curl https://yourdomain.com/api/health
```

## Troubleshooting

### Common Issues

1. **404 Error**: Ensure the API routes are properly loaded
2. **500 Error**: Check the Laravel logs for detailed error information
3. **No Logs**: Verify the ZKTeko log channel is configured correctly

### Debug Steps

1. Check if the webhook endpoint is accessible
2. Verify the payload format matches expectations
3. Review the ZKTeko logs for detailed information
4. Check Laravel application logs for any errors

## Security Considerations

- The webhook endpoint is public (no authentication required)
- Consider implementing IP whitelisting for production use
- Monitor webhook activity for suspicious patterns
- Implement rate limiting if needed

## Future Enhancements

The current implementation includes:
- Basic webhook handling
- Comprehensive logging
- Error handling
- Data validation

Planned enhancements:
- Attendance record creation
- Employee status updates
- Notification system integration
- Data synchronization with other systems
- Webhook authentication
- Rate limiting

## Support

For issues or questions regarding the ZKTeko webhook integration:
1. Check the logs first
2. Review this documentation
3. Contact the development team with specific error details
