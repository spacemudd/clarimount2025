<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AdminTeamController extends Controller
{
    // Middleware is applied at route level in web.php

    /**
     * Display admin teams dashboard
     */
    public function index(Request $request)
    {
        $query = Team::with(['owner', 'users'])
            ->withCount('users');

        // Search functionality
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('slug', 'LIKE', '%' . $request->search . '%')
                  ->orWhereHas('owner', function($q) use ($request) {
                      $q->where('name', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('email', 'LIKE', '%' . $request->search . '%');
                  });
            });
        }

        // Filter by status
        if ($request->status) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'suspended') {
                $query->where('is_active', false);
            }
        }

        // Filter by subscription status
        if ($request->subscription) {
            $query->where('subscription_status', $request->subscription);
        }

        $teams = $query->orderBy('created_at', 'desc')
                      ->paginate(20)
                      ->withQueryString();

        // Get summary stats
        $stats = [
            'total_teams' => Team::count(),
            'active_teams' => Team::where('is_active', true)->count(),
            'suspended_teams' => Team::where('is_active', false)->count(),
            'trial_teams' => Team::where('subscription_status', 'trial')->count(),
            'active_subscriptions' => Team::where('subscription_status', 'active')->count(),
        ];

        return Inertia::render('Admin/Teams/Index', [
            'teams' => $teams,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'subscription']),
        ]);
    }

    /**
     * Show team creation form
     */
    public function create()
    {
        $users = User::select('id', 'name', 'email')
                    ->whereNull('team_id')
                    ->orWhereDoesntHave('ownedTeams')
                    ->orderBy('name')
                    ->get();

        return Inertia::render('Admin/Teams/Create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a new team
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'owner_id' => 'required|exists:users,id',
            'subscription_status' => 'required|in:trial,active,past_due,canceled',
            'trial_days' => 'nullable|integer|min:0|max:365',
            'slug' => 'nullable|string|max:255|unique:teams,slug',
        ]);

        $owner = User::findOrFail($request->owner_id);
        $slug = $request->slug ?: Str::slug($request->name);

        // Ensure slug is unique
        $originalSlug = $slug;
        $counter = 1;
        while (Team::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        // Calculate trial end date
        $trialEndsAt = null;
        if ($request->subscription_status === 'trial') {
            $trialDays = $request->trial_days ?? 14;
            $trialEndsAt = now()->addDays($trialDays);
        }

        // Create the team
        $team = Team::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'owner_id' => $owner->id,
            'subscription_status' => $request->subscription_status,
            'trial_ends_at' => $trialEndsAt,
            'is_active' => true,
        ]);

        // Create team-specific roles
        $this->createTeamRoles($team);

        // Clear permissions cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Assign owner to team and give owner role
        $owner->update([
            'team_id' => $team->id,
            'joined_team_at' => now(),
        ]);

        $ownerRole = Role::where('name', 'team-owner-' . $team->id)->first();
        if ($ownerRole) {
            $owner->roles()->attach($ownerRole, ['team_id' => $team->id]);
        }

        return redirect()->route('admin.teams.index')
            ->with('success', "Team '{$team->name}' created successfully!");
    }

    /**
     * Show specific team details
     */
    public function show(Team $team)
    {
        $team->load([
            'owner',
            'users.roles' => function($query) use ($team) {
                $query->where('team_id', $team->id);
            }
        ]);

        // Get team activity/stats
        $teamStats = [
            'total_users' => $team->users()->count(),
            'active_users' => $team->activeUsers()->count(),
            'created_ago' => $team->created_at->diffForHumans(),
            'trial_status' => $team->isOnTrial() ? 'Active' : 'Expired',
            'days_since_created' => $team->created_at->diffInDays(),
        ];

        return Inertia::render('Admin/Teams/Show', [
            'team' => $team,
            'teamStats' => $teamStats,
        ]);
    }

    /**
     * Update team
     */
    public function update(Request $request, Team $team)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'subscription_status' => 'required|in:trial,active,past_due,canceled',
            'trial_days' => 'nullable|integer|min:0|max:365',
            'is_active' => 'required|boolean',
        ]);

        // Calculate trial end date if needed
        $trialEndsAt = $team->trial_ends_at;
        if ($request->subscription_status === 'trial' && $request->has('trial_days')) {
            $trialDays = $request->trial_days ?? 14;
            $trialEndsAt = now()->addDays($trialDays);
        }

        $team->update([
            'name' => $request->name,
            'description' => $request->description,
            'subscription_status' => $request->subscription_status,
            'trial_ends_at' => $trialEndsAt,
            'is_active' => $request->is_active,
        ]);

        return back()->with('success', 'Team updated successfully!');
    }

    /**
     * Suspend a team
     */
    public function suspend(Team $team)
    {
        $team->update(['is_active' => false]);

        return back()->with('success', "Team '{$team->name}' has been suspended.");
    }

    /**
     * Activate a team
     */
    public function activate(Team $team)
    {
        $team->update(['is_active' => true]);

        return back()->with('success', "Team '{$team->name}' has been activated.");
    }

    /**
     * Delete a team (soft delete)
     */
    public function destroy(Team $team)
    {
        // Remove all users from team
        $team->users()->update(['team_id' => null, 'joined_team_at' => null]);
        
        // Remove team-specific roles
        Role::where('team_id', $team->id)->delete();
        
        // Delete the team
        $team->delete();

        return redirect()->route('admin.teams.index')
            ->with('success', "Team '{$team->name}' has been deleted.");
    }

    /**
     * Create team-specific roles
     */
    private function createTeamRoles(Team $team)
    {
        $templateRoles = Role::whereNull('team_id')->whereIn('name', [
            'team-owner',
            'team-admin',
            'team-member',
            'team-viewer'
        ])->get();

        foreach ($templateRoles as $templateRole) {
            $teamRoleName = $templateRole->name . '-' . $team->id;
            
            $existingRole = Role::where('name', $teamRoleName)
                ->where('guard_name', $templateRole->guard_name)
                ->first();

            if (!$existingRole) {
                $teamRole = Role::create([
                    'name' => $teamRoleName,
                    'guard_name' => $templateRole->guard_name,
                    'team_id' => $team->id,
                ]);

                $teamRole->syncPermissions($templateRole->permissions);
            }
        }
    }
}
