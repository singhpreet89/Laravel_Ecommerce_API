<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setup(): void 
    {
        parent::setup();
    }

    /**
     *
     * @return void
     */
    public function testIndex()
    {
        $numberOfCategories = 18;
        Category::factory()->count($numberOfCategories)->create();

        $response = $this->getJson(route('categories.index'));
        
        $response->assertOk();
        $response->assertJsonCount($numberOfCategories, $key = "data");
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

    /**
     *
     * @return void
     */
    public function testShow()
    {
        $category = Category::factory()->create();

        $response = $this->getJson(route('categories.show', $category->id));

        $response->assertOk();
        $response->assertJson([
            "data" => [
                "id" => $category->id,
                "name" => $category->name,
                "description" => $category->description,
                "created_at" => $category->created_at,
                "updated_at" => $category->updated_at,
                "deleted_at" => null,
            ]
        ]);
    }

    /**
     *
     * @return void
     */
    public function testStore()
    {
        $payload = [
            "name" => "Mobile phones",
            "description" => "All the latest phones launched in Canada",
        ];

        $response = $this->postJson(route('categories.store'), $payload);
        
        $this->assertDatabaseHas('categories', [
            'name' => $payload["name"],
            'description' => $payload["description"],
        ]);
        $response->assertCreated();
        $response->assertJson([
            "data" => [
                "id" => 1,
                "name" => $payload["name"],
                "description" => $payload["description"],
                "created_at" => !null,
                "updated_at" => !null,
                "deleted_at" => null,
            ]
        ]);
    }

    /**
     *
     * @return void
     */
    public function testUpdate()
    {
        $category = Category::factory()->create([
            "name" => "Washing machines",
            "description" => "All the washing machines come under thic category.",
        ]);

        $payload = [
            "name" => "Alcohal",
            "description" => "Only top quality spirits.",
        ];

        $response = $this->putJson(route('categories.update', $category->id), $payload);
        
        $response->assertOk();
        $response->assertJson([
            "data" => [
                "id" => $category->id,
                "name" => $payload["name"],
                "description" => $payload["description"],
                "created_at" => $category->created_at,
                "updated_at" => $category->updated_at,
                "deleted_at" => null,
            ]
        ]);
    }

     /**
     *
     * @return void
     */
    public function testdestroy()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson(route('categories.destroy', $category->id));
        
        $response->assertOk();
        $this->assertSoftDeleted($category);
        $response->assertJson([
            "data" => [
                "id" => $category->id,
                "name" => $category->name,
                "description" => $category->description,
                "created_at" => $category->created_at,
                "updated_at" => $category->updated_at,
                "deleted_at" => !null,
            ]
        ]);
    }
}
