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

        // TEMPORARY FIX: Skip all team checks and just continue
        // This will allow direct URL access while you fix the team setup
        
        // If user has a team_id, try to set the team context
        if ($user->team_id) {
            $team = Team::find($user->team_id);
            if ($team) {
                $request->attributes->set('team', $team);
            }
        }
        
        return $next($request);
    }
}
