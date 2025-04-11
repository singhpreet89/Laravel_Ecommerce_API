<?php

namespace App\Services\FilterAndSort;

use Illuminate\Support\Facades\Facade;

class FilterAndSortFacade extends Facade
{
    protected static function getFacadeAccessor() : string
    {
        return 'FilterAndSortService'; // References a Service container key
    }
}