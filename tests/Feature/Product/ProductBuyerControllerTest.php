<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductBuyerControllerTest extends TestCase
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

        Transaction::factory()->count(2)->create([
            'buyer_id' => $buyer->id,
            'product_id' => $product->id,
        ]);

        $response = $this->getJson(route('products.buyers.index', $product->encrypted_id));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
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
}
