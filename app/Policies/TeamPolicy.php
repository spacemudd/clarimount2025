<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can view any teams.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasTeamPermission('view team') || 
               $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can view the team.
     */
    public function view(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team) || 
               $user->ownsTeam($team) ||
               $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can create teams.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create a team
        return true;
    }

    /**
     * Determine whether the user can update the team.
     */
    public function update(User $user, Team $team): bool
    {
        return ($user->belongsToTeam($team) && $user->hasTeamPermission('edit team')) ||
               $user->ownsTeam($team) ||
               $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can delete the team.
     */
    public function delete(User $user, Team $team): bool
    {
        return $user->ownsTeam($team) ||
               ($user->belongsToTeam($team) && $user->hasTeamPermission('delete team')) ||
               $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can restore the team.
     */
    public function restore(User $user, Team $team): bool
    {
        return $user->ownsTeam($team) || $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can permanently delete the team.
     */
    public function forceDelete(User $user, Team $team): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can invite users to the team.
     */
    public function invite(User $user, Team $team): bool
    {
        return ($user->belongsToTeam($team) && $user->hasTeamPermission('invite users')) ||
               $user->ownsTeam($team) ||
               $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can remove users from the team.
     */
    public function removeUser(User $user, Team $team): bool
    {
        return ($user->belongsToTeam($team) && $user->hasTeamPermission('remove users')) ||
               $user->ownsTeam($team) ||
               $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can assign roles in the team.
     */
    public function assignRoles(User $user, Team $team): bool
    {
        return ($user->belongsToTeam($team) && $user->hasTeamPermission('assign roles')) ||
               $user->ownsTeam($team) ||
               $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can manage team settings.
     */
    public function manageSettings(User $user, Team $team): bool
    {
        return ($user->belongsToTeam($team) && $user->hasTeamPermission('manage team settings')) ||
               $user->ownsTeam($team) ||
               $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can manage billing for the team.
     */
    public function manageBilling(User $user, Team $team): bool
    {
        return ($user->belongsToTeam($team) && $user->hasTeamPermission('manage billing')) ||
               $user->ownsTeam($team) ||
               $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can view analytics for the team.
     */
    public function viewAnalytics(User $user, Team $team): bool
    {
        return ($user->belongsToTeam($team) && $user->hasTeamPermission('view analytics')) ||
               $user->ownsTeam($team) ||
               $user->hasRole('super-admin');
    }
}
