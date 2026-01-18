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
        // Get raw body content
        $rawBody = $request->getContent();
        
        // Prepare log data
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
        
        // Log the request
        Log::channel('daily')->info('[ZKTeco] /iclock/cdata', $logData);
        
        // Return OK response with text/plain content type
        return response('OK', 200)
            ->header('Content-Type', 'text/plain');
    }
}
