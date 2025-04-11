<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        // Pick a seller who has products
        $seller = Seller::has('products')->inRandomOrder()->first();

        // Pick a buyer who is not the seller
        $buyer = User::where('id', '!=', $seller->id)->inRandomOrder()->first();

        return [
            'quantity' => fake()->numberBetween(1, 3),
            'buyer_id' => $buyer->id,
            'product_id' => $seller->products()->inRandomOrder()->first()->id,
        ];
    }
}

