<?php

namespace App\Http\Controllers\Seller;

use App\Models\Buyer;
use App\Models\Seller;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use App\Http\Resources\Buyer\BuyerCollection;
use App\Services\Pagination\PaginationFacade;
use App\Services\FilterAndSort\FilterAndSortFacade;

class SellerBuyerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Seller $seller, Buyer $buyer)
    {
        $productsWithTransactionsAndBuyer = $seller->products()->whereHas('transactions')->with('transactions.buyer')->get();
        $transactions = $productsWithTransactionsAndBuyer->pluck('transactions')->collapse();
        
        $buyers = '';
        if(Request::query('unique') === "true") {
            $buyers = $transactions->pluck('buyer')->unique('id')->values();
        } else {
            $buyers = $transactions->pluck('buyer');
        }

        $filteredAndSortedBuyers = FilterAndSortFacade::apply($buyers, $buyer);
        $paginatedBuyers = PaginationFacade::apply($filteredAndSortedBuyers);

        return BuyerCollection::collection($paginatedBuyers);
    }
}
