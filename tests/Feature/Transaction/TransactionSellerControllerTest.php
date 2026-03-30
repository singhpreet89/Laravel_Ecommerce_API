<?php

namespace Tests\Feature\Transaction;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionSellerControllerTest extends TestCase
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
            'quantity' => 5,
        ]);

        $transaction = Transaction::factory()->create([
            'buyer_id' => $buyer->id,
            'product_id' => $product->id,
        ]);

        $response = $this->getJson(route('transactions.sellers.index', $transaction->encrypted_id));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'verified',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ]);

        $response->assertJson([
            'data' => [
                'id' => $seller->encrypted_id,
                'name' => $seller->name,
                'email' => $seller->email,
            ],
        ]);
    }
}
