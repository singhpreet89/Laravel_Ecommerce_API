<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTransactionControllerTest extends TestCase
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
            'status' => Product::AVAILABLE_PRODUCT,
        ]);

        $product->categories()->attach($category->id);

        Transaction::factory()->count(2)->create([
            'buyer_id' => $buyer->id,
            'product_id' => $product->id,
        ]);

        $response = $this->getJson(route('categories.transactions.index', $category->encrypted_id));

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'quantity',
                    'buyer_id',
                    'product_id',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
            ],
            'links',
            'meta',
        ]);
    }
}
