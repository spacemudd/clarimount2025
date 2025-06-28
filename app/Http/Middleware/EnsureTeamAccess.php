<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Team;

class EnsureTeamAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // If user doesn't have a team, redirect to team selection/creation
        if (!$user->team_id) {
            return redirect()->route('teams.select');
        }

        // Ensure user's team exists and is active
        $team = Team::find($user->team_id);
        if (!$team || !$team->is_active) {
            $user->update(['team_id' => null]);
            return redirect()->route('teams.select')
                ->withErrors(['team' => 'Your team is no longer available.']);
        }

        // Ensure user is still a member of the team
        if (!$team->hasMember($user) && !$team->isOwner($user)) {
            $user->update(['team_id' => null]);
            return redirect()->route('teams.select')
                ->withErrors(['team' => 'You are no longer a member of this team.']);
        }

        // Check if team has active subscription (for SaaS)
        if (!$team->hasActiveSubscription()) {
            return redirect()->route('teams.billing')
                ->withErrors(['subscription' => 'Your team subscription has expired.']);
        }

        // Set team context for the request
        $request->attributes->set('team', $team);
        
        return $next($request);
    }
}
