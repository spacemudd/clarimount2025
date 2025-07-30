<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Location;
use App\Models\AssetCategory;
use App\Models\AssetTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_tag' => 'TEST-' . $this->faker->unique()->numberBetween(100, 999),
            'serial_number' => $this->faker->unique()->regexify('[A-Z0-9]{10}'),
            'service_tag_number' => $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'finance_tag_number' => $this->faker->unique()->regexify('[A-Z0-9]{6}'),
            'asset_category_id' => AssetCategory::factory(),
            'asset_template_id' => AssetTemplate::factory(),
            'location_id' => Location::factory(),
            'company_id' => Company::factory(),
            'assigned_to' => null,
            'assigned_date' => null,
            'model_name' => $this->faker->randomElement(['Dell Latitude', 'HP EliteBook', 'MacBook Pro', 'ThinkPad']),
            'model_number' => $this->faker->regexify('[A-Z0-9]{6}'),
            'manufacturer' => $this->faker->randomElement(['Dell', 'HP', 'Apple', 'Lenovo']),
            'status' => $this->faker->randomElement(['available', 'assigned', 'maintenance', 'retired']),
            'condition' => $this->faker->randomElement(['excellent', 'good', 'fair', 'poor']),
            'notes' => $this->faker->optional()->sentence(),
            'image_path' => null,
        ];
    }

    /**
     * Indicate that the asset is assigned.
     */
    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'assigned',
            'assigned_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the asset is available.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'available',
            'assigned_to' => null,
            'assigned_date' => null,
        ]);
    }
} 