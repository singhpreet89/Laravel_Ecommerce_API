<?php

namespace Tests\Unit\Model\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_all(): void
    {
        $numberOfUsers = 10;
        $users = User::factory()->count($numberOfUsers)->create();
        $createdUsers = User::all();

        $this->assertEquals($users->count(), $createdUsers->count());

        for ($i = 0; $i < $numberOfUsers; $i++) {
            $this->assertEquals(
                [
                    $users[$i]->id,
                    $users[$i]->name,
                    $users[$i]->email,
                    $users[$i]->email_verified_at,
                    $users[$i]->verified,
                    $users[$i]->admin,
                    $users[$i]->created_at,
                    $users[$i]->updated_at,
                ],
                [
                    $createdUsers[$i]->id,
                    $createdUsers[$i]->name,
                    $createdUsers[$i]->email,
                    $createdUsers[$i]->email_verified_at,
                    $createdUsers[$i]->verified,
                    $createdUsers[$i]->admin,
                    $createdUsers[$i]->created_at,
                    $createdUsers[$i]->updated_at,
                ]
            );
        }
    }

    public function test_create(): void
    {
        $user = User::factory()->create();
        $savedUser = User::find($user->id);
        $savedUserArray = $savedUser->toArray();

        $this->assertEquals(
            [
                $user->id,
                $user->name,
                $user->email,
                $user->email_verified_at,
                $user->verified,
                $user->admin,
                $user->created_at,
                $user->updated_at,
            ],
            [
                $savedUser->id,
                $savedUser->name,
                $savedUser->email,
                $savedUser->email_verified_at,
                $savedUser->verified,
                $savedUser->admin,
                $savedUser->created_at,
                $savedUser->updated_at,
            ]
        );

        $this->assertEquals(null, $savedUser->deleted_at);
        $this->assertArrayNotHasKey('password', $savedUserArray);
        $this->assertArrayNotHasKey('remember_token', $savedUserArray);
        $this->assertArrayNotHasKey('verification_token', $savedUserArray);

        // Because 'password', 'remember_token', 'verification_token' will not be returned, 'deleted_at' will be returned, and 'encrypted_id' is appended via ResolveTrait
        $this->assertCount(10, $savedUserArray);
    }

    public function test_update(): void
    {
        $user = User::factory()->create();

        $user->name = 'Jack Sparrow';
        $user->email = 'jack@sparrow.com';
        $user->save();

        $savedUser = User::find($user->id);

        $this->assertEquals(
            [
                $user->id,
                $user->name,
                $user->email,
            ],
            [
                $savedUser->id,
                $savedUser->name,
                $savedUser->email,
            ]
        );
        $this->assertEquals(null, $savedUser->deleted_at);
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();
        $deletedUser = $user->delete($user->id);

        $totalUsers = User::all();

        $this->assertTrue($deletedUser);
        $this->assertEquals(0, $totalUsers->count());
    }

    public function test_soft_deletes(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $this->assertSoftDeleted($user);
        $this->assertCount(0, User::all());

        $trashedUser = User::withTrashed()->find($user->id);
        $this->assertNotNull($trashedUser->deleted_at);
    }

    public function test_is_verified(): void
    {
        $verified = User::factory()->create(['verified' => User::VERIFIED_USER]);
        $unverified = User::factory()->create(['verified' => User::UNVERIFIED_USER]);

        $this->assertTrue($verified->isVerified());
        $this->assertFalse($unverified->isVerified());
    }

    public function test_is_admin(): void
    {
        $admin = User::factory()->create(['admin' => User::ADMIN_USER]);
        $regular = User::factory()->create(['admin' => User::REGULAR_USER]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($regular->isAdmin());
    }

    public function test_generate_verification_code(): void
    {
        $code = User::generateVerificationCode();

        $this->assertIsString($code);
        $this->assertEquals(40, strlen($code)); // bin2hex(random_bytes(20)) = 40 hex chars
    }

    public function test_cache_drivers()
    {
        $drivers = ['redis', 'database', 'array'];
        $results = [];

        foreach ($drivers as $driver) {
            Cache::setDefaultDriver($driver);

            $start = microtime(true);
            Cache::remember('test_key', 60, fn () => User::all());
            $write = microtime(true) - $start;

            $start = microtime(true);
            $data = Cache::get('test_key');
            $read = microtime(true) - $start;

            $results[$driver] = [
                'write' => $write * 1000,
                'read' => $read * 1000,
            ];
        }

        print_r($results);

        $this->AssertTrue(true);
    }
}
