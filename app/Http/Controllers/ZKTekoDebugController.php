<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ZKTekoDevice;
use App\Models\ZKTekoHeartbeat;
use App\Models\ZKTekoAttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ZKTekoDebugController extends Controller
{
    /**
     * Display the ZKTeko debug dashboard
     */
    public function index(): Response
    {
        $devices = ZKTekoDevice::with(['heartbeats' => function ($query) {
            $query->latest()->limit(10);
        }])
        ->orderBy('last_heartbeat', 'desc')
        ->get()
        ->map(function ($device) {
            return [
                'id' => $device->id,
                'serial_number' => $device->serial_number,
                'device_name' => $device->device_name,
                'display_name' => $device->display_name,
                'model' => $device->model,
                'firmware_version' => $device->firmware_version,
                'ip_address' => $device->ip_address,
                'mac_address' => $device->mac_address,
                'last_heartbeat' => $device->last_heartbeat?->toISOString(),
                'last_attendance_record' => $device->last_attendance_record?->toISOString(),
                'total_heartbeats' => $device->total_heartbeats,
                'total_attendance_records' => $device->total_attendance_records,
                'is_online' => $device->is_online,
                'status' => $device->status,
                'status_color' => $device->status_color,
                'time_since_last_heartbeat' => $device->time_since_last_heartbeat,
                'is_considered_offline' => $device->isConsideredOffline(),
                'last_error' => $device->last_error,
                'created_at' => $device->created_at->toISOString(),
                'updated_at' => $device->updated_at->toISOString(),
                'recent_heartbeats' => $device->heartbeats->map(function ($heartbeat) {
                    return [
                        'id' => $heartbeat->id,
                        'timestamp' => $heartbeat->timestamp->toISOString(),
                        'data' => $heartbeat->data,
                    ];
                }),
            ];
        });

        $stats = [
            'total_devices' => ZKTekoDevice::count(),
            'online_devices' => ZKTekoDevice::where('is_online', true)->count(),
            'offline_devices' => ZKTekoDevice::where('is_online', false)->count(),
            'devices_with_recent_heartbeats' => ZKTekoDevice::withRecentHeartbeats(5)->count(),
            'total_heartbeats_today' => ZKTekoHeartbeat::whereDate('timestamp', today())->count(),
            'total_attendance_records_today' => ZKTekoAttendanceRecord::whereDate('timestamp', today())->count(),
        ];

        return Inertia::render('ZKTekoDebug/Index', [
            'devices' => $devices,
            'stats' => $stats,
        ]);
    }

    /**
     * Get device details with recent activity
     */
    public function show(int $id): JsonResponse
    {
        $device = ZKTekoDevice::with([
            'heartbeats' => function ($query) {
                $query->latest()->limit(50);
            },
            'attendanceRecords' => function ($query) {
                $query->latest()->limit(50);
            }
        ])->findOrFail($id);

        return response()->json([
            'device' => [
                'id' => $device->id,
                'serial_number' => $device->serial_number,
                'device_name' => $device->device_name,
                'display_name' => $device->display_name,
                'model' => $device->model,
                'firmware_version' => $device->firmware_version,
                'ip_address' => $device->ip_address,
                'mac_address' => $device->mac_address,
                'device_info' => $device->device_info,
                'last_heartbeat' => $device->last_heartbeat?->toISOString(),
                'last_attendance_record' => $device->last_attendance_record?->toISOString(),
                'total_heartbeats' => $device->total_heartbeats,
                'total_attendance_records' => $device->total_attendance_records,
                'is_online' => $device->is_online,
                'status' => $device->status,
                'status_color' => $device->status_color,
                'time_since_last_heartbeat' => $device->time_since_last_heartbeat,
                'is_considered_offline' => $device->isConsideredOffline(),
                'last_error' => $device->last_error,
                'created_at' => $device->created_at->toISOString(),
                'updated_at' => $device->updated_at->toISOString(),
            ],
            'recent_heartbeats' => $device->heartbeats->map(function ($heartbeat) {
                return [
                    'id' => $heartbeat->id,
                    'timestamp' => $heartbeat->timestamp->toISOString(),
                    'data' => $heartbeat->data,
                ];
            }),
            'recent_attendance_records' => $device->attendanceRecords->map(function ($record) {
                return [
                    'id' => $record->id,
                    'employee_id' => $record->employee_id,
                    'timestamp' => $record->timestamp->toISOString(),
                    'type' => $record->type,
                    'data' => $record->data,
                ];
            }),
        ]);
    }

    /**
     * Get real-time device status updates
     */
    public function status(): JsonResponse
    {
        $devices = ZKTekoDevice::select([
            'id',
            'serial_number',
            'device_name',
            'last_heartbeat',
            'is_online',
            'status',
            'total_heartbeats',
            'total_attendance_records',
        ])
        ->orderBy('last_heartbeat', 'desc')
        ->get()
        ->map(function ($device) {
            return [
                'id' => $device->id,
                'serial_number' => $device->serial_number,
                'device_name' => $device->device_name,
                'last_heartbeat' => $device->last_heartbeat?->toISOString(),
                'is_online' => $device->is_online,
                'status' => $device->status,
                'time_since_last_heartbeat' => $device->time_since_last_heartbeat,
                'is_considered_offline' => $device->isConsideredOffline(),
                'total_heartbeats' => $device->total_heartbeats,
                'total_attendance_records' => $device->total_attendance_records,
            ];
        });

        $stats = [
            'total_devices' => ZKTekoDevice::count(),
            'online_devices' => ZKTekoDevice::where('is_online', true)->count(),
            'offline_devices' => ZKTekoDevice::where('is_online', false)->count(),
            'devices_with_recent_heartbeats' => ZKTekoDevice::withRecentHeartbeats(5)->count(),
        ];

        return response()->json([
            'devices' => $devices,
            'stats' => $stats,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get device heartbeat history
     */
    public function heartbeats(int $id, Request $request): JsonResponse
    {
        $device = ZKTekoDevice::findOrFail($id);
        
        $query = $device->heartbeats()->latest();
        
        if ($request->has('from')) {
            $query->where('timestamp', '>=', Carbon::parse($request->from));
        }
        
        if ($request->has('to')) {
            $query->where('timestamp', '<=', Carbon::parse($request->to));
        }
        
        $heartbeats = $query->paginate(100);
        
        return response()->json([
            'heartbeats' => $heartbeats->items(),
            'pagination' => [
                'current_page' => $heartbeats->currentPage(),
                'last_page' => $heartbeats->lastPage(),
                'per_page' => $heartbeats->perPage(),
                'total' => $heartbeats->total(),
            ],
        ]);
    }

    /**
     * Get device attendance records
     */
    public function attendanceRecords(int $id, Request $request): JsonResponse
    {
        $device = ZKTekoDevice::findOrFail($id);
        
        $query = $device->attendanceRecords()->latest();
        
        if ($request->has('from')) {
            $query->where('timestamp', '>=', Carbon::parse($request->from));
        }
        
        if ($request->has('to')) {
            $query->where('timestamp', '<=', Carbon::parse($request->to));
        }
        
        $records = $query->paginate(100);
        
        return response()->json([
            'attendance_records' => $records->items(),
            'pagination' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
            ],
        ]);
    }

    /**
     * Mark device as offline manually
     */
    public function markOffline(int $id, Request $request): JsonResponse
    {
        $device = ZKTekoDevice::findOrFail($id);
        $reason = $request->input('reason', 'Manually marked offline');
        
        $device->markOffline($reason);
        
        return response()->json([
            'message' => 'Device marked as offline',
            'device' => [
                'id' => $device->id,
                'serial_number' => $device->serial_number,
                'status' => $device->status,
                'is_online' => $device->is_online,
            ],
        ]);
    }

    /**
     * Clear device error
     */
    public function clearError(int $id): JsonResponse
    {
        $device = ZKTekoDevice::findOrFail($id);
        
        $device->update([
            'last_error' => null,
            'status' => 'online',
        ]);
        
        return response()->json([
            'message' => 'Device error cleared',
            'device' => [
                'id' => $device->id,
                'serial_number' => $device->serial_number,
                'status' => $device->status,
                'last_error' => $device->last_error,
            ],
        ]);
    }
}