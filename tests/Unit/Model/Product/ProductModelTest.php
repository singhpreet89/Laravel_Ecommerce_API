<?php

namespace Tests\Unit\Model\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_create(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);
        $saved = Product::find($product->id);

        $this->assertEquals($product->name, $saved->name);
        $this->assertEquals($product->seller_id, $saved->seller_id);
        $this->assertNull($saved->deleted_at);
    }

    public function test_soft_deletes(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);
        $product->delete();

        $this->assertSoftDeleted($product);
        $this->assertCount(0, Product::all());
    }

    public function test_is_available(): void
    {
        $seller = User::factory()->create();

        $available = Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => Product::AVAILABLE_PRODUCT,
        ]);

        $unavailable = Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => Product::UNAVAILABLE_PRODUCT,
        ]);

        $this->assertTrue($available->isAvailable());
        $this->assertFalse($unavailable->isAvailable());
    }

    public function test_seller_relationship(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);

        $this->assertEquals($seller->id, $product->seller->id);
    }

    public function test_categories_relationship(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);
        $categories = Category::factory()->count(2)->create();
        $product->categories()->attach($categories->pluck('id')->toArray());

        $this->assertCount(2, $product->categories);
    }

    public function test_transactions_relationship(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);

        Transaction::factory()->count(3)->create([
            'buyer_id' => $buyer->id,
            'product_id' => $product->id,
        ]);

        $this->assertCount(3, $product->transactions);
    }

    public function test_encrypted_id_attribute(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);

        $this->assertNotNull($product->encrypted_id);
        $this->assertNotEquals($product->id, $product->encrypted_id);
        $this->assertEquals((string) $product->id, encryptDecrypt($product->encrypted_id, 'decrypt'));
    }
}
