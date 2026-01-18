<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ZkTecoController extends Controller
{
    /**
     * Handle ZKTeco device data requests
     * 
     * @param Request $request
     * @return Response
     */
    public function cdata(Request $request): Response
    {
        // Log immediately to ensure we capture any request
        try {
            // Log to both single and daily channels for redundancy
            Log::channel('single')->info('=== ZKTeco CONTROLLER REACHED ===');
            Log::channel('single')->info('Controller Time: ' . now()->toDateTimeString());
            Log::channel('single')->info('Controller Method: ' . $request->method());
            Log::channel('single')->info('Controller URL: ' . $request->fullUrl());
            Log::channel('single')->info('Controller IP: ' . $request->ip());
            
            // Simple log first to confirm request received
            Log::channel('daily')->info('=== ZKTeco REQUEST RECEIVED ===');
            Log::channel('daily')->info('Time: ' . now()->toDateTimeString());
            Log::channel('daily')->info('Method: ' . $request->method());
            Log::channel('daily')->info('URL: ' . $request->fullUrl());
            Log::channel('daily')->info('IP: ' . $request->ip());
            
            // Get raw body content
            $rawBody = $request->getContent();
            
            // Prepare detailed log data
            $logData = [
                'method' => $request->method(),
                'full_url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'query_params' => $request->query(),
                'headers' => $request->headers->all(),
                'raw_body' => $rawBody,
                'body_length' => strlen($rawBody),
            ];
            
            // Log detailed request to both channels
            Log::channel('single')->info('[ZKTeco] /iclock/cdata - Full Details', $logData);
            Log::channel('daily')->info('[ZKTeco] /iclock/cdata - Full Details', $logData);
            Log::channel('single')->info('=== ZKTeco REQUEST END ===');
            Log::channel('daily')->info('=== ZKTeco REQUEST END ===');
            
        } catch (\Exception $e) {
            // Log any errors to both channels
            Log::channel('single')->error('[ZKTeco] Error processing request: ' . $e->getMessage());
            Log::channel('single')->error('Stack trace: ' . $e->getTraceAsString());
            Log::channel('daily')->error('[ZKTeco] Error processing request: ' . $e->getMessage());
            Log::channel('daily')->error('Stack trace: ' . $e->getTraceAsString());
        }
        
        // Return OK response with text/plain content type
        return response('OK', 200)
            ->header('Content-Type', 'text/plain');
    }
}
