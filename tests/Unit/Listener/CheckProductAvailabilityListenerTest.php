<?php

namespace Tests\Unit\Listener;

use App\Events\CheckProductAvailabilityEvent;
use App\Listeners\CheckProductAvailabilityListener;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckProductAvailabilityListenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_sets_product_unavailable_when_quantity_is_zero_and_available(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => Product::AVAILABLE_PRODUCT,
            'quantity' => 0,
        ]);

        $event = new CheckProductAvailabilityEvent($product);
        $listener = new CheckProductAvailabilityListener;
        $listener->handle($event);

        $this->assertEquals(Product::UNAVAILABLE_PRODUCT, $product->fresh()->status);
    }

    public function test_does_not_change_status_when_quantity_is_non_zero(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => Product::AVAILABLE_PRODUCT,
            'quantity' => 5,
        ]);

        $event = new CheckProductAvailabilityEvent($product);
        $listener = new CheckProductAvailabilityListener;
        $listener->handle($event);

        $this->assertEquals(Product::AVAILABLE_PRODUCT, $product->fresh()->status);
    }

    public function test_does_not_change_status_when_already_unavailable(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => Product::UNAVAILABLE_PRODUCT,
            'quantity' => 0,
        ]);

        $event = new CheckProductAvailabilityEvent($product);
        $listener = new CheckProductAvailabilityListener;
        $listener->handle($event);

        $this->assertEquals(Product::UNAVAILABLE_PRODUCT, $product->fresh()->status);
    }
}
