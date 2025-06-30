<?php

use App\Http\Controllers\TeamController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetTemplateController;
use App\Http\Controllers\Admin\AdminTeamController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

// Super Admin routes
Route::middleware(['auth', 'verified', 'super-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('dashboard');
    
    // Team management
    Route::resource('teams', AdminTeamController::class);
    Route::post('teams/{team}/suspend', [AdminTeamController::class, 'suspend'])->name('teams.suspend');
    Route::post('teams/{team}/activate', [AdminTeamController::class, 'activate'])->name('teams.activate');
});

// Team management routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Routes that don't require team access
    Route::get('/teams/select', [TeamController::class, 'select'])->name('teams.select');
    Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::post('/teams/{team}/switch', [TeamController::class, 'switch'])->name('teams.switch');
    
    // Routes that require team access
    Route::middleware(['team.access'])->group(function () {
        Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
        Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
        Route::post('/teams/{team}/invite', [TeamController::class, 'inviteUser'])->name('teams.invite');
        Route::delete('/teams/{team}/users/{user}', [TeamController::class, 'removeUser'])->name('teams.remove-user');
    });
    
    // Placeholder routes for team features
    Route::middleware(['team.access'])->group(function () {
        Route::get('/teams/{team}/billing', function () {
            return Inertia::render('Teams/Billing');
        })->name('teams.billing');
        
        Route::get('/teams/{team}/analytics', function () {
            return Inertia::render('Teams/Analytics');
        })->name('teams.analytics')->middleware('permission:view analytics');
    });
});

// Company management routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('companies', CompanyController::class);
});

// IT Asset Management & Ticketing System routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Locations management
    Route::resource('locations', LocationController::class);
    
    // Employees management
    Route::resource('employees', EmployeeController::class);
    
    // Asset Categories management
    Route::resource('asset-categories', AssetCategoryController::class);
    
    // Assets management
    Route::resource('assets', AssetController::class);
    
    // Asset Templates management
    Route::resource('asset-templates', AssetTemplateController::class);
    
    // API endpoints for async searches
    Route::get('api/locations/search', [LocationController::class, 'search'])->name('api.locations.search');
    Route::get('api/asset-templates/search', [AssetTemplateController::class, 'search'])->name('api.asset-templates.search');
});

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
