<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class TeamController extends Controller
{
    /**
     * Show team selection page for users without a team
     */
    public function select()
    {
        $user = auth()->user();
        
        // Get teams user owns or is a member of
        $ownedTeams = $user->ownedTeams()->with('users')->get();
        $memberTeams = Team::whereHas('users', function($query) use ($user) {
            $query->where('users.id', $user->id);
        })->where('owner_id', '!=', $user->id)->get();
        
        return Inertia::render('Teams/Select', [
            'ownedTeams' => $ownedTeams,
            'memberTeams' => $memberTeams,
        ]);
    }

    /**
     * Show team creation form
     */
    public function create()
    {
        return Inertia::render('Teams/Create');
    }

    /**
     * Store a new team
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'slug' => 'nullable|string|max:255|unique:teams,slug',
        ]);

        $user = $request->user();
        $slug = $request->slug ?: Str::slug($request->name);

        // Ensure slug is unique
        $originalSlug = $slug;
        $counter = 1;
        while (Team::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        // Create the team
        $team = Team::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'owner_id' => $user->id,
            'is_active' => true,
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);

        // Create team-specific roles
        $this->createTeamRoles($team);

        // Assign user to team and give owner role
        $user->update([
            'team_id' => $team->id,
            'joined_team_at' => now(),
        ]);
        
        $user->assignRole('team-owner', 'web', $team->id);

        return redirect()->route('dashboard')
            ->with('success', 'Team created successfully!');
    }

    /**
     * Show team settings
     */
    public function show(Team $team)
    {
        $this->authorize('view', $team);
        
        $team->load(['owner', 'users.roles' => function($query) use ($team) {
            $query->where('team_id', $team->id);
        }]);

        return Inertia::render('Teams/Show', [
            'team' => $team,
            'userRole' => auth()->user()->getTeamRoles()->first()?->name,
        ]);
    }

    /**
     * Update team settings
     */
    public function update(Request $request, Team $team)
    {
        $this->authorize('update', $team);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
        ]);

        $team->update([
            'name' => $request->name,
            'description' => $request->description,
            'website' => $request->website,
        ]);

        return back()->with('success', 'Team updated successfully!');
    }

    /**
     * Switch to a different team
     */
    public function switch(Team $team)
    {
        $user = auth()->user();
        
        // Verify user has access to this team
        if (!$team->hasMember($user) && !$team->isOwner($user)) {
            abort(403, 'You do not have access to this team.');
        }

        if (!$team->is_active) {
            return back()->withErrors(['team' => 'This team is not active.']);
        }

        $user->switchToTeam($team);

        return redirect()->route('dashboard')
            ->with('success', "Switched to team: {$team->name}");
    }

    /**
     * Invite user to team
     */
    public function inviteUser(Request $request, Team $team)
    {
        $this->authorize('invite', $team);

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|string|in:team-admin,team-member,team-viewer',
        ]);

        $user = User::where('email', $request->email)->first();
        
        if ($team->hasMember($user) || $team->isOwner($user)) {
            return back()->withErrors(['email' => 'User is already a member of this team.']);
        }

        // Add user to team
        $user->update([
            'team_id' => $team->id,
            'joined_team_at' => now(),
        ]);

        // Assign role
        $user->assignRole($request->role, 'web', $team->id);

        return back()->with('success', "User {$user->name} has been added to the team.");
    }

    /**
     * Remove user from team
     */
    public function removeUser(Request $request, Team $team, User $user)
    {
        $this->authorize('removeUser', $team);

        if ($team->isOwner($user)) {
            return back()->withErrors(['user' => 'Cannot remove team owner.']);
        }

        if (!$team->hasMember($user)) {
            return back()->withErrors(['user' => 'User is not a member of this team.']);
        }

        // Remove user from team
        $user->update(['team_id' => null, 'joined_team_at' => null]);
        
        // Remove team-specific roles
        $user->roles()->where('team_id', $team->id)->detach();

        return back()->with('success', "User {$user->name} has been removed from the team.");
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
            $teamRole = Role::create([
                'name' => $templateRole->name,
                'guard_name' => $templateRole->guard_name,
                'team_id' => $team->id,
            ]);

            $teamRole->syncPermissions($templateRole->permissions);
        }
    }
}
