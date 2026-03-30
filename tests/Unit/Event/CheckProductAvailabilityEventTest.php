<?php

namespace Tests\Unit\Event;

use App\Events\CheckProductAvailabilityEvent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckProductAvailabilityEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_stores_product_object(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);

        $event = new CheckProductAvailabilityEvent($product);

        $this->assertInstanceOf(Product::class, $event->productObject);
        $this->assertEquals($product->id, $event->productObject->id);
    }

    public function test_broadcast_on_returns_private_channel(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);

        $event = new CheckProductAvailabilityEvent($product);
        $channel = $event->broadcastOn();

        $this->assertInstanceOf(PrivateChannel::class, $channel);
    }
}
