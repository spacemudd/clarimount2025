<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
            // Extract attendance data from the webhook payload
            $attendanceData = $this->parseAttendanceData($request->all());
            
            if ($attendanceData) {
                // Log the parsed attendance data
                Log::channel('zkteko')->info('Parsed attendance data', [
                    'employee_id' => $attendanceData['employee_id'] ?? null,
                    'timestamp' => $attendanceData['timestamp'] ?? null,
                    'type' => $attendanceData['type'] ?? null,
                    'device_id' => $attendanceData['device_id'] ?? null,
                ]);

                // TODO: Process the attendance data
                // This could involve:
                // - Creating attendance records
                // - Updating employee status
                // - Triggering notifications
                // - Syncing with other systems
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
