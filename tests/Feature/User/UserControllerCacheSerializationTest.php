<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UserControllerCacheSerializationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_index_returns_results_from_a_warm_cache(): void
    {
        User::factory()->count(3)->create();

        Cache::forget('users');

        $firstResponse = $this->getJson('/api/v1/users');
        $secondResponse = $this->getJson('/api/v1/users');

        $firstResponse
            ->assertOk()
            ->assertJsonCount(3, 'data');

        $secondResponse
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }
}
