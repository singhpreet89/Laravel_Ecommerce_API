<?php

namespace Tests\Feature\Buyer;

use App\Models\User;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;
use Tests\TestCase;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class BuyerTransactionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    /**
     *
     * @return void
     */
    public function testIndex()
    {
        $numberOfCategories = 5;
        $numberOfUsers = 7;

        Category::factory()->count($numberOfCategories)->create();

        /**
         * ? User 1 to 5 are Sellers and each Seller has 1 Product listed which further belongs to multiple Categories
         * ? User 6 becomes a Buyer, by purchasing one Product being sold by a randomly selected Seller in a Transaction 
         */
        // 
        $buyerId = null;                                // Captured Buyer id
        
        User::factory()->count($numberOfUsers)->create()
            ->each(function($user) use ($numberOfUsers, $numberOfCategories, &$buyerId) {    // Passing $buyerId and $categoriesAttachedToEachProduct as reference
                
                if($user->id < $numberOfUsers) {
                    Product::factory()->count($this->faker->numberBetween(1, 5))->create([
                        'seller_id' => $user->id,
                        'quantity' => 20,
                        'status' => "available",
                    ])->each(function($product) use($numberOfCategories) {
                        $categories = Category::all()->random(mt_rand(1, $numberOfCategories))->pluck('id');
                        $product->categories()->attach($categories);
                    });
                } else {
                    $buyerId = $user->id;   // Capturing the Buyer id
                    $sellers = Seller::has('products')->get()->random(mt_rand(1, $numberOfUsers - 1));

                    $sellers->each(function($seller) use($user) {   // Because all the users are Sellers except 1 which is a Buyer
                        $randomProductId = $seller->products->random()->id;

                        Transaction::factory()->create([
                            "quantity" => $this->faker->numberBetween(1, 3),
                            "buyer_id" => $user->id,
                            "product_id" => $randomProductId,     
                        ]);
                    });  
                }
            }
        );

        $response = $this->getJson(route('buyers.transactions.index', $buyerId));

        $response->assertStatus(200);
        $response->assertJsonCount(Transaction::all()->count(), $key = "data");
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
}
