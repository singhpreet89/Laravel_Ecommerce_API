<?php

namespace Tests\Feature\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setup(): void
    {
        parent::setup();
    }

    public function test_index(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);
        $categories = Category::factory()->count(2)->create();
        $product->categories()->attach($categories->pluck('id')->toArray());

        $response = $this->getJson(route('products.categories.index', $product->encrypted_id));

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
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

    public function test_update(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);
        $category = Category::factory()->create();

        $response = $this->putJson(
            route('products.categories.update', [$product->encrypted_id, $category->encrypted_id])
        );

        $response->assertOk();
        $this->assertDatabaseHas('category_product', [
            'product_id' => $product->id,
            'category_id' => $category->id,
        ]);
    }

    /**
     * Attaching same category twice should not create a duplicate.
     */
    public function test_update_is_demote_potent(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);
        $category = Category::factory()->create();
        $product->categories()->attach($category->id);

        $response = $this->putJson(
            route('products.categories.update', [$product->encrypted_id, $category->encrypted_id])
        );

        $response->assertOk();
        $this->assertCount(1, $product->fresh()->categories);
    }

    public function test_destroy(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);
        $category = Category::factory()->create();
        $product->categories()->attach($category->id);

        $response = $this->deleteJson(
            route('products.categories.destroy', [$product->encrypted_id, $category->encrypted_id])
        );

        $response->assertOk();
        $this->assertDatabaseMissing('category_product', [
            'product_id' => $product->id,
            'category_id' => $category->id,
        ]);
    }

    /**
     * Destroying a category that does not belong to the product returns 404.
     */
    public function test_destroy_returns404_when_category_not_attached(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);
        $category = Category::factory()->create();

        $response = $this->deleteJson(
            route('products.categories.destroy', [$product->encrypted_id, $category->encrypted_id])
        );

        $response->assertNotFound();
        $response->assertJsonPath('errors.category.0', 'The specified category does not belong to this product.');
    }
}
