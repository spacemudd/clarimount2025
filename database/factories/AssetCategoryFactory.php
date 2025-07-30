<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssetCategory>
 */
class AssetCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_en' => $this->faker->randomElement(['Laptops', 'Desktops', 'Monitors', 'Printers', 'Servers']),
            'name_ar' => $this->faker->randomElement(['أجهزة محمولة', 'أجهزة سطح المكتب', 'شاشات', 'طابعات', 'خوادم']),
            'code' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'description_en' => $this->faker->sentence(),
            'description_ar' => $this->faker->sentence() . ' (Arabic)',
            'company_id' => Company::factory(),
            'parent_id' => null,
            'is_active' => true,
            'settings' => [],
        ];
    }

    /**
     * Indicate that the category is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
} 