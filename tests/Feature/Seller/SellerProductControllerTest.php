<?php

namespace Tests\Feature\Seller;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellerProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setup(): void
    {
        parent::setup();
    }

    public function test_index(): void
    {
        $seller = User::factory()->create();
        Product::factory()->count(3)->create(['seller_id' => $seller->id]);

        $response = $this->getJson(route('sellers.products.index', $seller->encrypted_id));

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

    public function test_store(): void
    {
        Storage::fake('public');

        $seller = User::factory()->create();
        $image = UploadedFile::fake()->create('product.jpg', 100, 'image/jpeg');

        $response = $this->postJson(route('sellers.products.store', $seller->encrypted_id), [
            'name' => 'Test Product',
            'description' => 'A test product description.',
            'quantity' => 5,
            'image' => $image,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.name', 'Test Product');
        $response->assertJsonPath('data.status', Product::UNAVAILABLE_PRODUCT);
        $response->assertJsonPath('data.seller_id', $seller->id);
    }

    public function test_update(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'name' => 'Old Name',
        ]);

        $response = $this->putJson(
            route('sellers.products.update', [$seller->encrypted_id, $product->encrypted_id]),
            ['name' => 'New Name']
        );

        $response->assertOk();
        $response->assertJsonPath('data.name', 'New Name');
    }

    public function test_update_returns_forbidden_when_seller_mismatch(): void
    {
        $seller = User::factory()->create();
        // Give seller at least one product so the Seller global scope resolves correctly
        Product::factory()->create(['seller_id' => $seller->id]);
        $otherSeller = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $otherSeller->id]);

        $response = $this->putJson(
            route('sellers.products.update', [$seller->encrypted_id, $product->encrypted_id]),
            ['name' => 'Hacked Name']
        );

        $response->assertForbidden();
        $response->assertJsonPath('errors.seller.0', 'The product can only be updated by the original Seller.');
    }

    public function test_update_returns_conflict_when_available_with_no_category(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => Product::UNAVAILABLE_PRODUCT,
        ]);

        $response = $this->putJson(
            route('sellers.products.update', [$seller->encrypted_id, $product->encrypted_id]),
            ['status' => Product::AVAILABLE_PRODUCT]
        );

        $response->assertConflict();
        $response->assertJsonPath('errors.category.0', 'An available product must belong to atleast one Category.');
    }

    public function test_update_status_to_available_with_category(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => Product::UNAVAILABLE_PRODUCT,
        ]);
        $category = Category::factory()->create();
        $product->categories()->attach($category->id);

        $response = $this->putJson(
            route('sellers.products.update', [$seller->encrypted_id, $product->encrypted_id]),
            ['status' => Product::AVAILABLE_PRODUCT]
        );

        $response->assertOk();
        $response->assertJsonPath('data.status', Product::AVAILABLE_PRODUCT);
    }

    public function test_update_with_new_image(): void
    {
        Storage::fake('public');

        $seller = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);
        $image = UploadedFile::fake()->create('new_product.jpg', 100, 'image/jpeg');

        $response = $this->putJson(
            route('sellers.products.update', [$seller->encrypted_id, $product->encrypted_id]),
            ['image' => $image]
        );

        $response->assertOk();
    }

    public function test_destroy(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);

        $response = $this->deleteJson(
            route('sellers.products.destroy', [$seller->encrypted_id, $product->encrypted_id])
        );

        $response->assertOk();
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_destroy_returns_forbidden_when_seller_mismatch(): void
    {
        $seller = User::factory()->create();
        // Give seller at least one product so the Seller global scope resolves correctly
        Product::factory()->create(['seller_id' => $seller->id]);
        $otherSeller = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $otherSeller->id]);

        $response = $this->deleteJson(
            route('sellers.products.destroy', [$seller->encrypted_id, $product->encrypted_id])
        );

        $response->assertForbidden();
        $response->assertJsonPath('errors.seller.0', 'The product can only be deleted by the original Seller.');
    }
}
