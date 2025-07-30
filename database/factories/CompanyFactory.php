<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();
        
        return [
            'name_en' => $name,
            'name_ar' => $name . ' (Arabic)',
            'slug' => Str::slug($name),
            'company_email' => $this->faker->unique()->companyEmail(),
            'description_en' => $this->faker->paragraph(),
            'description_ar' => $this->faker->paragraph() . ' (Arabic)',
            'website' => $this->faker->url(),
            'owner_id' => User::factory(),
            'is_active' => true,
            'settings' => [],
        ];
    }

    /**
     * Indicate that the company is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
} 