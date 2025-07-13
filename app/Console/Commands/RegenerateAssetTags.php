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

        // Get all assets with their templates and companies
        $assets = Asset::with(['assetTemplate', 'company'])->get();
        
        if ($assets->isEmpty()) {
            $this->error('No assets found.');
            return 1;
        }

        $totalUpdated = 0;
        $this->info("Found {$assets->count()} assets...");
        $this->newLine();

        // Group assets by the company that should be used for tag generation (asset's company)
        $assetsByTagCompany = $assets->groupBy(function ($asset) {
            // Since all templates are now global, always use the asset's company for tag generation
            return $asset->company_id;
        });

        foreach ($assetsByTagCompany as $companyId => $companyAssets) {
            $company = Company::find($companyId);
            if (!$company) {
                $this->warn("Company with ID {$companyId} not found, skipping...");
                continue;
            }
            
            $this->regenerateAssetTagsForCompany($company, $companyAssets, $dryRun, $totalUpdated);
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
     * Regenerate asset tags for assets based on a specific company's naming
     */
    private function regenerateAssetTagsForCompany(Company $company, $assets, bool $dryRun, int &$totalUpdated): void
    {
        if ($assets->isEmpty()) {
            return;
        }

        $this->info("🏢 Tag Company: {$company->name_en}");
        $this->info("📦 Assets: {$assets->count()}");

        $updatedCount = 0;

        // Sort assets by creation date for consistent numbering
        $sortedAssets = $assets->sortBy('created_at');

        // Generate sequential tags manually to avoid duplicates during the same run
        $abbreviation = $this->generateCompanyAbbreviation($company->name_en);
        $counter = 1;
        $existingTags = Asset::with(['assetTemplate', 'company'])
            ->get()
            ->filter(function ($existingAsset) use ($company) {
                // Since all templates are now global, always use the asset's company for filtering
                return $existingAsset->company_id == $company->id;
            })
            ->pluck('asset_tag')
            ->toArray();

        foreach ($sortedAssets as $asset) {
            $oldTag = $asset->asset_tag;
            
            // Generate new sequential tag
            do {
                $newTag = $abbreviation . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
                $counter++;
            } while (in_array($newTag, $existingTags) && $newTag !== $oldTag);

            if ($oldTag !== $newTag) {
                if ($dryRun) {
                    $this->line("  • {$oldTag} → {$newTag} (Asset: {$asset->id})");
                } else {
                    $asset->asset_tag = $newTag;
                    $asset->save();
                    $this->line("  ✓ {$oldTag} → {$newTag} (Asset: {$asset->id})");
                    
                    // Update the existing tags array to prevent duplicates in current run
                    $existingTags[] = $newTag;
                    if (($key = array_search($oldTag, $existingTags)) !== false) {
                        unset($existingTags[$key]);
                    }
                }
                $updatedCount++;
                $totalUpdated++;
            } else {
                $this->line("  - {$oldTag} (no change needed, Asset: {$asset->id})");
            }
        }

        $this->info("📊 Updated: {$updatedCount}/{$assets->count()} assets using {$company->name_en} naming");
        $this->newLine();
    }

    /**
     * Generate a 4-letter abbreviation from company name
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
