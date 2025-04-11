<?php

namespace App\Http\Controllers\Product;

use App\Models\Buyer;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Buyer\BuyerCollection;
use App\Services\Pagination\PaginationFacade;
use App\Services\FilterAndSort\FilterAndSortFacade;

class ProductBuyerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Product $product, Buyer $buyer)
    {
        $transactionsWithBuyers = $product->transactions()->with('buyer')->get();
        $buyers = $transactionsWithBuyers->pluck('buyer')->unique('id')->values();

        $filteredAndSortedBuyers = FilterAndSortFacade::apply($buyers, $buyer);
        $paginatedBuyers = PaginationFacade::apply($filteredAndSortedBuyers);

        return BuyerCollection::collection($paginatedBuyers);
    }
}
