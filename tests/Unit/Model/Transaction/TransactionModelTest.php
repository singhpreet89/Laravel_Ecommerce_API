<?php

namespace Tests\Unit\Model\Transaction;

use App\Models\Buyer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_create(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);

        $transaction = Transaction::factory()->create([
            'buyer_id' => $buyer->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $saved = Transaction::find($transaction->id);

        $this->assertEquals($transaction->quantity, $saved->quantity);
        $this->assertEquals($buyer->id, $saved->buyer_id);
        $this->assertEquals($product->id, $saved->product_id);
        $this->assertNull($saved->deleted_at);
    }

    public function test_soft_deletes(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);

        $transaction = Transaction::factory()->create([
            'buyer_id' => $buyer->id,
            'product_id' => $product->id,
        ]);

        $transaction->delete();

        $this->assertSoftDeleted($transaction);
        $this->assertCount(0, Transaction::all());
    }

    public function test_buyer_relationship(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);

        $transaction = Transaction::factory()->create([
            'buyer_id' => $buyer->id,
            'product_id' => $product->id,
        ]);

        $this->assertEquals($buyer->id, $transaction->buyer->id);
        $this->assertInstanceOf(Buyer::class, $transaction->buyer);
    }

    public function test_product_relationship(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);

        $transaction = Transaction::factory()->create([
            'buyer_id' => $buyer->id,
            'product_id' => $product->id,
        ]);

        $this->assertEquals($product->id, $transaction->product->id);
        $this->assertInstanceOf(Product::class, $transaction->product);
    }

    public function test_encrypted_id_attribute(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);

        $transaction = Transaction::factory()->create([
            'buyer_id' => $buyer->id,
            'product_id' => $product->id,
        ]);

        $this->assertNotNull($transaction->encrypted_id);
        $this->assertNotEquals($transaction->id, $transaction->encrypted_id);
        $this->assertEquals((string) $transaction->id, encryptDecrypt($transaction->encrypted_id, 'decrypt'));
    }
}
