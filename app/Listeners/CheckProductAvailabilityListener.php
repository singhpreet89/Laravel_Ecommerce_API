<?php

namespace App\Listeners;

use App\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\CheckProductAvailabilityEvent;

class CheckProductAvailabilityListener
{   
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CheckProductAvailabilityEvent  $event
     * @return void
     */
    public function handle(CheckProductAvailabilityEvent $event)
    {
        if($event->productObject->quantity === 0 && $event->productObject->isAvailable())  {
            $event->productObject->status = Product::UNAVAILABLE_PRODUCT;
            $event->productObject->save();
        }
    }
}
