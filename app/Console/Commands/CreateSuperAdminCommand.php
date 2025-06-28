<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateSuperAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-super-admin 
                            {name : The name of the super admin}
                            {email : The email of the super admin}
                            {password : The password for the super admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin user with full system access';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists!");
            return 1;
        }

        // Ensure super-admin role exists
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if (!$superAdminRole) {
            $this->error("Super admin role not found! Please run 'php artisan db:seed --class=RolesAndPermissionsSeeder' first.");
            return 1;
        }

        // Create the user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        // Assign super-admin role
        $user->assignRole('super-admin');

        $this->info("Super admin user created successfully!");
        $this->info("Name: {$user->name}");
        $this->info("Email: {$user->email}");
        $this->info("Role: super-admin");
        $this->info("");
        $this->info("You can now login at: " . url('/login'));

        return 0;
    }
} 