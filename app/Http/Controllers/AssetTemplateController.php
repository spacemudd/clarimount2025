<?php

namespace App\Http\Controllers;

use App\Models\AssetTemplate;
use App\Models\AssetCategory;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AssetTemplateController extends Controller
{
    /**
     * Display a listing of asset templates.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $user = Auth::user();
        $companies = $user->ownedCompanies()->get();
        
        if ($companies->isEmpty()) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage asset templates.');
        }

        $currentCompany = $user->currentCompany();
        $companyIds = $companies->pluck('id');

        $query = AssetTemplate::query()
            ->where(function ($q) use ($companyIds) {
                $q->where('is_global', true)
                  ->orWhereIn('company_id', $companyIds);
            })
            ->with(['assetCategory', 'company', 'createdBy']);

        // Handle search
        if ($search = $request->get('search')) {
            $query->search($search);
        }

        // Handle category filter
        if ($categoryId = $request->get('category_id')) {
            $query->where('asset_category_id', $categoryId);
        }

        // Handle company filter
        if ($companyId = $request->get('company_id')) {
            $query->where('company_id', $companyId);
        }

        $templates = $query->orderBy('usage_count', 'desc')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // Get categories for filtering
        $categories = AssetCategory::whereIn('company_id', $companyIds)
            ->withDepth()
            ->orderBy('_lft')
            ->get();

        return Inertia::render('AssetTemplates/Index', [
            'templates' => $templates,
            'companies' => $companies,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'company_id']),
        ]);
    }

    /**
     * Search asset templates for async selection.
     */
    public function search(Request $request): JsonResponse
    {
        $user = Auth::user();
        $companyIds = $user->ownedCompanies()->pluck('id');
        
        $query = $request->get('q', '');
        $categoryId = $request->get('category_id');
        $companyId = $request->get('company_id');
        
        $templates = AssetTemplate::query()
            ->where(function ($q) use ($companyIds, $companyId) {
                if ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhere('is_global', true);
                } else {
                    $q->where('is_global', true)
                      ->orWhereIn('company_id', $companyIds);
                }
            })
            ->when($query, function ($q) use ($query) {
                return $q->search($query);
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                return $q->where('asset_category_id', $categoryId);
            })
            ->with(['assetCategory', 'company'])
            ->orderBy('usage_count', 'desc')
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'manufacturer' => $template->manufacturer,
                    'model_name' => $template->model_name,
                    'model_number' => $template->model_number,
                    'display_name' => $template->display_name,
                    'specifications' => $template->specifications,
                    'default_notes' => $template->default_notes,
                    'image_path' => $template->image_path,
                    'formatted_specifications' => $template->formatted_specifications,
                    'category_name' => $template->assetCategory?->name,
                    'company_name' => $template->company?->name_en ?? 'Global',
                    'usage_count' => $template->usage_count,
                    'is_global' => $template->is_global,
                ];
            });

        return response()->json($templates);
    }

    /**
     * Show the form for creating a new asset template.
     */
    public function create(): Response|RedirectResponse
    {
        $user = Auth::user();
        $companies = $user->ownedCompanies()->get();
        
        if ($companies->isEmpty()) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage asset templates.');
        }

        $currentCompany = $user->currentCompany();
        $companyIds = $companies->pluck('id');
        
        // Get categories for all companies the user owns
        $categories = collect();
        if ($companyIds->isNotEmpty()) {
            $categories = AssetCategory::whereIn('company_id', $companyIds)
                ->withDepth()
                ->orderBy('_lft')
                ->get();
        }

        return Inertia::render('AssetTemplates/Create', [
            'companies' => $companies,
            'currentCompany' => $currentCompany,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created asset template.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model_name' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'company_id' => 'nullable|exists:companies,id',
            'specifications' => 'nullable|array',
            'default_notes' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_global' => 'boolean',
        ]);

        // Verify user owns the selected company (if not global)
        if (!$validated['is_global'] && $validated['company_id']) {
            $user->ownedCompanies()->findOrFail($validated['company_id']);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('asset-templates', 'public');
            $validated['image_path'] = $imagePath;
        }

        $validated['created_by_user_id'] = $user->id;

        $template = AssetTemplate::create($validated);

        // If this is an AJAX request (from assets wizard), return JSON
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'template' => $template->load(['assetCategory', 'company']),
                'message' => 'Asset template created successfully.',
            ]);
        }

        return redirect()->route('asset-templates.show', $template)
            ->with('success', 'Asset template created successfully.');
    }

    /**
     * Display the specified asset template.
     */
    public function show(AssetTemplate $assetTemplate): Response
    {
        $user = Auth::user();
        $userCompanyIds = $user->ownedCompanies()->pluck('id');

        // Check if user can view this template
        if (!$assetTemplate->is_global && !$userCompanyIds->contains($assetTemplate->company_id)) {
            abort(403);
        }

        $assetTemplate->load(['assetCategory', 'company', 'createdBy']);

        return Inertia::render('AssetTemplates/Show', [
            'template' => $assetTemplate,
        ]);
    }

    /**
     * Show the form for editing the specified asset template.
     */
    public function edit(AssetTemplate $assetTemplate): Response|RedirectResponse
    {
        $user = Auth::user();
        
        if (!$assetTemplate->canBeEditedBy($user)) {
            abort(403);
        }

        $companies = $user->ownedCompanies()->get();
        $companyIds = $companies->pluck('id');
        
        $categories = AssetCategory::whereIn('company_id', $companyIds)
            ->withDepth()
            ->orderBy('_lft')
            ->get();

        return Inertia::render('AssetTemplates/Edit', [
            'template' => $assetTemplate->load(['assetCategory', 'company']),
            'companies' => $companies,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified asset template.
     */
    public function update(Request $request, AssetTemplate $assetTemplate): RedirectResponse
    {
        $user = Auth::user();
        
        if (!$assetTemplate->canBeEditedBy($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model_name' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'company_id' => 'nullable|exists:companies,id',
            'specifications' => 'nullable|array',
            'default_notes' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'delete_image' => 'boolean',
            'is_global' => 'boolean',
        ]);

        // Verify user owns the selected company (if not global)
        if (!$validated['is_global'] && $validated['company_id']) {
            $user->ownedCompanies()->findOrFail($validated['company_id']);
        }

        // Handle image deletion
        if ($validated['delete_image'] && $assetTemplate->image_path) {
            \Storage::disk('public')->delete($assetTemplate->image_path);
            $validated['image_path'] = null;
        }

        // Handle image upload (new image)
        if ($request->hasFile('image')) {
            // Delete old image if exists (and not already deleted above)
            if ($assetTemplate->image_path && !$validated['delete_image']) {
                \Storage::disk('public')->delete($assetTemplate->image_path);
            }
            
            $imagePath = $request->file('image')->store('asset-templates', 'public');
            $validated['image_path'] = $imagePath;
        }

        // Remove delete_image from validated data as it's not a model field
        unset($validated['delete_image']);

        $assetTemplate->update($validated);

        return redirect()->route('asset-templates.show', $assetTemplate)
            ->with('success', 'Asset template updated successfully.');
    }

    /**
     * Remove the specified asset template.
     */
    public function destroy(AssetTemplate $assetTemplate): RedirectResponse
    {
        $user = Auth::user();
        
        if (!$assetTemplate->canBeEditedBy($user)) {
            abort(403);
        }

        // Delete associated image if exists
        if ($assetTemplate->image_path) {
            \Storage::disk('public')->delete($assetTemplate->image_path);
        }

        $assetTemplate->delete();

        return redirect()->route('asset-templates.index')
            ->with('success', 'Asset template deleted successfully.');
    }
}
