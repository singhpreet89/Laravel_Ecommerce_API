<?php

namespace App\Services\Pagination;

use Illuminate\Support\Facades\Facade;

class PaginationFacade extends Facade
{
    protected static function getFacadeAccessor() : string
    {
        return 'PaginationService'; // References a Service container key
    }
}