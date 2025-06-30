<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AssetController extends Controller
{
    /**
     * Display a listing of the assets.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $user = Auth::user();
        $ownedCompanies = $user->ownedCompanies();

        // If user doesn't have any companies, redirect to create one
        if ($ownedCompanies->count() === 0) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage assets.');
        }

        // Get company IDs that the user owns
        $ownedCompanyIds = $ownedCompanies->pluck('id');

        // Only show assets from companies the user owns
        $query = Asset::with(['category', 'location', 'assignments', 'company'])
            ->whereIn('company_id', $ownedCompanyIds);

        // Handle search
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('asset_tag', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('service_tag_number', 'like', "%{$search}%")
                  ->orWhere('finance_tag_number', 'like', "%{$search}%")
                  ->orWhere('model_name', 'like', "%{$search}%")
                  ->orWhere('model_number', 'like', "%{$search}%");
            });
        }

        // Handle category filter
        if ($categoryId = $request->get('category_id')) {
            $query->where('asset_category_id', $categoryId);
        }

        // Handle location filter
        if ($locationId = $request->get('location_id')) {
            $query->where('location_id', $locationId);
        }

        $assets = $query->orderBy('asset_tag')->paginate(20)->withQueryString();

        // Get categories and locations for filters (from all owned companies)
        $categories = AssetCategory::withDepth()
            ->orderBy('_lft')
            ->get();

        $locations = Location::orderBy('name')
            ->get();

        return Inertia::render('Assets/Index', [
            'assets' => $assets,
            'categories' => $categories,
            'locations' => $locations,
            'filters' => $request->only(['search', 'category_id', 'location_id']),
        ]);
    }

    /**
     * Show the form for creating a new asset.
     */
    public function create(): Response|RedirectResponse
    {
        $user = Auth::user();
        $companies = $user->ownedCompanies()->get();

        // If user doesn't have any companies, redirect to create one
        if ($companies->isEmpty()) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage assets.');
        }

        $currentCompany = $user->currentCompany();

        // Get categories and locations for current company (for initial load)
        $categories = $currentCompany ?
            AssetCategory::scoped(['company_id' => $currentCompany->id])
                ->withDepth()
                ->orderBy('_lft')
                ->get() : collect();

        $locations = $currentCompany ?
            Location::where('company_id', $currentCompany->id)
                ->orderBy('name')
                ->get() : collect();

        return Inertia::render('Assets/Create', [
            'companies' => $companies,
            'currentCompany' => $currentCompany,
            'categories' => $categories,
            'locations' => $locations,
        ]);
    }

    /**
     * Store a newly created asset in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $company = $user->currentCompany();

        // If user doesn't have a company, redirect to create one
        if (!$company) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage assets.');
        }

        $validated = $request->validate([
            'asset_template_id' => 'required|exists:asset_templates,id',
            'location_id' => 'required|exists:locations,id',
            'company_id' => 'nullable|exists:companies,id',
            'department_id' => 'nullable|exists:departments,id',
            'assigned_to' => 'nullable|exists:employees,id',
            'serial_number' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:5120', // 5MB max
        ]);

        // Get the asset template and validate access
        $template = \App\Models\AssetTemplate::find($validated['asset_template_id']);
        if (!$template) {
            return back()->withErrors(['asset_template_id' => 'Invalid asset template.']);
        }

        // Check if user has access to this template (either global or belongs to their company)
        if (!$template->is_global && $template->company_id !== $company->id) {
            return back()->withErrors(['asset_template_id' => 'Invalid asset template.']);
        }

        // Ensure template has a valid category
        if (!$template->asset_category_id) {
            return back()->withErrors(['asset_template_id' => 'Selected template does not have a category assigned. Please update the template first.']);
        }

        // Determine the company to use (from form or current company)
        $targetCompanyId = $validated['company_id'] ?? $company->id;

        // Verify user owns the target company
        $targetCompany = $user->ownedCompanies()->find($targetCompanyId);
        if (!$targetCompany) {
            return back()->withErrors(['company_id' => 'Invalid company selection.']);
        }

        // Just verify that the selected location, department, and employee exist (no company restrictions)
        $location = Location::find($validated['location_id']);
        if (!$location) {
            return back()->withErrors(['location_id' => 'Invalid location selection.']);
        }

        // Validate department exists (if provided) - no company restriction
        if (!empty($validated['department_id'])) {
            $department = \App\Models\Department::find($validated['department_id']);
            if (!$department) {
                return back()->withErrors(['department_id' => 'Invalid department selection.']);
            }
        }

        // Validate employee exists (if provided) - no company restriction
        if (!empty($validated['assigned_to'])) {
            $employee = \App\Models\Employee::find($validated['assigned_to']);
            if (!$employee) {
                return back()->withErrors(['assigned_to' => 'Invalid employee selection.']);
            }
        }

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('assets', 'public');
        }

        // Create asset with data from template
        $assetData = [
            'company_id' => $targetCompanyId,
            'asset_category_id' => $template->asset_category_id,
            'location_id' => $validated['location_id'],
            'assigned_to' => $validated['assigned_to'],
            'serial_number' => $validated['serial_number'],
            'model_name' => $template->model_name,
            'model_number' => $template->model_number,
            'manufacturer' => $template->manufacturer,
            'notes' => $template->default_notes,
            'image_path' => $imagePath,
            'asset_template_id' => $template->id,
            'status' => !empty($validated['assigned_to']) ? 'assigned' : 'available',
            'assigned_date' => !empty($validated['assigned_to']) ? now() : null,
        ];

        // Generate asset tag based on template's company
        $assetData['asset_tag'] = Asset::generateUniqueAssetTag($template->company_id);

        $asset = Asset::create($assetData);

        // Increment template usage count
        $template->increment('usage_count');

        return redirect()->route('assets.index')
            ->with('success', 'Asset created successfully.');
    }



    /**
     * Display the specified asset.
     */
    public function show(Asset $asset): Response|RedirectResponse
    {
        $user = Auth::user();
        $ownedCompanyIds = $user->ownedCompanies()->pluck('id');

        // If user doesn't have any companies, redirect to create one
        if ($ownedCompanyIds->isEmpty()) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage assets.');
        }

        // Check if user has access to this asset (asset must belong to one of their companies)
        if (!$ownedCompanyIds->contains($asset->company_id)) {
            abort(403);
        }

        $asset->load(['category', 'location', 'assignments.employee', 'company', 'assetTemplate.company']);

        return Inertia::render('Assets/Show', [
            'asset' => $asset,
        ]);
    }

    /**
     * Show the form for editing the specified asset.
     */
    public function edit(Asset $asset): Response|RedirectResponse
    {
        $user = Auth::user();
        $ownedCompanyIds = $user->ownedCompanies()->pluck('id');

        // If user doesn't have any companies, redirect to create one
        if ($ownedCompanyIds->isEmpty()) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage assets.');
        }

        // Check if user has access to this asset (asset must belong to one of their companies)
        if (!$ownedCompanyIds->contains($asset->company_id)) {
            abort(403);
        }

        // Get categories and locations from all owned companies for editing flexibility
        $categories = AssetCategory::withDepth()
            ->orderBy('_lft')
            ->get();

        $locations = Location::orderBy('name')
            ->get();

        return Inertia::render('Assets/Edit', [
            'asset' => $asset,
            'categories' => $categories,
            'locations' => $locations,
        ]);
    }

    /**
     * Update the specified asset in storage.
     */
    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $user = Auth::user();
        $ownedCompanyIds = $user->ownedCompanies()->pluck('id');

        // If user doesn't have any companies, redirect to create one
        if ($ownedCompanyIds->isEmpty()) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage assets.');
        }

        // Check if user has access to this asset (asset must belong to one of their companies)
        if (!$ownedCompanyIds->contains($asset->company_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'serial_number' => 'nullable|string|max:255',
            'service_tag_number' => 'nullable|string|max:255',
            'finance_tag_number' => 'nullable|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'location_id' => 'required|exists:locations,id',
            'model_name' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Just validate that category and location exist (no company restrictions)
        $category = AssetCategory::find($validated['asset_category_id']);
        $location = Location::find($validated['location_id']);

        if (!$category) {
            return back()->withErrors(['asset_category_id' => 'Invalid asset category.']);
        }

        if (!$location) {
            return back()->withErrors(['location_id' => 'Invalid location.']);
        }

        $asset->update($validated);

        return redirect()->route('assets.show', $asset)
            ->with('success', 'Asset updated successfully.');
    }

    /**
     * Remove the specified asset from storage.
     */
    public function destroy(Asset $asset): RedirectResponse
    {
        $user = Auth::user();
        $ownedCompanyIds = $user->ownedCompanies()->pluck('id');

        // If user doesn't have any companies, redirect to create one
        if ($ownedCompanyIds->isEmpty()) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage assets.');
        }

        // Check if user has access to this asset (asset must belong to one of their companies)
        if (!$ownedCompanyIds->contains($asset->company_id)) {
            abort(403);
        }

        // Check if asset is currently assigned
        if ($asset->assignments()->where('returned_at', null)->count() > 0) {
            return redirect()->route('assets.show', $asset)
                ->with('error', 'Cannot delete asset that is currently assigned. Please return the asset first.');
        }

        $asset->delete();

        return redirect()->route('assets.index')
            ->with('success', 'Asset deleted successfully.');
    }
}
