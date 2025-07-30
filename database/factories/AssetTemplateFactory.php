<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\AssetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssetTemplate>
 */
class AssetTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_en' => $this->faker->randomElement(['Standard Laptop', 'High-Performance Desktop', 'Basic Monitor', 'Network Printer']),
            'name_ar' => $this->faker->randomElement(['لابتوب قياسي', 'كمبيوتر عالي الأداء', 'شاشة أساسية', 'طابعة شبكة']),
            'code' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'description_en' => $this->faker->sentence(),
            'description_ar' => $this->faker->sentence() . ' (Arabic)',
            'company_id' => Company::factory(),
            'asset_category_id' => AssetCategory::factory(),
            'manufacturer' => $this->faker->randomElement(['Dell', 'HP', 'Apple', 'Lenovo']),
            'model_name' => $this->faker->randomElement(['Latitude 5520', 'EliteBook 840', 'MacBook Pro', 'ThinkPad X1']),
            'model_number' => $this->faker->regexify('[A-Z0-9]{6}'),
            'specifications' => [
                'processor' => $this->faker->randomElement(['Intel i5', 'Intel i7', 'AMD Ryzen 5', 'AMD Ryzen 7']),
                'memory' => $this->faker->randomElement(['8GB', '16GB', '32GB']),
                'storage' => $this->faker->randomElement(['256GB SSD', '512GB SSD', '1TB SSD']),
            ],
            'is_active' => true,
            'settings' => [],
        ];
    }

    /**
     * Indicate that the template is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
} 