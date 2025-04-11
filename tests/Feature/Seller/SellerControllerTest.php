<?php

namespace Tests\Feature\Seller;

use App\Models\User;
use App\Models\Seller;
use App\Models\Product;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SellerControllerTest extends TestCase
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
        $numberOfUsers = 20;

        // Each created user might have purchased a product or not rand(0, 2) ensures that 
        User::factory()->count($numberOfUsers)->create()->each(function ($user) {
            Product::factory()->count(rand(0, 2))->create([
                "seller_id" => $user->id,
            ]);
        });
        
        $totalSellers = Seller::has('products')->count();

        $response = $this->getJson(route('sellers.index'));

        $response->assertOk();
        $response->assertJsonCount($totalSellers, $key = "data");
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
        Product::factory()->create([
            "seller_id" => $seller->id,
        ]);

        $response = $this->getJson(route('sellers.show', $seller->id));

        $response->assertOk();
        $response->assertJson([
            "data" => [
                "id" => $seller->id,
                "name" => $seller->name,
                "email" => $seller->email,
                "verified" => (int) $seller->verified,
                'created_at' => $seller->created_at,
                'updated_at' => $seller->updated_at,
                'deleted_at' => null,
            ]
        ]);
    }
}
