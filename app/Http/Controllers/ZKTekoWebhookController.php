<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ZKTekoDevice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ZKTekoWebhookController extends Controller
{
    /**
     * Handle incoming webhook from ZKTeko fingerprint device
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        // Log the incoming webhook data with ZKTeko keyword
        Log::channel('zkteko')->info('Webhook received', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
            'timestamp' => now()->toISOString(),
        ]);

        try {
            $payload = $request->all();
            $deviceData = $this->parseDeviceData($payload);
            
            // Get or create device
            $device = $this->getOrCreateDevice($deviceData);
            
            // Check if this is a heartbeat or attendance record
            if ($this->isHeartbeat($payload)) {
                $device->updateHeartbeat($deviceData);
                Log::channel('zkteko')->info('Device heartbeat updated', [
                    'device_id' => $device->id,
                    'serial_number' => $device->serial_number,
                ]);
            } else {
                // Extract attendance data from the webhook payload
                $attendanceData = $this->parseAttendanceData($payload);
                
                if ($attendanceData) {
                    $device->updateAttendanceRecord($attendanceData);
                    
                    // Log the parsed attendance data
                    Log::channel('zkteko')->info('Attendance record processed', [
                        'device_id' => $device->id,
                        'serial_number' => $device->serial_number,
                        'employee_id' => $attendanceData['employee_id'] ?? null,
                        'timestamp' => $attendanceData['timestamp'] ?? null,
                        'type' => $attendanceData['type'] ?? null,
                    ]);

                    // TODO: Process the attendance data
                    // This could involve:
                    // - Creating attendance records
                    // - Updating employee status
                    // - Triggering notifications
                    // - Syncing with other systems
                }
            }

            // Return success response
            return response('OK', 200);

        } catch (\Exception $e) {
            // Log any errors that occur during processing
            Log::channel('zkteko')->error('Error processing webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
            ]);

            // Return error response but don't expose internal details
            return response('Internal Server Error', 500);
        }
    }

    /**
     * Parse attendance data from webhook payload
     *
     * @param array $payload
     * @return array|null
     */
    private function parseAttendanceData(array $payload): ?array
    {
        // This is a placeholder implementation
        // You'll need to adjust this based on the actual ZKTeko webhook format
        
        // Common ZKTeko webhook fields (adjust as needed):
        $data = [
            'employee_id' => $payload['employee_id'] ?? $payload['user_id'] ?? $payload['uid'] ?? null,
            'timestamp' => $payload['timestamp'] ?? $payload['time'] ?? $payload['datetime'] ?? null,
            'type' => $payload['type'] ?? $payload['event'] ?? $payload['action'] ?? null,
            'device_id' => $payload['device_id'] ?? $payload['device'] ?? $payload['machine'] ?? null,
            'location' => $payload['location'] ?? null,
            'temperature' => $payload['temperature'] ?? null, // If device supports it
        ];

        // Validate that we have at least some basic data
        if (empty($data['employee_id']) || empty($data['timestamp'])) {
            Log::warning('ZKTeko: Invalid webhook payload - missing required fields', [
                'payload' => $payload,
                'parsed_data' => $data,
            ]);
            return null;
        }

        return $data;
    }

    /**
     * Parse device data from webhook payload
     *
     * @param array $payload
     * @return array
     */
    private function parseDeviceData(array $payload): array
    {
        return [
            'serial_number' => $payload['serial_number'] ?? $payload['device_sn'] ?? $payload['sn'] ?? null,
            'device_name' => $payload['device_name'] ?? $payload['name'] ?? null,
            'model' => $payload['model'] ?? $payload['device_model'] ?? null,
            'firmware_version' => $payload['firmware_version'] ?? $payload['fw_version'] ?? null,
            'ip_address' => $payload['ip_address'] ?? $payload['ip'] ?? null,
            'mac_address' => $payload['mac_address'] ?? $payload['mac'] ?? null,
            'device_info' => $payload,
        ];
    }

    /**
     * Get or create device based on serial number
     *
     * @param array $deviceData
     * @return ZKTekoDevice
     */
    private function getOrCreateDevice(array $deviceData): ZKTekoDevice
    {
        if (empty($deviceData['serial_number'])) {
            throw new \Exception('Device serial number is required');
        }

        return ZKTekoDevice::firstOrCreate(
            ['serial_number' => $deviceData['serial_number']],
            array_merge($deviceData, [
                'device_name' => $deviceData['device_name'] ?? 'Unknown Device',
                'status' => 'unknown',
            ])
        );
    }

    /**
     * Check if the payload is a heartbeat
     *
     * @param array $payload
     * @return bool
     */
    private function isHeartbeat(array $payload): bool
    {
        // Check for common heartbeat indicators
        $heartbeatIndicators = [
            'heartbeat',
            'ping',
            'status',
            'keepalive',
            'health_check',
        ];

        $type = strtolower($payload['type'] ?? $payload['event'] ?? $payload['action'] ?? '');
        
        return in_array($type, $heartbeatIndicators) || 
               isset($payload['heartbeat']) || 
               isset($payload['ping']) ||
               (empty($payload['employee_id']) && empty($payload['user_id']) && empty($payload['uid']));
    }

    /**
     * Test endpoint to verify webhook is working
     *
     * @return JsonResponse
     */
    public function test(): JsonResponse
    {
        Log::channel('zkteko')->info('Test endpoint called');
        
        return response()->json([
            'status' => 'success',
            'message' => 'ZKTeko webhook endpoint is working',
            'timestamp' => now()->toISOString(),
        ]);
    }
}
