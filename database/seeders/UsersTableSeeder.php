<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        $existingAdmin = User::where('email', 'admin@clarimount.com')->first();
        
        if ($existingAdmin) {
            $this->command->info('Admin user already exists: admin@clarimount.com');
            return;
        }

        // Ensure super-admin role exists
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if (!$superAdminRole) {
            $this->command->error('Super admin role not found! Please run RolesAndPermissionsSeeder first.');
            return;
        }

        // Create the admin user
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@clarimount.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'language' => 'en',
        ]);

        // Assign super-admin role (global role, no team context)
        // Explicitly set team_id to null for global role
        $admin->roles()->attach($superAdminRole->id, ['team_id' => null]);

        $this->command->info('✅ Super admin user created successfully!');
        $this->command->info('📧 Email: admin@clarimount.com');
        $this->command->info('🔑 Password: password');
        $this->command->info('🌐 Login at: ' . url('/login'));
    }
} 