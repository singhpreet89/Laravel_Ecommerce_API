<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setup(): void
    {
        parent::setup();
    }

    /**
     * @return void
     */
    public function test_index()
    {
        $numberOfUsers = 5;
        $numberOfProducts = 19;

        User::factory()->count($numberOfUsers)->create();
        Product::factory()->count($numberOfProducts)->create([
            'seller_id' => rand(1, $numberOfUsers),
        ]);

        $response = $this->getJson(route('products.index'));

        $response->assertOk();
        $response->assertJsonCount($numberOfProducts, $key = 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'seller_id',
                    'name',
                    'description',
                    'quantity',
                    'status',
                    'image',
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

    /**
     * @return void
     */
    public function test_show()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'seller_id' => $user->id,
        ]);

        $response = $this->getJson(route('products.show', $product->encrypted_id));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'id' => $product->encrypted_id,
                'seller_id' => $product->seller_id,
                'name' => $product->name,
                'description' => $product->description,
                'quantity' => $product->quantity,
                'status' => $product->status,
                'image' => url("img/{$product->image}"),
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
                'deleted_at' => null,
            ],
        ]);
    }
}
