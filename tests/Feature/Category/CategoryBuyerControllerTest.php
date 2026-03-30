<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryBuyerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setup(): void
    {
        parent::setup();
    }

    public function test_index(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'quantity' => 10,
        ]);

        $product->categories()->attach($category->id);

        Transaction::factory()->count(3)->create([
            'buyer_id' => $buyer->id,
            'product_id' => $product->id,
        ]);

        $response = $this->getJson(route('categories.buyers.index', $category->encrypted_id));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'verified',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
            ],
            'links',
            'meta',
        ]);
    }

    public function test_index_with_unique_parameter(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'quantity' => 10,
        ]);

        $product->categories()->attach($category->id);

        // Same buyer makes 3 transactions — without unique: 3 entries, with unique: 1 entry
        Transaction::factory()->count(3)->create([
            'buyer_id' => $buyer->id,
            'product_id' => $product->id,
        ]);

        $response = $this->getJson(route('categories.buyers.index', $category->encrypted_id).'?unique=true');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }
}
