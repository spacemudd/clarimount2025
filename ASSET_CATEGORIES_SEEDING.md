# Asset Categories Seeding System

This system automatically seeds comprehensive asset categories for both IT devices and office facilities when a new user registers.

## How It Works

### 1. User Registration Process
When a new user registers, the following happens automatically:

1. **User Registration**: User completes the registration form
2. **Event Triggered**: Laravel fires the `Registered` event
3. **Event Listener**: `CreateDefaultCompanyAndAssetCategories` listener handles the event
4. **Company Creation**: A default company is created for the user
5. **Asset Categories Seeding**: The `AssetCategorySeederService` seeds default categories

### 2. Default Company Creation
Each new user gets a default company with:
- **English Name**: `{User Name}'s Company`
- **Arabic Name**: `شركة {User Name}`
- **Company Email**: User's email address
- **Auto-created Flag**: Settings include `auto_created: true`

### 3. Asset Categories Structure

The system creates comprehensive hierarchical categories:

#### IT Equipment Categories
```
IT Equipment
└── End-User Devices
    ├── Laptops
    │   ├── Dell Laptops
    │   ├── HP Laptops
    │   └── MacBooks
    ├── Desktop Computers
    │   ├── Dell Desktops
    │   ├── HP Desktops
    │   └── iMacs
    ├── Mobile Devices
    │   ├── Smartphones
    │   └── Tablets
    └── Peripherals
        ├── Monitors
        ├── Keyboards
        ├── Mice
        └── Printers
├── Network Equipment
│   ├── Switches
│   ├── Routers
│   └── Access Points
└── Servers
    ├── Physical Servers
    └── Virtual Servers
```

#### Facility & Furniture Categories
```
Facility & Furniture
├── Office Furniture
│   ├── Desks & Workstations
│   │   ├── Standing Desks
│   │   └── Regular Desks
│   ├── Chairs & Seating
│   │   ├── Office Chairs
│   │   └── Meeting Chairs
│   └── Storage & Organization
│       ├── Filing Cabinets
│       └── Bookshelves
├── Meeting Room Equipment
│   ├── Conference Tables
│   ├── Projectors
│   └── Whiteboards
└── Office Appliances
    ├── Coffee Machines
    ├── Refrigerators
    └── Microwaves
```

## Key Features

### 1. Hierarchical Structure
- Uses Laravel Nested Set Model for efficient tree operations
- Supports unlimited nesting levels
- Efficient querying of category trees

### 2. Color-Coded Categories
- Each category has a unique color for visual organization
- Brand-specific colors (e.g., Dell blue, HP cyan, Apple black)
- Consistent color schemes across related categories

### 3. Icon Integration
- Each category includes appropriate icons
- Icons are based on Lucide icon names
- Consistent iconography across the system

### 4. Multi-language Support
- Category names and descriptions support internationalization
- Default descriptions in English
- Ready for Arabic translations

### 5. Company Scoping
- All categories are scoped to specific companies
- No cross-company data leakage
- Each company gets its own category tree

## Manual Usage

### Command Line Tool
You can manually seed categories using the Artisan command:

```bash
# Seed categories for a specific company
php artisan seed:asset-categories {company_id}

# Interactive mode - choose from available companies
php artisan seed:asset-categories
```

### Programmatic Usage
```php
use App\Services\AssetCategorySeederService;
use App\Models\Company;

$seeder = app(AssetCategorySeederService::class);
$company = Company::find(1);
$seeder->seedDefaultCategories($company);
```

## Service Class: `AssetCategorySeederService`

### Main Method
- `seedDefaultCategories(Company $company)`: Seeds both IT and facility categories

### Private Methods
- `seedITCategories(Company $company)`: Seeds IT equipment categories
- `seedFacilityCategories(Company $company)`: Seeds facility and furniture categories

### Safety Features
- **Duplicate Prevention**: Checks if categories already exist before seeding
- **Company Scoping**: All categories are properly scoped to the company
- **Transactional Safety**: Uses database transactions for consistency

## Event Listener: `CreateDefaultCompanyAndAssetCategories`

### Features
- **Queued Processing**: Implements `ShouldQueue` for background processing
- **Duplicate Prevention**: Checks if user already has companies
- **Auto-naming**: Generates appropriate company names
- **Comprehensive Seeding**: Seeds both company and categories

### Event Registration
Registered in `AppServiceProvider::boot()`:
```php
Event::listen(
    Registered::class,
    CreateDefaultCompanyAndAssetCategories::class
);
```

## Customization

### Adding New Categories
To add new categories, modify the `AssetCategorySeederService`:

1. **Add to IT Categories**: Edit `seedITCategories()` method
2. **Add to Facility Categories**: Edit `seedFacilityCategories()` method
3. **Create New Main Category**: Add new method and call from `seedDefaultCategories()`

### Customizing Colors and Icons
Each category definition includes:
```php
[
    'name' => 'Category Name',
    'code' => 'CATEGORY-CODE',
    'description' => 'Category description',
    'icon' => 'lucide-icon-name',
    'color' => '#hex-color',
    'company_id' => $company->id,
    'is_active' => true,
]
```

### Example: Adding Security Equipment
```php
// Add to seedFacilityCategories method
$security = $facilityCategory->children()->create([
    'name' => 'Security Equipment',
    'code' => 'FAC-SEC',
    'description' => 'Security cameras, access control, and monitoring equipment',
    'icon' => 'shield',
    'color' => '#dc2626',
    'company_id' => $company->id,
    'is_active' => true,
]);

$security->children()->createMany([
    [
        'name' => 'Security Cameras',
        'code' => 'FAC-SEC-CAM',
        'description' => 'CCTV and IP cameras',
        'icon' => 'camera',
        'color' => '#b91c1c',
        'company_id' => $company->id,
        'is_active' => true,
    ],
    // ... more security equipment
]);
```

## Testing

### Testing Registration Flow
1. Register a new user through the web interface
2. Check that a company was created
3. Verify asset categories were seeded
4. Test the category tree structure

### Testing Command
```bash
# Test with existing company
php artisan seed:asset-categories 1

# Test interactive mode
php artisan seed:asset-categories
```

### Database Verification
```sql
-- Check categories were created
SELECT name, code, depth, parent_id FROM asset_categories WHERE company_id = 1;

-- Check hierarchical structure
SELECT name, _lft, _rgt, depth FROM asset_categories WHERE company_id = 1 ORDER BY _lft;
```

This comprehensive seeding system ensures that every new user starts with a well-organized, professional asset management structure covering both IT infrastructure and office facilities. 