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
        $company = $user->currentCompany();
        
        // If user doesn't have a company, redirect to create one
        if (!$company) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage assets.');
        }

        $query = Asset::where('company_id', $company->id)
            ->with(['category', 'location', 'assignments']);

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

        // Get categories and locations for filters
        $categories = AssetCategory::scoped(['company_id' => $company->id])
            ->withDepth()
            ->orderBy('_lft')
            ->get();
            
        $locations = Location::where('company_id', $company->id)
            ->orderBy('name')
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
        $company = $user->currentCompany();
        
        // If user doesn't have a company, redirect to create one
        if (!$company) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage assets.');
        }

        $categories = AssetCategory::scoped(['company_id' => $company->id])
            ->withDepth()
            ->orderBy('_lft')
            ->get();
            
        $locations = Location::where('company_id', $company->id)
            ->orderBy('name')
            ->get();

        return Inertia::render('Assets/Create', [
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
            'serial_number' => 'nullable|string|max:255',
            'service_tag_number' => 'nullable|string|max:255',
            'finance_tag_number' => 'nullable|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'location_id' => 'required|exists:locations,id',
            'model_name' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Validate that category and location belong to the same company
        $category = AssetCategory::find($validated['asset_category_id']);
        $location = Location::find($validated['location_id']);
        
        if (!$category || $category->company_id !== $company->id) {
            return back()->withErrors(['asset_category_id' => 'Invalid asset category.']);
        }
        
        if (!$location || $location->company_id !== $company->id) {
            return back()->withErrors(['location_id' => 'Invalid location.']);
        }

        $validated['company_id'] = $company->id;

        $asset = Asset::create($validated);

        return redirect()->route('assets.show', $asset)
            ->with('success', 'Asset created successfully.');
    }

    /**
     * Display the specified asset.
     */
    public function show(Asset $asset): Response|RedirectResponse
    {
        $user = Auth::user();
        $company = $user->currentCompany();
        
        // If user doesn't have a company, redirect to create one
        if (!$company) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage assets.');
        }
        
        // Check if user has access to this asset
        if ($asset->company_id !== $company->id) {
            abort(403);
        }

        $asset->load(['category', 'location', 'assignments.employee', 'company']);

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
        $company = $user->currentCompany();
        
        // If user doesn't have a company, redirect to create one
        if (!$company) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage assets.');
        }
        
        // Check if user has access to this asset
        if ($asset->company_id !== $company->id) {
            abort(403);
        }

        $categories = AssetCategory::scoped(['company_id' => $company->id])
            ->withDepth()
            ->orderBy('_lft')
            ->get();
            
        $locations = Location::where('company_id', $company->id)
            ->orderBy('name')
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
        $company = $user->currentCompany();
        
        // If user doesn't have a company, redirect to create one
        if (!$company) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage assets.');
        }
        
        // Check if user has access to this asset
        if ($asset->company_id !== $company->id) {
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

        // Validate that category and location belong to the same company
        $category = AssetCategory::find($validated['asset_category_id']);
        $location = Location::find($validated['location_id']);
        
        if (!$category || $category->company_id !== $company->id) {
            return back()->withErrors(['asset_category_id' => 'Invalid asset category.']);
        }
        
        if (!$location || $location->company_id !== $company->id) {
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
        $company = $user->currentCompany();
        
        // If user doesn't have a company, redirect to create one
        if (!$company) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage assets.');
        }
        
        // Check if user has access to this asset
        if ($asset->company_id !== $company->id) {
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