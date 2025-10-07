<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BayzatConfigRequest;
use App\Models\BayzatConfig;
use App\Models\Company;
use App\Services\BayzatSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BayzatConfigController extends Controller
{
    public function __construct(
        private BayzatSyncService $syncService
    ) {}

    public function index(): Response
    {
        $companies = Company::with('bayzatConfig')
        ->orderBy('name_en')
        ->get();

        return Inertia::render('BayzatConfig/Index', [
            'companies' => $companies,
        ]);
    }

    public function show(Company $company): Response
    {

        $company->load('bayzatConfig');

        // Get sync statistics for this company
        $syncStats = [
            'total_syncs' => $company->attendanceImportRecords()->count(),
            'successful_syncs' => $company->attendanceImportRecords()
                ->where('bayzat_sync_status', 'synced')->count(),
            'failed_syncs' => $company->attendanceImportRecords()
                ->where('bayzat_sync_status', 'failed')->count(),
            'pending_syncs' => $company->attendanceImportRecords()
                ->where('bayzat_sync_status', 'pending')->count(),
            'last_sync_at' => $company->bayzatConfig?->last_sync_at,
        ];

        return Inertia::render('BayzatConfig/Show', [
            'company' => $company,
            'syncStats' => $syncStats,
        ]);
    }

    public function store(BayzatConfigRequest $request, Company $company)
    {


        try {
            $config = BayzatConfig::updateOrCreate(
                ['company_id' => $company->id],
                $request->validated()
            );

            return back()->with('success', __('messages.bayzat_config_saved'));

        } catch (\Exception $e) {
            return back()->withErrors(['config' => $e->getMessage()]);
        }
    }

    public function update(BayzatConfigRequest $request, Company $company)
    {


        try {
            $config = $company->bayzatConfig;
            if (!$config) {
                return back()->withErrors(['config' => __('messages.bayzat_config_not_found')]);
            }

            $config->update($request->validated());

            return back()->with('success', __('messages.bayzat_config_updated'));

        } catch (\Exception $e) {
            return back()->withErrors(['config' => $e->getMessage()]);
        }
    }

    public function destroy(Company $company)
    {


        try {
            $config = $company->bayzatConfig;
            if ($config) {
                $config->delete();
            }

            return back()->with('success', __('messages.bayzat_config_deleted'));

        } catch (\Exception $e) {
            return back()->withErrors(['config' => $e->getMessage()]);
        }
    }

    public function testConnection(Company $company)
    {


        $config = $company->bayzatConfig;
        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => __('messages.bayzat_config_not_found'),
            ]);
        }

        try {
            $result = $this->syncService->testConnection($config);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function toggle(Company $company)
    {


        $config = $company->bayzatConfig;
        if (!$config) {
            return back()->withErrors(['config' => __('messages.bayzat_config_not_found')]);
        }

        try {
            $config->update(['is_enabled' => !$config->is_enabled]);

            $message = $config->is_enabled 
                ? __('messages.bayzat_config_enabled')
                : __('messages.bayzat_config_disabled');

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->withErrors(['config' => $e->getMessage()]);
        }
    }

    public function updateSettings(Request $request, Company $company)
    {


        $request->validate([
            'sync_frequency' => 'required|in:manual,hourly,daily',
            'settings' => 'nullable|array',
        ]);

        $config = $company->bayzatConfig;
        if (!$config) {
            return back()->withErrors(['config' => __('messages.bayzat_config_not_found')]);
        }

        try {
            $config->update([
                'sync_frequency' => $request->sync_frequency,
                'settings' => $request->settings ?? [],
            ]);

            return back()->with('success', __('messages.bayzat_settings_updated'));

        } catch (\Exception $e) {
            return back()->withErrors(['settings' => $e->getMessage()]);
        }
    }
}
