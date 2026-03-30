<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductBuyerTransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setup(): void
    {
        parent::setup();
    }

    private function makeStoreRoute(Product $product, User $buyer): string
    {
        return route('products.buyers.transactions.store', [
            'product' => $product->encrypted_id,
            'buyer' => $buyer->encrypted_id,
        ]);
    }

    /**
     * Happy path: successful transaction creation.
     */
    public function test_store_creates_transaction(): void
    {
        $seller = User::factory()->create(['verified' => User::VERIFIED_USER]);
        $buyer = User::factory()->create(['verified' => User::VERIFIED_USER]);

        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => Product::AVAILABLE_PRODUCT,
            'quantity' => 10,
        ]);

        $response = $this->postJson($this->makeStoreRoute($product, $buyer), ['quantity' => 2]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'quantity',
                'buyer_id',
                'product_id',
            ],
        ]);
        $response->assertJsonPath('data.quantity', 2);
        $this->assertEquals(8, $product->fresh()->quantity);
    }

    /**
     * Buyer cannot be the same as the seller.
     */
    public function test_store_conflict_buyer_is_seller(): void
    {
        $seller = User::factory()->create(['verified' => User::VERIFIED_USER]);

        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => Product::AVAILABLE_PRODUCT,
            'quantity' => 5,
        ]);

        $response = $this->postJson($this->makeStoreRoute($product, $seller), ['quantity' => 1]);

        $response->assertConflict();
        $response->assertJsonPath('errors.buyer.0', 'The Buyer must be different from the Seller.');
    }

    /**
     * Buyer must be verified.
     */
    public function test_store_conflict_buyer_not_verified(): void
    {
        $seller = User::factory()->create(['verified' => User::VERIFIED_USER]);
        $buyer = User::factory()->create(['verified' => User::UNVERIFIED_USER]);

        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => Product::AVAILABLE_PRODUCT,
            'quantity' => 5,
        ]);

        $response = $this->postJson($this->makeStoreRoute($product, $buyer), ['quantity' => 1]);

        $response->assertConflict();
        $response->assertJsonPath('errors.buyer.0', 'The Buyer must be a verified user.');
    }

    /**
     * Seller must be verified.
     */
    public function test_store_conflict_seller_not_verified(): void
    {
        $seller = User::factory()->create(['verified' => User::UNVERIFIED_USER]);
        $buyer = User::factory()->create(['verified' => User::VERIFIED_USER]);

        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => Product::AVAILABLE_PRODUCT,
            'quantity' => 5,
        ]);

        $response = $this->postJson($this->makeStoreRoute($product, $buyer), ['quantity' => 1]);

        $response->assertConflict();
        $response->assertJsonPath('errors.seller.0', 'The Seller must be a verified user.');
    }

    /**
     * Product must be available.
     */
    public function test_store_conflict_product_not_available(): void
    {
        $seller = User::factory()->create(['verified' => User::VERIFIED_USER]);
        $buyer = User::factory()->create(['verified' => User::VERIFIED_USER]);

        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => Product::UNAVAILABLE_PRODUCT,
            'quantity' => 5,
        ]);

        $response = $this->postJson($this->makeStoreRoute($product, $buyer), ['quantity' => 1]);

        $response->assertConflict();
        $response->assertJsonPath('errors.product.0', 'The product is not available.');
    }

    /**
     * Quantity requested cannot exceed product stock.
     */
    public function test_store_conflict_insufficient_quantity(): void
    {
        $seller = User::factory()->create(['verified' => User::VERIFIED_USER]);
        $buyer = User::factory()->create(['verified' => User::VERIFIED_USER]);

        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => Product::AVAILABLE_PRODUCT,
            'quantity' => 2,
        ]);

        $response = $this->postJson($this->makeStoreRoute($product, $buyer), ['quantity' => 10]);

        $response->assertConflict();
        $response->assertJsonPath('errors.product.0', 'The provided does not have anough units available for this transaction.');
    }
}
