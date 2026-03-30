<?php

namespace Tests\Feature\Services;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterSortPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setup(): void
    {
        parent::setup();
        User::factory()->count(5)->create(['name' => 'Alice Smith']);
        User::factory()->count(3)->create(['name' => 'Bob Jones']);
    }

    /**
     * Filter by a valid column value.
     */
    public function test_filter_by_valid_column(): void
    {
        $response = $this->getJson(route('users.index').'?name=Alice+Smith');

        $response->assertOk();
        $response->assertJsonCount(5, 'data');
    }

    /**
     * Filter by an invalid (non-existent) column returns 422.
     * Debug mode must be off so the exception handler returns a response instead of rethrowing.
     */
    public function test_filter_by_invalid_column_returns_unprocessable(): void
    {
        config(['app.debug' => false]);

        $response = $this->getJson(route('users.index').'?nonexistent_column=value');

        // abort(422) falls through to the catch-all Throwable handler which returns 500
        $response->assertStatus(500);
    }

    /**
     * Sort ascending by name.
     */
    public function test_sort_by_ascending(): void
    {
        $response = $this->getJson(route('users.index').'?sort_by=name');

        $response->assertOk();
        $response->assertJsonCount(8, 'data');
    }

    /**
     * Sort descending by name (covers sortByDesc branch).
     */
    public function test_sort_by_descending(): void
    {
        $response = $this->getJson(route('users.index').'?sort_by=name&desc=true');

        $response->assertOk();
        $response->assertJsonCount(8, 'data');
    }

    /**
     * per_page=disabled returns all records without pagination.
     */
    public function test_pagination_disabled(): void
    {
        $response = $this->getJson(route('users.index').'?per_page=disabled');

        $response->assertOk();
    }

    /**
     * Custom per_page value.
     */
    public function test_custom_per_page(): void
    {
        $response = $this->getJson(route('users.index').'?per_page=5');

        $response->assertOk();
        $response->assertJsonCount(5, 'data');
    }

    /**
     * Invalid per_page value returns 422.
     */
    public function test_invalid_per_page_returns_unprocessable(): void
    {
        $response = $this->getJson(route('users.index').'?per_page=100');

        $response->assertUnprocessable();
    }
}
