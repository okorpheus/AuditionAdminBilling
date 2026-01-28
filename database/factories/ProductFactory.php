<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'sku' => $this->faker->unique()->bothify('???-####'),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(1000, 50000),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }
}