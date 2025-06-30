<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Company;
use App\Models\AssetCategory;
use Illuminate\Console\Command;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

class TestUserRegistrationFlow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:user-registration {email=test@example.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the user registration flow to verify asset categories are created';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        
        $this->info("Testing user registration flow for: {$email}");
        
        // Clean up any existing test user
        User::where('email', $email)->delete();
        
        // Create a new user
        $this->info('Creating new user...');
        $user = User::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => Hash::make('password'),
        ]);
        
        // Fire the Registered event (this simulates what happens during registration)
        $this->info('Firing Registered event...');
        event(new Registered($user));
        
        // Check if company was created
        $company = $user->currentCompany();
        if (!$company) {
            $this->error('❌ No company was created for the user');
            return 1;
        }
        
        $this->info("✅ Company created: {$company->name_en} (ID: {$company->id})");
        
        // Check if asset categories were created
        $categoriesCount = AssetCategory::where('company_id', $company->id)->count();
        
        if ($categoriesCount === 0) {
            $this->error('❌ No asset categories were created');
            return 1;
        }
        
        $this->info("✅ Asset categories created: {$categoriesCount} categories");
        
        // Show some example categories
        $rootCategories = AssetCategory::where('company_id', $company->id)
            ->whereIsRoot()
            ->get();
            
        $this->info('Root categories:');
        foreach ($rootCategories as $category) {
            $childrenCount = $category->children()->count();
            $this->line("  - {$category->name} ({$category->code}) - {$childrenCount} subcategories");
        }
        
        // Show detailed breakdown
        $this->newLine();
        $this->info('Category breakdown:');
        $categories = AssetCategory::where('company_id', $company->id)
            ->with('ancestors')
            ->get()
            ->groupBy('depth');
            
        foreach ($categories as $depth => $cats) {
            $this->line("  Depth {$depth}: " . $cats->count() . ' categories');
        }
        
        $this->newLine();
        $this->info("🎉 User registration flow test completed successfully!");
        $this->info("The user can now access {$categoriesCount} asset categories in their company.");
        
        // Clean up test user if needed
        if ($this->confirm('Delete the test user?', true)) {
            // This will cascade delete the company and asset categories due to foreign key constraints
            $user->delete();
            $this->info('✅ Test user and associated data deleted');
        }
        
        return 0;
    }
}
