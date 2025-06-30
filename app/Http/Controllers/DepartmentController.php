<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get companies owned by the user
        $companyIds = Company::where('owner_id', $user->id)->pluck('id');

        $query = Department::with('company')
            ->whereIn('company_id', $companyIds);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $departments = $query->orderBy('code')->paginate(15)->withQueryString();
        
        // Get companies for the filter/context
        $companies = Company::where('owner_id', $user->id)->get();

        return Inertia::render('Departments/Index', [
            'departments' => $departments,
            'companies' => $companies,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $companies = Company::where('owner_id', $user->id)->get();

        return Inertia::render('Departments/Create', [
            'companies' => $companies,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => [
                'required',
                'exists:companies,id',
                function ($attribute, $value, $fail) use ($user) {
                    if (!Company::where('id', $value)->where('owner_id', $user->id)->exists()) {
                        $fail('You can only create departments for companies you own.');
                    }
                },
            ],
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('departments')->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id);
                }),
            ],
            'description' => 'nullable|string|max:1000',
        ]);

        Department::create($validated);

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        // Check if user owns the company this department belongs to
        if ($department->company->owner_id !== auth()->id()) {
            abort(403);
        }

        $department->load(['company', 'employees']);

        return Inertia::render('Departments/Show', [
            'department' => $department,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        // Check if user owns the company this department belongs to
        if ($department->company->owner_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Departments/Edit', [
            'department' => $department,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        // Check if user owns the company this department belongs to
        if ($department->company->owner_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('departments')->where(function ($query) use ($department) {
                    return $query->where('company_id', $department->company_id);
                })->ignore($department->id),
            ],
            'description' => 'nullable|string|max:1000',
        ]);

        $department->update($validated);

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        // Check if user owns the company this department belongs to
        if ($department->company->owner_id !== auth()->id()) {
            abort(403);
        }

        // Check if department has employees
        if ($department->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete department that has employees assigned.');
        }

        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }

    /**
     * Search departments for async selection.
     */
    public function search(Request $request): JsonResponse
    {
        $user = $request->user();
        $companyIds = Company::where('owner_id', $user->id)->pluck('id');
        
        $query = $request->get('q', '');
        $companyId = $request->get('company_id');
        
        $departments = Department::query()
            ->whereIn('company_id', $companyIds)
            ->when($companyId, function ($q) use ($companyId) {
                return $q->where('company_id', $companyId);
            })
            ->when($query, function ($q) use ($query) {
                return $q->where(function ($subQuery) use ($query) {
                    $subQuery->where('name', 'like', "%{$query}%")
                        ->orWhere('code', 'like', "%{$query}%");
                });
            })
            ->with('company')
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'code' => $department->code,
                    'description' => $department->description,
                    'company_name' => $department->company->name_en,
                    'display_name' => "{$department->code}: {$department->name}",
                ];
            });

        return response()->json($departments);
    }
}
