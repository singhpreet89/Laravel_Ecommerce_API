<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTransactionControllerTest extends TestCase
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

        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'quantity' => 10,
        ]);

        Transaction::factory()->count(3)->create([
            'buyer_id' => $buyer->id,
            'product_id' => $product->id,
        ]);

        $response = $this->getJson(route('products.transactions.index', $product->encrypted_id));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
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
