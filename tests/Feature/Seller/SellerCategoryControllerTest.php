<?php

namespace Tests\Feature\Seller;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerCategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setup(): void
    {
        parent::setup();
    }

    public function test_index(): void
    {
        $seller = User::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'seller_id' => $seller->id,
        ]);

        $product->categories()->attach($category->id);

        $response = $this->getJson(route('sellers.categories.index', $seller->encrypted_id));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
            ],
            'links',
            'meta',
        ]);
    }

    public function test_index_with_unique_parameter(): void
    {
        $seller = User::factory()->create();
        $category = Category::factory()->create();

        // 2 products both in the same category — without unique: 2 entries, with unique: 1 entry
        Product::factory()->count(2)->create([
            'seller_id' => $seller->id,
        ])->each(function ($product) use ($category) {
            $product->categories()->attach($category->id);
        });

        $response = $this->getJson(route('sellers.categories.index', $seller->encrypted_id).'?unique=true');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }
}
