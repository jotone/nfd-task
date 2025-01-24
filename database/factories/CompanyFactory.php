<?php

namespace Database\Factories;

use App\Models\Company;
use App\Services\NipService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
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
        return [
            'name'    => fake()->company(),
            'tax_id'  => NipService::make()->generate(),
            'address' => fake()->streetAddress(),
            'city'    => fake()->city(),
            'zip'     => fake()->postcode(),
        ];
    }
}
