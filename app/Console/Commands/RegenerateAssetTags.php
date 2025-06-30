<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\Company;
use Illuminate\Console\Command;

class RegenerateAssetTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asset:regenerate-tags {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate all asset tags using company name abbreviations instead of simple numbers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        } else {
            $this->warn('⚠️  This will update ALL asset tags in the database!');
            if (!$this->confirm('Are you sure you want to continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
            $this->newLine();
        }

        // Get all companies with assets
        $companies = Company::whereHas('assets')->with('assets')->get();
        
        if ($companies->isEmpty()) {
            $this->error('No companies with assets found.');
            return 1;
        }

        $totalUpdated = 0;
        $this->info("Found {$companies->count()} companies with assets...");
        $this->newLine();

        foreach ($companies as $company) {
            $this->regenerateCompanyAssetTags($company, $dryRun, $totalUpdated);
        }

        $this->newLine();
        if ($dryRun) {
            $this->info("🔍 DRY RUN COMPLETE: Would update {$totalUpdated} asset tags");
        } else {
            $this->info("✅ Successfully updated {$totalUpdated} asset tags!");
        }
        
        return 0;
    }

    /**
     * Regenerate asset tags for a specific company
     */
    private function regenerateCompanyAssetTags(Company $company, bool $dryRun, int &$totalUpdated): void
    {
        $assets = $company->assets()->orderBy('created_at')->get();
        
        if ($assets->isEmpty()) {
            return;
        }

        // Generate company abbreviation
        $abbreviation = $this->generateCompanyAbbreviation($company->name_en);
        
        $this->info("🏢 Company: {$company->name_en}");
        $this->info("📝 Abbreviation: {$abbreviation}");
        $this->info("📦 Assets: {$assets->count()}");

        $updatedCount = 0;
        $counter = 1;

        foreach ($assets as $asset) {
            $oldTag = $asset->asset_tag;
            $newTag = $abbreviation . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
            
            // Check if the new tag already exists for a different asset
            while (Asset::where('company_id', $company->id)
                       ->where('asset_tag', $newTag)
                       ->where('id', '!=', $asset->id)
                       ->exists()) {
                $counter++;
                $newTag = $abbreviation . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
            }

            if ($oldTag !== $newTag) {
                if ($dryRun) {
                    $this->line("  • {$oldTag} → {$newTag}");
                } else {
                    $asset->asset_tag = $newTag;
                    $asset->save();
                    $this->line("  ✓ {$oldTag} → {$newTag}");
                }
                $updatedCount++;
                $totalUpdated++;
            } else {
                $this->line("  - {$oldTag} (no change needed)");
            }
            
            $counter++;
        }

        $this->info("📊 Updated: {$updatedCount}/{$assets->count()} assets for {$company->name_en}");
        $this->newLine();
    }

    /**
     * Generate a 4-letter abbreviation from company name
     * (Same logic as in Asset model)
     */
    private function generateCompanyAbbreviation(string $companyName): string
    {
        // Remove common business words and clean the name
        $commonWords = ['corporation', 'corp', 'company', 'co', 'inc', 'incorporated', 'ltd', 'limited', 'llc', 'for', 'and', 'the', 'of', 'in', 'to'];
        $cleanName = str_replace($commonWords, '', strtolower($companyName));
        
        // Split into words and remove empty elements
        $words = array_filter(explode(' ', trim($cleanName)));
        
        if (count($words) >= 2) {
            // Take first 2 letters from first 2 words
            $abbreviation = substr($words[0], 0, 2) . substr($words[1], 0, 2);
        } elseif (count($words) == 1) {
            // Take first 4 letters if only one word
            $abbreviation = substr($words[0], 0, 4);
        } else {
            // Fallback: take first 4 letters of original name (alphanumeric only)
            $alphanumeric = preg_replace('/[^a-zA-Z0-9]/', '', $companyName);
            $abbreviation = substr($alphanumeric, 0, 4);
        }
        
        // Ensure it's exactly 4 characters, pad with 'X' if needed
        $abbreviation = str_pad($abbreviation, 4, 'X');
        
        return strtoupper(substr($abbreviation, 0, 4));
    }
}
