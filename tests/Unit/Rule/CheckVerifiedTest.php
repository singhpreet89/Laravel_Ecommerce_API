<?php

namespace Tests\Unit\Rule;

use App\Models\User;
use App\Rules\CheckVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckVerifiedTest extends TestCase
{
    use RefreshDatabase;

    public function test_passes_for_verified_user(): void
    {
        $user = User::factory()->create(['verified' => User::VERIFIED_USER]);
        $rule = new CheckVerified($user);

        $failed = false;
        $rule->validate('admin', User::ADMIN_USER, function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    public function test_fails_for_unverified_user(): void
    {
        $user = User::factory()->create(['verified' => User::UNVERIFIED_USER]);
        $rule = new CheckVerified($user);

        $message = null;
        $rule->validate('admin', User::ADMIN_USER, function (string $msg) use (&$message) {
            $message = $msg;
        });

        $this->assertEquals('Only verified users can modify the admin field.', $message);
    }
}
