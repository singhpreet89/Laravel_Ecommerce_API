<?php

namespace Tests\Feature\Transaction;

use App\Models\User;
use App\Models\Product;
use Tests\TestCase;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setup(): void
    {
        parent::setup();
    }

    /**
     * 
     * @return void
     */
    public function testIndex()
    {
        $numberOfSellers = 5;
        $numberOfBuyers = 10;

        // Creating 3 products per seller, so a total of 15 products
        User::factory()->count($numberOfSellers)->create()->each(function ($user) {
            Product::factory()->count(3)->create([
                "seller_id" => $user->id,
                "quantity" => 5,
            ]);
        });

        // Creating 2 transactions per Buyers, transalting to a total of 20 transactions 
        User::factory()->count($numberOfBuyers)->create()->each(function ($buyer) {
            Transaction::factory()->count(2)->create([
                "buyer_id" => $buyer->id,
                "product_id" => rand(1, 15),
            ]);
        });

        $response = $this->getJson(route('transactions.index'));

        $response->assertOk();
        $response->assertJsonCount(20, $key = "data");
        $response->assertJsonStructure([
            "data" => [
                "*" => [
                    "id",
                    "quantity",
                    "buyer_id",
                    "product_id",
                    "created_at",
                    "updated_at",
                    "deleted_at",
                    "links" => [
                        "*" => [
                            "rel",
                            "href",
                        ]
                    ]
                ]
            ],
            "links" => [
                "first",
                "last",
                "prev",
                "next"
            ],
            "meta" => [
                "current_page",
                "from",
                "last_page",
                "path",
                "per_page",
                "to",
                "total"
            ]
        ]);
    }

    /**
     * 
     * @return void
     */
    public function testShow()
    {
        // Creating 1 product of 1 Seller
        $seller = User::factory()->create();
        $product = Product::factory()->create([
            "seller_id" => $seller->id,
        ]);

        // Creating 1 transactions of 1 Buyers 
        $buyer = User::factory()->create();
        $transaction = Transaction::factory()->create([
            "buyer_id" => $buyer->id,
            "quantity" => 1,
            "product_id" => $product->id,
        ]);

        $response = $this->getJson(route('transactions.show', $transaction->id));

        $response->assertOk();
        $response->assertJson([
            "data" => [
                "id" => $transaction->id,
                "quantity" => $transaction->quantity,
                "buyer_id" => $buyer->id,
                "product_id" => $product->id,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
                'deleted_at' => null,
            ]
        ]);
    }
}
