<?php

namespace App\Services;

use App\Models\AssetCategory;
use App\Models\Company;

class AssetCategorySeederService
{
    /**
     * Seed default asset categories for a company
     */
    public function seedDefaultCategories(Company $company): void
    {
        // Check if categories already exist for this company
        if (AssetCategory::where('company_id', $company->id)->exists()) {
            return;
        }

        $this->seedITCategories($company);
        $this->seedFacilityCategories($company);
    }

    /**
     * Seed IT device categories
     */
    private function seedITCategories(Company $company): void
    {
        // Main IT category
        $itCategory = AssetCategory::create([
            'name' => 'IT Equipment',
            'code' => 'IT',
            'description' => 'Information Technology equipment and devices',
            'icon' => 'monitor',
            'color' => '#3b82f6',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        // End-User Devices
        $endUserDevices = $itCategory->children()->create([
            'name' => 'End-User Devices',
            'code' => 'IT-EU',
            'description' => 'Devices used by end users for daily work',
            'icon' => 'laptop',
            'color' => '#6366f1',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        // Laptops
        $laptops = $endUserDevices->children()->create([
            'name' => 'Laptops',
            'code' => 'IT-EU-LAP',
            'description' => 'Portable computers',
            'icon' => 'laptop',
            'color' => '#8b5cf6',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        $laptops->children()->createMany([
            [
                'name' => 'Dell Laptops',
                'code' => 'IT-EU-LAP-DELL',
                'description' => 'Dell brand laptops',
                'icon' => 'laptop',
                'color' => '#0066cc',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'HP Laptops',
                'code' => 'IT-EU-LAP-HP',
                'description' => 'HP brand laptops',
                'icon' => 'laptop',
                'color' => '#0096d6',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'MacBooks',
                'code' => 'IT-EU-LAP-MAC',
                'description' => 'Apple MacBook laptops',
                'icon' => 'laptop',
                'color' => '#000000',
                'company_id' => $company->id,
                'is_active' => true,
            ],
        ]);

        // Desktops
        $desktops = $endUserDevices->children()->create([
            'name' => 'Desktop Computers',
            'code' => 'IT-EU-DESK',
            'description' => 'Desktop computers and workstations',
            'icon' => 'monitor',
            'color' => '#a855f7',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        $desktops->children()->createMany([
            [
                'name' => 'Dell Desktops',
                'code' => 'IT-EU-DESK-DELL',
                'description' => 'Dell desktop computers',
                'icon' => 'monitor',
                'color' => '#0066cc',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'HP Desktops',
                'code' => 'IT-EU-DESK-HP',
                'description' => 'HP desktop computers',
                'icon' => 'monitor',
                'color' => '#0096d6',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'iMacs',
                'code' => 'IT-EU-DESK-MAC',
                'description' => 'Apple iMac computers',
                'icon' => 'monitor',
                'color' => '#000000',
                'company_id' => $company->id,
                'is_active' => true,
            ],
        ]);

        // Mobile Devices
        $mobileDevices = $endUserDevices->children()->create([
            'name' => 'Mobile Devices',
            'code' => 'IT-EU-MOB',
            'description' => 'Smartphones, tablets, and mobile devices',
            'icon' => 'smartphone',
            'color' => '#c084fc',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        $mobileDevices->children()->createMany([
            [
                'name' => 'Smartphones',
                'code' => 'IT-EU-MOB-PHONE',
                'description' => 'Company smartphones',
                'icon' => 'smartphone',
                'color' => '#e879f9',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'Tablets',
                'code' => 'IT-EU-MOB-TAB',
                'description' => 'Tablets and iPads',
                'icon' => 'tablet',
                'color' => '#f0abfc',
                'company_id' => $company->id,
                'is_active' => true,
            ],
        ]);

        // Peripherals
        $peripherals = $endUserDevices->children()->create([
            'name' => 'Peripherals',
            'code' => 'IT-EU-PER',
            'description' => 'Computer peripherals and accessories',
            'icon' => 'mouse',
            'color' => '#d946ef',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        $peripherals->children()->createMany([
            [
                'name' => 'Monitors',
                'code' => 'IT-EU-PER-MON',
                'description' => 'External monitors and displays',
                'icon' => 'monitor-speaker',
                'color' => '#f97316',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'Keyboards',
                'code' => 'IT-EU-PER-KEY',
                'description' => 'Computer keyboards',
                'icon' => 'keyboard',
                'color' => '#eab308',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'Mice',
                'code' => 'IT-EU-PER-MOUSE',
                'description' => 'Computer mice',
                'icon' => 'mouse',
                'color' => '#84cc16',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'Printers',
                'code' => 'IT-EU-PER-PRINT',
                'description' => 'Printers and scanners',
                'icon' => 'printer',
                'color' => '#22c55e',
                'company_id' => $company->id,
                'is_active' => true,
            ],
        ]);

        // Network Equipment
        $networkEquipment = $itCategory->children()->create([
            'name' => 'Network Equipment',
            'code' => 'IT-NET',
            'description' => 'Networking hardware and infrastructure',
            'icon' => 'network',
            'color' => '#10b981',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        $networkEquipment->children()->createMany([
            [
                'name' => 'Switches',
                'code' => 'IT-NET-SW',
                'description' => 'Network switches',
                'icon' => 'router',
                'color' => '#14b8a6',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'Routers',
                'code' => 'IT-NET-RT',
                'description' => 'Network routers',
                'icon' => 'router',
                'color' => '#06b6d4',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'Access Points',
                'code' => 'IT-NET-AP',
                'description' => 'Wireless access points',
                'icon' => 'wifi',
                'color' => '#0ea5e9',
                'company_id' => $company->id,
                'is_active' => true,
            ],
        ]);

        // Servers
        $servers = $itCategory->children()->create([
            'name' => 'Servers',
            'code' => 'IT-SRV',
            'description' => 'Server hardware and infrastructure',
            'icon' => 'server',
            'color' => '#3b82f6',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        $servers->children()->createMany([
            [
                'name' => 'Physical Servers',
                'code' => 'IT-SRV-PHYS',
                'description' => 'Physical server hardware',
                'icon' => 'server',
                'color' => '#6366f1',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'Virtual Servers',
                'code' => 'IT-SRV-VIRT',
                'description' => 'Virtual server instances',
                'icon' => 'cloud',
                'color' => '#8b5cf6',
                'company_id' => $company->id,
                'is_active' => true,
            ],
        ]);
    }

    /**
     * Seed office facility categories
     */
    private function seedFacilityCategories(Company $company): void
    {
        // Main Facility category
        $facilityCategory = AssetCategory::create([
            'name' => 'Facility & Furniture',
            'code' => 'FAC',
            'description' => 'Office furniture, fixtures, and facility equipment',
            'icon' => 'building',
            'color' => '#dc2626',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        // Office Furniture
        $officeFurniture = $facilityCategory->children()->create([
            'name' => 'Office Furniture',
            'code' => 'FAC-FURN',
            'description' => 'Desks, chairs, and office furniture',
            'icon' => 'armchair',
            'color' => '#ea580c',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        // Desks
        $desks = $officeFurniture->children()->create([
            'name' => 'Desks & Workstations',
            'code' => 'FAC-FURN-DESK',
            'description' => 'Office desks and workstations',
            'icon' => 'rectangle-horizontal',
            'color' => '#d97706',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        $desks->children()->createMany([
            [
                'name' => 'Standing Desks',
                'code' => 'FAC-FURN-DESK-STAND',
                'description' => 'Height-adjustable standing desks',
                'icon' => 'move-vertical',
                'color' => '#ca8a04',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'Regular Desks',
                'code' => 'FAC-FURN-DESK-REG',
                'description' => 'Standard office desks',
                'icon' => 'rectangle-horizontal',
                'color' => '#a16207',
                'company_id' => $company->id,
                'is_active' => true,
            ],
        ]);

        // Chairs
        $chairs = $officeFurniture->children()->create([
            'name' => 'Chairs & Seating',
            'code' => 'FAC-FURN-CHAIR',
            'description' => 'Office chairs and seating',
            'icon' => 'armchair',
            'color' => '#65a30d',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        $chairs->children()->createMany([
            [
                'name' => 'Office Chairs',
                'code' => 'FAC-FURN-CHAIR-OFF',
                'description' => 'Ergonomic office chairs',
                'icon' => 'armchair',
                'color' => '#84cc16',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'Meeting Chairs',
                'code' => 'FAC-FURN-CHAIR-MEET',
                'description' => 'Conference and meeting room chairs',
                'icon' => 'chair',
                'color' => '#a3e635',
                'company_id' => $company->id,
                'is_active' => true,
            ],
        ]);

        // Storage
        $storage = $officeFurniture->children()->create([
            'name' => 'Storage & Organization',
            'code' => 'FAC-FURN-STOR',
            'description' => 'Filing cabinets, shelves, and storage',
            'icon' => 'archive',
            'color' => '#16a34a',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        $storage->children()->createMany([
            [
                'name' => 'Filing Cabinets',
                'code' => 'FAC-FURN-STOR-FILE',
                'description' => 'Document filing cabinets',
                'icon' => 'file-cabinet',
                'color' => '#059669',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'Bookshelves',
                'code' => 'FAC-FURN-STOR-SHELF',
                'description' => 'Office bookshelves and storage units',
                'icon' => 'book-open',
                'color' => '#0d9488',
                'company_id' => $company->id,
                'is_active' => true,
            ],
        ]);

        // Meeting Room Equipment
        $meetingEquipment = $facilityCategory->children()->create([
            'name' => 'Meeting Room Equipment',
            'code' => 'FAC-MEET',
            'description' => 'Conference and meeting room equipment',
            'icon' => 'presentation-chart-bar',
            'color' => '#0891b2',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        $meetingEquipment->children()->createMany([
            [
                'name' => 'Conference Tables',
                'code' => 'FAC-MEET-TABLE',
                'description' => 'Meeting and conference tables',
                'icon' => 'table',
                'color' => '#0284c7',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'Projectors',
                'code' => 'FAC-MEET-PROJ',
                'description' => 'Meeting room projectors',
                'icon' => 'projector',
                'color' => '#2563eb',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'Whiteboards',
                'code' => 'FAC-MEET-WHITE',
                'description' => 'Whiteboards and presentation boards',
                'icon' => 'presentation-chart-line',
                'color' => '#4f46e5',
                'company_id' => $company->id,
                'is_active' => true,
            ],
        ]);

        // Office Appliances
        $appliances = $facilityCategory->children()->create([
            'name' => 'Office Appliances',
            'code' => 'FAC-APP',
            'description' => 'Kitchen and break room appliances',
            'icon' => 'utensils',
            'color' => '#7c3aed',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        $appliances->children()->createMany([
            [
                'name' => 'Coffee Machines',
                'code' => 'FAC-APP-COFFEE',
                'description' => 'Coffee makers and espresso machines',
                'icon' => 'coffee',
                'color' => '#8b5cf6',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'Refrigerators',
                'code' => 'FAC-APP-FRIDGE',
                'description' => 'Office refrigerators and mini-fridges',
                'icon' => 'refrigerator',
                'color' => '#a855f7',
                'company_id' => $company->id,
                'is_active' => true,
            ],
            [
                'name' => 'Microwaves',
                'code' => 'FAC-APP-MICRO',
                'description' => 'Microwave ovens',
                'icon' => 'microwave',
                'color' => '#c084fc',
                'company_id' => $company->id,
                'is_active' => true,
            ],
        ]);
    }
} 