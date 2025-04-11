<?php

namespace Tests\Feature\Buyer;

use App\Models\User;
use App\Models\Product;
use Tests\TestCase;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BuyerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();


    }

    /**
     *
     * @return void
     */
    public function testIndex()
    { 
        $numberOfSellers = 20;
        $numberOfProducts = 10;
        $numberOfBuyers = 15;
        $numberOfTransactions = 10;

        User::factory()->count($numberOfSellers)->create();
        Product::factory()->count($numberOfProducts)->create();
        User::factory()->count($numberOfBuyers)->create();
        Transaction::factory()->count($numberOfTransactions)->create();
   
        $response = $this->getJson(route('buyers.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "*" => [
                    "id",
                    "name",
                    "email",
                    "verified",
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
        $seller = User::factory()->create();
        Product::factory()->count(2)->create();
        $buyer = User::factory()->create();
        Transaction::factory()->create([
            'buyer_id' => $buyer->id,
        ]);
   
        $response = $this->getJson(route('buyers.show', $buyer->id));

        $response->assertOk();
        $response->assertJson([
            "data" => [
                "id" => $buyer->id,
                "name" => $buyer->name,
                "email" => $buyer->email,
                "verified" => (int) $buyer->verified,
                'created_at' => isset($buyer->created_at) ? (string) $buyer->created_at : null,
                'updated_at' => isset($buyer->updated_at) ? (string) $buyer->updated_at : null,
                'deleted_at' => isset($buyer->deleted_at) ? (string) $buyer->deleted_at : null,
            ]
        ]);
    }
}
