<?php

namespace Tests\Feature\Buyer;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BuyerSellerControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    /**
     * @return void
     */
    public function test_index()
    {
        $numberOfCategories = 5;
        $numberOfUsers = 6;

        Category::factory()->count($numberOfCategories)->create();

        /**
         * ? User 1 to 5 are Sellers and each Seller has 1 Product listed which further belongs to multiple Categories
         * ? User 6 becomes a Buyer, by purchasing one Product being sold by a randomly selected Seller in a Transaction
         */
        //
        $buyer = null;                                  // Captured Buyer
        $productsPurchasedFromNumberOfSellers = 0;

        User::factory()->count($numberOfUsers)->create()
            ->each(function ($user) use ($numberOfUsers, $numberOfCategories, &$buyer, &$productsPurchasedFromNumberOfSellers) {    // Passing $buyer and $categoriesAttachedToEachProduct as reference

                if ($user->id < $numberOfUsers) {
                    Product::factory()->count($this->faker->numberBetween(1, 5))->create([
                        'seller_id' => $user->id,
                        'quantity' => 20,
                        'status' => 'available',
                    ])->each(function ($product) use ($numberOfCategories) {
                        $categories = Category::all()->random(mt_rand(1, $numberOfCategories))->pluck('id');
                        $product->categories()->attach($categories);
                    });
                } else {
                    $buyer = $user;   // Capturing the Buyer
                    $sellers = Seller::has('products')->get()->random(mt_rand(1, $numberOfUsers - 1));
                    $productsPurchasedFromNumberOfSellers = $sellers->count();

                    $sellers->each(function ($seller) use ($user, &$totalPurchasedProducts) {   // Because all the users are Sellers except 1 which is a Buyer
                        $randomProductId = $seller->products->random()->id;

                        Transaction::factory()->create([
                            'quantity' => $this->faker->numberBetween(1, 3),
                            'buyer_id' => $user->id,
                            'product_id' => $randomProductId,
                        ]);
                    });
                }
            }
            );

        $response = $this->getJson(route('buyers.sellers.index', $buyer->encrypted_id));

        // Also test the unique=true branch (covers BuyerSellerController line 41)
        $uniqueResponse = $this->getJson(route('buyers.sellers.index', $buyer->encrypted_id).'?unique=true');
        $uniqueResponse->assertStatus(200);

        $response->assertStatus(200);
        $response->assertJsonCount($productsPurchasedFromNumberOfSellers, 'data');
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
                    'links' => [
                        '*' => [
                            'rel',
                            'href',
                        ],
                    ],
                ],
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'path',
                'per_page',
                'to',
                'total',
            ],
        ]);
    }
}
