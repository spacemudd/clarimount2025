<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $user = Auth::user();
        $company = $user->currentCompany();
        
        // If user doesn't have a company, redirect to create one
        if (!$company) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage employees.');
        }
        
        $query = Employee::where('company_id', $company->id)
            ->with(['assets'])
            ->withCount(['assets', 'reportedTickets']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('job_title', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('employment_status', $request->input('status'));
        }

        // Apply department filter
        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        $employees = $query->latest()->paginate(12)->withQueryString();

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
            'company' => $company,
            'filters' => [
                'search' => $request->input('search'),
                'status' => $request->input('status'),
                'department' => $request->input('department'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(): Response|RedirectResponse
    {
        $user = Auth::user();
        $company = $user->currentCompany();
        
        // If user doesn't have a company, redirect to create one
        if (!$company) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage employees.');
        }

        return Inertia::render('Employees/Create', [
            'company' => $company,
        ]);
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $company = $user->currentCompany();
        
        // If user doesn't have a company, redirect to create one
        if (!$company) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage employees.');
        }

        $validated = $request->validate([
            'employee_id' => 'nullable|string|max:50|unique:employees,employee_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'manager' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $company->id;

        $employee = Employee::create($validated);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee): Response|RedirectResponse
    {
        $user = Auth::user();
        $company = $user->currentCompany();
        
        // If user doesn't have a company, redirect to create one
        if (!$company) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage employees.');
        }
        
        // Check if user has access to this employee
        if ($employee->company_id !== $company->id) {
            abort(403);
        }

        $employee->load([
            'company', 
            'assets.assetCategory',
            'assetAssignments.asset.assetCategory',
            'reportedTickets.ticketCategory'
        ]);
        $employee->loadCount(['assets', 'reportedTickets']);

        return Inertia::render('Employees/Show', [
            'employee' => $employee,
        ]);
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee): Response|RedirectResponse
    {
        $user = Auth::user();
        $company = $user->currentCompany();
        
        // If user doesn't have a company, redirect to create one
        if (!$company) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage employees.');
        }
        
        // Check if user has access to this employee
        if ($employee->company_id !== $company->id) {
            abort(403);
        }

        return Inertia::render('Employees/Edit', [
            'employee' => $employee,
        ]);
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $user = Auth::user();
        $company = $user->currentCompany();
        
        // If user doesn't have a company, redirect to create one
        if (!$company) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage employees.');
        }
        
        // Check if user has access to this employee
        if ($employee->company_id !== $company->id) {
            abort(403);
        }

        $validated = $request->validate([
            'employee_id' => 'nullable|string|max:50|unique:employees,employee_id,' . $employee->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'manager' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
            'termination_date' => 'nullable|date',
            'employment_status' => 'required|in:active,inactive,terminated',
            'notes' => 'nullable|string',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee): RedirectResponse
    {
        $user = Auth::user();
        $company = $user->currentCompany();
        
        // If user doesn't have a company, redirect to create one
        if (!$company) {
            return redirect()->route('companies.create')
                ->with('info', 'Please create a company first to manage employees.');
        }
        
        // Check if user has access to this employee
        if ($employee->company_id !== $company->id) {
            abort(403);
        }

        // Check if employee has assets assigned
        if ($employee->assets()->count() > 0) {
            return redirect()->route('employees.index')
                ->with('error', 'Cannot delete employee who has assets assigned. Please return all assets first.');
        }

        // Check if employee has open tickets
        if ($employee->reportedTickets()->whereNotIn('status', ['resolved', 'closed'])->count() > 0) {
            return redirect()->route('employees.index')
                ->with('error', 'Cannot delete employee who has open tickets. Please resolve all tickets first.');
        }

        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }
}
