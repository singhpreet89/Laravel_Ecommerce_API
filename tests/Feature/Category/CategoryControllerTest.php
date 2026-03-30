<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
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
        $numberOfCategories = 18;
        Category::factory()->count($numberOfCategories)->create();

        $response = $this->getJson(route('categories.index'));

        $response->assertOk();
        $response->assertJsonCount($numberOfCategories, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
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
        $category = Category::factory()->create();

        $response = $this->getJson(route('categories.show', $category->encrypted_id));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'id' => $category->encrypted_id,
                'name' => $category->name,
                'description' => $category->description,
                'created_at' => (string) $category->created_at,
                'updated_at' => (string) $category->updated_at,
                'deleted_at' => null,
            ],
        ]);
    }

    /**
     * @return void
     */
    public function test_store()
    {
        $payload = [
            'name' => 'Mobile phones',
            'description' => 'All the latest phones launched in Canada',
        ];

        $response = $this->postJson(route('categories.store'), $payload);

        $this->assertDatabaseHas('categories', [
            'name' => $payload['name'],
            'description' => $payload['description'],
        ]);

        $createdCategory = Category::first();

        $response->assertCreated();
        $response->assertJson([
            'data' => [
                'id' => $createdCategory->encrypted_id,
                'name' => $payload['name'],
                'description' => $payload['description'],
                'deleted_at' => null,
            ],
        ]);
    }

    /**
     * @return void
     */
    public function test_update()
    {
        $category = Category::factory()->create([
            'name' => 'Washing machines',
            'description' => 'All the washing machines come under thic category.',
        ]);

        $payload = [
            'name' => 'Alcohal',
            'description' => 'Only top quality spirits.',
        ];

        $response = $this->putJson(route('categories.update', $category->encrypted_id), $payload);

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'id' => $category->encrypted_id,
                'name' => $payload['name'],
                'description' => $payload['description'],
                'deleted_at' => null,
            ],
        ]);
    }

    /**
     * @return void
     */
    public function testdestroy()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson(route('categories.destroy', $category->encrypted_id));

        $response->assertOk();
        $this->assertSoftDeleted($category);
        $response->assertJson([
            'data' => [
                'id' => $category->encrypted_id,
                'name' => $category->name,
                'description' => $category->description,
                'deleted_at' => ! null,
            ],
        ]);
    }
}
