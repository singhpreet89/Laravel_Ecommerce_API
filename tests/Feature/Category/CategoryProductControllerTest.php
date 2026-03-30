<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryProductControllerTest extends TestCase
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

        $products = Product::factory()->count(3)->create([
            'seller_id' => $seller->id,
        ]);

        foreach ($products as $product) {
            $product->categories()->attach($category->id);
        }

        $response = $this->getJson(route('categories.products.index', $category->encrypted_id));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
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
                ],
            ],
            'links',
            'meta',
        ]);
    }
}
