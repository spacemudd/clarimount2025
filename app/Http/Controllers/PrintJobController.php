<?php

namespace App\Http\Controllers;

use App\Events\PrintJobCreated;
use App\Models\Asset;
use App\Models\PrintJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PrintJobController extends Controller
{
    /**
     * Display the print station page.
     */
    public function printStation(): Response
    {
        return Inertia::render('PrintStation/Index', [
            'pusherConfig' => [
                'key' => config('broadcasting.connections.pusher.key'),
                'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                'encrypted' => config('broadcasting.connections.pusher.options.encrypted'),
            ],
        ]);
    }

    /**
     * Create a new print job for an asset.
     */
    public function create(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'priority' => 'sometimes|in:low,normal,high,urgent',
            'printer_name' => 'nullable|string|max:255',
        ]);

        // Get the asset and verify user has access
        $asset = Asset::with(['company', 'category', 'location'])->findOrFail($validated['asset_id']);
        
        // Check if user has access to this asset (asset must belong to one of their companies)
        $ownedCompanyIds = $user->ownedCompanies()->pluck('id');
        if (!$ownedCompanyIds->contains($asset->company_id)) {
            return response()->json(['error' => 'Unauthorized access to this asset.'], 403);
        }

        // Prepare print data
        $printData = [
            'asset_tag' => $asset->asset_tag,
            'serial_number' => $asset->serial_number,
            'model_name' => $asset->model_name,
            'model_number' => $asset->model_number,
            'category_name' => $asset->category->name ?? null,
            'location_name' => $asset->location->name ?? null,
            'company_name' => $asset->company->name_en,
            'manufacturer' => $asset->manufacturer,
            'condition' => $asset->condition,
            'status' => $asset->status,
        ];

        // Create the print job
        $printJob = PrintJob::create([
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'company_id' => $asset->company_id,
            'priority' => $validated['priority'] ?? 'normal',
            'printer_name' => $validated['printer_name'],
            'print_data' => $printData,
        ]);

        // Load relationships for the event
        $printJob->load(['asset.category', 'company', 'user']);

        // Broadcast the print job creation
        broadcast(new PrintJobCreated($printJob))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Print job created successfully.',
            'print_job' => [
                'id' => $printJob->id,
                'job_id' => $printJob->job_id,
                'status' => $printJob->status,
                'priority' => $printJob->priority,
                'created_at' => $printJob->created_at,
            ],
        ]);
    }

    /**
     * Get pending print jobs for the print station.
     */
    public function pending(): JsonResponse
    {
        $printJobs = PrintJob::with(['asset.category', 'company', 'user'])
            ->pending()
            ->orderedByPriority()
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'job_id' => $job->job_id,
                    'asset' => [
                        'id' => $job->asset->id,
                        'asset_tag' => $job->asset->asset_tag,
                        'serial_number' => $job->asset->serial_number,
                        'model_name' => $job->asset->model_name,
                        'category' => $job->asset->category->name ?? null,
                    ],
                    'company' => [
                        'id' => $job->company->id,
                        'name' => $job->company->name_en,
                    ],
                    'user' => [
                        'id' => $job->user->id,
                        'name' => $job->user->name,
                    ],
                    'priority' => $job->priority,
                    'status' => $job->status,
                    'print_data' => $job->print_data,
                    'requested_at' => $job->requested_at,
                    'created_at' => $job->created_at,
                ];
            });

        return response()->json($printJobs);
    }

    /**
     * Update print job status.
     */
    public function updateStatus(Request $request, PrintJob $printJob): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:processing,completed,failed,cancelled',
            'error_message' => 'nullable|string',
            'print_station_id' => 'nullable|string|max:255',
        ]);

        switch ($validated['status']) {
            case 'processing':
                $printJob->markAsProcessing($validated['print_station_id'] ?? null);
                break;
            case 'completed':
                $printJob->markAsCompleted();
                break;
            case 'failed':
                $printJob->markAsFailed($validated['error_message'] ?? 'Print job failed');
                break;
            case 'cancelled':
                $printJob->markAsCancelled();
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Print job status updated successfully.',
            'print_job' => [
                'id' => $printJob->id,
                'job_id' => $printJob->job_id,
                'status' => $printJob->status,
                'updated_at' => $printJob->updated_at,
            ],
        ]);
    }

    /**
     * Get print job history for a user.
     */
    public function history(Request $request): JsonResponse
    {
        $user = Auth::user();
        $ownedCompanyIds = $user->ownedCompanies()->pluck('id');

        $printJobs = PrintJob::with(['asset', 'company'])
            ->whereIn('company_id', $ownedCompanyIds)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($job) {
                return [
                    'id' => $job->id,
                    'job_id' => $job->job_id,
                    'asset_tag' => $job->asset->asset_tag,
                    'company_name' => $job->company->name_en,
                    'status' => $job->status,
                    'formatted_status' => $job->formatted_status,
                    'status_color' => $job->status_color,
                    'priority' => $job->priority,
                    'requested_at' => $job->requested_at,
                    'completed_at' => $job->completed_at,
                    'duration' => $job->duration,
                ];
            });

        return response()->json($printJobs);
    }

    /**
     * Cancel a print job.
     */
    public function cancel(PrintJob $printJob): JsonResponse
    {
        $user = Auth::user();
        
        // Check if user can cancel this print job
        if ($printJob->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized to cancel this print job.'], 403);
        }

        // Can only cancel pending or processing jobs
        if (!in_array($printJob->status, ['pending', 'processing'])) {
            return response()->json(['error' => 'Cannot cancel a print job that is already completed or failed.'], 400);
        }

        $printJob->markAsCancelled();

        return response()->json([
            'success' => true,
            'message' => 'Print job cancelled successfully.',
        ]);
    }

    /**
     * Get print statistics for dashboard.
     */
    public function statistics(): JsonResponse
    {
        $user = Auth::user();
        $ownedCompanyIds = $user->ownedCompanies()->pluck('id');

        $stats = [
            'pending' => PrintJob::whereIn('company_id', $ownedCompanyIds)->pending()->count(),
            'processing' => PrintJob::whereIn('company_id', $ownedCompanyIds)->processing()->count(),
            'completed_today' => PrintJob::whereIn('company_id', $ownedCompanyIds)
                ->completed()
                ->whereDate('completed_at', today())
                ->count(),
            'failed_today' => PrintJob::whereIn('company_id', $ownedCompanyIds)
                ->failed()
                ->whereDate('created_at', today())
                ->count(),
        ];

        return response()->json($stats);
    }
}
