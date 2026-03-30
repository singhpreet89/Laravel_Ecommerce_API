<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
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
        $response->assertJsonCount($numberOfUsers, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'verified',
                    'admin',
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
    public function test_store()
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'random',
            'password_confirmation' => 'random',
        ];

        $response = $this->postJson(route('users.store'), $payload);

        $this->assertDatabaseHas('users', [
            'name' => $payload['name'],
            'email' => $payload['email'],
        ]);

        $createdUser = User::first();

        $response->assertCreated();
        $response->assertJson([
            'data' => [
                'id' => $createdUser->encrypted_id,
                'name' => $payload['name'],
                'email' => $payload['email'],
                'verified' => 0,
                'admin' => false,
                'created_at' => ! null,
                'updated_at' => ! null,
                'deleted_at' => null,
            ],
        ]);
    }

    /**
     * @return void
     */
    public function test_show()
    {
        $user = User::factory()->create();

        $response = $this->getJson(route('users.show', $user->encrypted_id));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'id' => $user->encrypted_id,
                'name' => $user->name,
                'email' => $user->email,
                'verified' => (int) $user->verified,
                'admin' => $user->admin === 'true' ? true : false,
                'created_at' => (string) $user->created_at,
                'updated_at' => (string) $user->updated_at,
                'deleted_at' => $user->deleted_at,
            ],
        ]);
    }

    /**
     * @return void
     */
    public function test_update()
    {
        $user = User::factory()->create([
            'verified' => '0',
            'admin' => 'false',
        ]);

        $payload = [
            'name' => 'Jack Daniels',
            'email' => 'jack@example.com',
        ];

        $response = $this->putJson(route('users.update', $user->encrypted_id), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $user->encrypted_id,
                'name' => $payload['name'],
                'email' => $payload['email'],
                'verified' => (int) $user->verified,
                'admin' => $user->admin === 'true' ? true : false,
                'created_at' => (string) $user->created_at,
                'updated_at' => (string) $user->updated_at,
                'deleted_at' => $user->deleted_at,
            ],
        ]);
    }

    public function test_update_password(): void
    {
        $user = User::factory()->create();

        $response = $this->putJson(route('users.update', $user->encrypted_id), [
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.id', $user->encrypted_id);
    }

    /**
     * Updating admin field on a verified user.
     */
    public function test_update_admin_for_verified_user(): void
    {
        $user = User::factory()->create(['verified' => User::VERIFIED_USER]);

        $response = $this->putJson(route('users.update', $user->encrypted_id), [
            'admin' => User::ADMIN_USER,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.admin', true);
    }

    /**
     * Updating admin field on an unverified user is rejected by CheckVerified rule.
     */
    public function test_update_admin_for_unverified_user_fails(): void
    {
        $user = User::factory()->create(['verified' => User::UNVERIFIED_USER]);

        $response = $this->putJson(route('users.update', $user->encrypted_id), [
            'admin' => User::ADMIN_USER,
        ]);

        $response->assertUnprocessable();
    }

    /**
     * @return void
     */
    public function test_destroy()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson(route('users.destroy', $user->encrypted_id));

        $response->assertOk(200);
        $this->assertSoftDeleted($user);
        $response->assertJson([
            'data' => [
                'id' => $user->encrypted_id,
                'name' => $user->name,
                'email' => $user->email,
                'verified' => (int) $user->verified,
                'admin' => $user->admin === 'true' ? true : false,
                'created_at' => (string) $user->created_at,
                'updated_at' => (string) $user->updated_at,
                'deleted_at' => ! null,
            ],
        ]);
    }
}
