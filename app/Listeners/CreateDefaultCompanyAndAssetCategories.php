<?php

namespace App\Listeners;

use App\Models\Company;
use App\Services\AssetCategorySeederService;
use Illuminate\Auth\Events\Registered;
class CreateDefaultCompanyAndAssetCategories
{

    /**
     * Create the event listener.
     */
    public function __construct(
        private AssetCategorySeederService $assetCategorySeeder
    ) {}

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;

        // Skip if user already has a company
        if ($user->ownedCompanies()->exists()) {
            return;
        }

        // Create a default company for the user
        $company = Company::create([
            'name_en' => $user->name . "'s Company",
            'name_ar' => 'شركة ' . $user->name,
            'company_email' => $user->email,
            'description_en' => 'Default company created for ' . $user->name,
            'description_ar' => 'الشركة الافتراضية المنشأة لـ ' . $user->name,
            'owner_id' => $user->id,
            'is_active' => true,
            'slug' => 'company-' . $user->id . '-' . time(),
            'settings' => [
                'auto_created' => true,
                'created_at' => now()->toISOString(),
            ],
        ]);

        // Seed default asset categories for the new company
        $this->assetCategorySeeder->seedDefaultCategories($company);
    }
} 