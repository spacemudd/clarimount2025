<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Display a listing of the companies.
     */
    public function index(): Response
    {
        $companies = Company::where('owner_id', Auth::id())
            ->latest()
            ->paginate(10);

        return Inertia::render('Companies/Index', [
            'companies' => $companies,
        ]);
    }

    /**
     * Show the form for creating a new company.
     */
    public function create(): Response
    {
        return Inertia::render('Companies/Create');
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'company_email' => 'required|email|unique:companies,company_email',
            'description_en' => 'nullable|string|max:1000',
            'description_ar' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
        ]);

        $validated['owner_id'] = Auth::id();
        $validated['slug'] = Str::slug($validated['name_en']);

        $company = Company::create($validated);

        return redirect()->route('companies.show', $company)
            ->with('success', 'Company created successfully.');
    }

    /**
     * Display the specified company.
     */
    public function show(Company $company): Response
    {
        // Check if user owns this company
        if ($company->owner_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('Companies/Show', [
            'company' => $company->load('owner'),
        ]);
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Company $company): Response
    {
        // Check if user owns this company
        if ($company->owner_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('Companies/Edit', [
            'company' => $company,
        ]);
    }

    /**
     * Update the specified company in storage.
     */
    public function update(Request $request, Company $company)
    {
        // Check if user owns this company
        if ($company->owner_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'company_email' => 'required|email|unique:companies,company_email,' . $company->id,
            'description_en' => 'nullable|string|max:1000',
            'description_ar' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
        ]);

        // Update slug if English name changed
        if ($validated['name_en'] !== $company->name_en) {
            $validated['slug'] = Str::slug($validated['name_en']);
        }

        $company->update($validated);

        return redirect()->route('companies.show', $company)
            ->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified company from storage.
     */
    public function destroy(Company $company)
    {
        // Check if user owns this company
        if ($company->owner_id !== Auth::id()) {
            abort(403);
        }

        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Company deleted successfully.');
    }
}
