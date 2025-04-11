<?php

namespace Tests\Feature\User;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp(): void
    {
        parent::setUp();


    }

    /**
     *
     * @return void
     */
    public function testindex()
    {
        $numberOfUsers = 5;
        User::factory()->count($numberOfUsers)->create();

        $response = $this->getJson(route('users.index'));
        // Log::info($response->getContent());
        // $response->dump();

        $response->assertStatus(200);
        $response->assertJsonCount($numberOfUsers, $key = "data");
        $response->assertJsonStructure([
            "data" => [
                "*" => [
                    "id",
                    "name",
                    "email",
                    "verified",
                    "admin",
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
    public function testStore()
    {
        $payload = [
            "name" => $this->faker->name,
            "email" =>$this->faker->safeEmail,
            "password" => "random",
            "password_confirmation" => "random"
        ];

        // $this->withoutExceptionHandling();
        $response = $this->postJson(route('users.store'), $payload);

        $this->assertDatabaseHas('users', [
            'name' => $payload["name"],
            'email' => $payload["email"],
        ]);
        $response->assertCreated();
        $response->assertJson([
            "data" => [
                "id" => 1,
                "name" => $payload["name"],
                "email" => $payload["email"],
                "verified" => 0,
                "admin" => false,
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
    public function testShow()
    {
        $user = User::factory()->create();
        $user->save();

        $response = $this->getJson(route('users.show', $user->id));

        $response->assertOk();
        $response->assertJson([
            "data" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "verified" => (int) $user->verified,
                "admin" => $user->admin === "true" ? true : false,
                "created_at" => (string) $user->created_at,
                "updated_at" => (string) $user->updated_at,
                "deleted_at" => $user->deleted_at,
            ]
        ]);
    }

    /**
     *
     * @return void
     */
    public function testUpdate()
    {
        $user = User::factory()->create([
            "verified" => "0",
            "admin" => "false",
        ]);

        $payload = [
            "name" => "Jack Daniels",
            "email" => "jack@example.com",
        ];

        $response = $this->putJson(route('users.update', $user->id), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [
                "id" => $user->id,
                "name" => $payload['name'],
                "email" => $payload['email'],
                "verified" => (int) $user->verified,
                "admin" => $user->admin === "true" ? true : false,
                "created_at" => (string) $user->created_at,
                "updated_at" => (string) $user->updated_at,
                "deleted_at" => $user->deleted_at,
            ]
        ]);
    }

    /**
     *
     * @return void
     */
    public function testDestroy()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson(route('users.destroy', $user->id));

        $response->assertOk(200);
        $this->assertSoftDeleted($user);
        $response->assertJson([
            "data" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "verified" => (int) $user->verified,
                "admin" => $user->admin === "true" ? true : false,
                "created_at" => (string) $user->created_at,
                "updated_at" => (string) $user->updated_at,
                "deleted_at" => !null,
            ]
        ]);
    }
}
