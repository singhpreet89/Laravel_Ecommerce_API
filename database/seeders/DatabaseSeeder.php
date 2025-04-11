<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Visual feedback
        echo "\n\e[0;32m**********************************************\e[0m";
        echo "\n             \e[1;33mSEEDING IN PROGRESS\e[0m\n";
        echo "\e[0;31mNOTE: Seeding can take between 1 to 3 minutes.\e[0m";
        echo "\n\e[0;32m**********************************************\e[0m\n";

        $users = 1000;
        $numberOfCategories = 30;
        $numberOfProducts = 1000;
        $numberOfTransactions = 1000;

        echo "\n\e[1;33mSeeding: 'users' table\e[0m";
        User::factory($users)->create();
        echo "\n\e[0;32mSeeded: 'users' table\e[0m";

        echo "\n\e[1;33mSeeding: 'categories' table\e[0m";
        Category::factory($numberOfCategories)->create();
        echo "\n\e[0;32mSeeded: 'categories' table\e[0m";

        echo "\n\e[1;33mSeeding: 'products' table\e[0m";
        Product::factory($numberOfProducts)->create()->each(function ($product) {
            // Assign random 1–5 categories to the product
            $categories = Category::inRandomOrder()
                ->take(mt_rand(1, 5))
                ->pluck('id');

            $product->categories()->attach($categories);
        });
        echo "\n\e[0;32mSeeded: 'products' table\e[0m";

        echo "\n\e[1;33mSeeding: 'transactions' table\e[0m";
        Transaction::factory($numberOfTransactions)->create();
        echo "\n\e[0;32mSeeded: 'transactions' table\e[0m\n\n";
    }
}

