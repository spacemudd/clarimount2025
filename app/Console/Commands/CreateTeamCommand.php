<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Team;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class CreateTeamCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'team:create 
                            {name : The name of the team}
                            {owner_email : The email of the team owner}
                            {--description= : Team description}
                            {--slug= : Custom team slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new team with owner and proper role setup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $ownerEmail = $this->argument('owner_email');
        $description = $this->option('description');
        $slug = $this->option('slug') ?: Str::slug($name);

        // Find the owner user
        $owner = User::where('email', $ownerEmail)->first();
        if (!$owner) {
            $this->error("User with email {$ownerEmail} not found!");
            return 1;
        }

        // Check if team with slug already exists
        if (Team::where('slug', $slug)->exists()) {
            $this->error("Team with slug '{$slug}' already exists!");
            return 1;
        }

        // Create the team
        $team = Team::create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'owner_id' => $owner->id,
            'is_active' => true,
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->addDays(14), // 14-day trial
        ]);

        // Create team-specific roles
        $this->createTeamRoles($team);

        // Clear permissions cache to ensure new roles are available
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Assign owner to team and give owner role
        $owner->update(['team_id' => $team->id, 'joined_team_at' => now()]);
        
        // Find the role directly from database and assign it with team context
        $ownerRole = Role::where('name', 'team-owner-' . $team->id)->first();
        if ($ownerRole) {
            $owner->roles()->attach($ownerRole, ['team_id' => $team->id]);
        }

        $this->info("Team '{$name}' created successfully!");
        $this->info("Team ID: {$team->id}");
        $this->info("Team Slug: {$team->slug}");
        $this->info("Owner: {$owner->name} ({$owner->email})");
        $this->info("Trial ends: {$team->trial_ends_at->format('Y-m-d H:i:s')}");

        return 0;
    }

    /**
     * Create team-specific roles for the team
     */
    private function createTeamRoles(Team $team)
    {
        // Get template roles (these are global templates)
        $templateRoles = Role::whereNull('team_id')->whereIn('name', [
            'team-owner',
            'team-admin', 
            'team-member',
            'team-viewer'
        ])->get();

        foreach ($templateRoles as $templateRole) {
            // Create unique role name for team
            $teamRoleName = $templateRole->name . '-' . $team->id;
            
            // Check if team-specific role already exists
            $existingRole = Role::where('name', $teamRoleName)
                ->where('guard_name', $templateRole->guard_name)
                ->first();

            if (!$existingRole) {
                // Create team-specific role with unique name
                $teamRole = Role::create([
                    'name' => $teamRoleName,
                    'guard_name' => $templateRole->guard_name,
                    'team_id' => $team->id,
                ]);

                // Copy permissions from template role
                $teamRole->syncPermissions($templateRole->permissions);
                
                $this->info("Created role: {$teamRoleName}");
            }
        }

        $this->info("Created team-specific roles for team: {$team->name}");
    }
}
