<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\AssetCategorySeederService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SeedAssetCategoriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:asset-categories {company_id? : The company ID to seed categories for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed default asset categories for a company';

    /**
     * Execute the console command.
     */
    public function handle(AssetCategorySeederService $seeder): int
    {
        $companyId = $this->argument('company_id');

        if ($companyId) {
            $company = Company::find($companyId);
            if (!$company) {
                $this->error("Company with ID {$companyId} not found.");
                return 1;
            }
        } else {
            // Show all companies and let user choose
            $companies = Company::all();
            if ($companies->isEmpty()) {
                $this->error('No companies found.');
                return 1;
            }

            $choices = $companies->map(function ($company) {
                return "[{$company->id}] {$company->name_en}";
            })->toArray();

            $selection = $this->choice('Select a company:', $choices);
            $companyId = (int) Str::between($selection, '[', ']');
            $company = Company::find($companyId);
        }

        $this->info("Seeding asset categories for company: {$company->name_en}");

        try {
            $seeder->seedDefaultCategories($company);
            $this->info('✅ Asset categories seeded successfully!');
            
            // Show summary
            $categoriesCount = $company->fresh()->assetCategories()->count();
            $this->info("Total categories created: {$categoriesCount}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error seeding asset categories: ' . $e->getMessage());
            return 1;
        }
    }
} 