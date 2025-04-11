<?php

namespace Tests\Feature\Buyer;

use App\Models\User;
use App\Models\Buyer;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;
use Tests\TestCase;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BuyerCategoryControllerTest extends TestCase
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
    public function testindex()
    {
        $numberOfCategories = 5;
        $numberOfUsers = 6;

        Category::factory()->count($numberOfCategories)->create();

        /**
         * ? User 1 to 5 are Sellers and each Seller has 1 Product listed which further belongs to multiple Categories
         * ? User 6 becomes a Buyer, by purchasing one Product being sold by a randomly selected Seller in a Transaction 
         */
        // 
        $buyerId = null;                                // Captured Buyer id
        $categoriesAttachedToEachProduct = collect();   // Collection to hold the  number of categories attached to each product
        $randomProductId = null;
        User::factory()->count($numberOfUsers)->create()
            ->each(function($user) use ($numberOfUsers, $numberOfCategories, &$buyerId, &$categoriesAttachedToEachProduct, &$randomProductId) {    // Passing $buyerId and $categoriesAttachedToEachProduct as reference
                if($user->id < $numberOfUsers) {
                    Product::factory()->count($this->faker->numberBetween(1, 3))->create([
                        'seller_id' => $user->id,
                        'quantity' => 20,
                        'status' => "available",
                    ])->each(function($product) use($numberOfCategories, &$categoriesAttachedToEachProduct) {
                        $categories = Category::all()->random(mt_rand(1, $numberOfCategories))->pluck('id');
                        $categoriesCount = $categories->count(); 
                        $categoriesAttachedToEachProduct->push($categoriesCount);
                        $product->categories()->attach($categories);
                    });
                } else {
                    $buyerId = $user->id;   // Capturing the Buyer id
                    $seller = Seller::has('products')->get()->random();
                    $randomProductId = $seller->products->random()->id;
                    
                    // factory(Transaction::class, 1)->create([
                    Transaction::factory()->count(1)->create([
                        "quantity" => $this->faker->numberBetween(1, 3),
                        "buyer_id" => $user->id,
                        "product_id" => $randomProductId,     
                    ]);  
                }
            }
        );

        $categoriesCount = $categoriesAttachedToEachProduct[$randomProductId - 1];

        $response = $this->getJson(route('buyers.categories.index', $buyerId));

        $response->assertStatus(200);
        $response->assertJsonCount($categoriesCount, $key = "data");
        $response->assertJsonStructure([
            "data" => [
                "*" => [
                    "id",
                    "name",
                    "description",
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
