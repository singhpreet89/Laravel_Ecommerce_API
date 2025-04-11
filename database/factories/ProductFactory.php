<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->sentence(6, true),
            'seller_id' => User::query()->inRandomOrder()->value('id'),
            'description' => fake()->paragraph(1),
            'quantity' => fake()->numberBetween(1, 10),
            'status' => fake()->randomElement([
                Product::AVAILABLE_PRODUCT,
                Product::UNAVAILABLE_PRODUCT
            ]),
            'image' => fake()->randomElement(['1.jpg', '2.jpg', '3.jpg']),
        ];
    }
}

