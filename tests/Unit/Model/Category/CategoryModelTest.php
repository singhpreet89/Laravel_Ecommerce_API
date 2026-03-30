<?php

namespace Tests\Unit\Model\Category;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_create(): void
    {
        $category = Category::factory()->create();
        $saved = Category::find($category->id);

        $this->assertEquals($category->name, $saved->name);
        $this->assertEquals($category->description, $saved->description);
        $this->assertNull($saved->deleted_at);
    }

    public function test_soft_deletes(): void
    {
        $category = Category::factory()->create();
        $category->delete();

        $this->assertSoftDeleted($category);
        $this->assertCount(0, Category::all());

        $trashed = Category::withTrashed()->find($category->id);
        $this->assertNotNull($trashed->deleted_at);
    }

    public function test_products_relationship(): void
    {
        $seller = User::factory()->create();
        $category = Category::factory()->create();

        $products = Product::factory()->count(2)->create(['seller_id' => $seller->id]);
        $category->products()->attach($products->pluck('id')->toArray());

        $this->assertCount(2, $category->products);
        $this->assertInstanceOf(Product::class, $category->products->first());
    }

    public function test_encrypted_id_attribute(): void
    {
        $category = Category::factory()->create();

        $this->assertNotNull($category->encrypted_id);
        $this->assertNotEquals($category->id, $category->encrypted_id);
        $this->assertEquals((string) $category->id, encryptDecrypt($category->encrypted_id, 'decrypt'));
    }
}
