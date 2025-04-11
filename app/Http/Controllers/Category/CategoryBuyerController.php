<?php

namespace App\Http\Controllers\Category;

use App\Models\Buyer;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use App\Http\Resources\Buyer\BuyerCollection;
use App\Services\Pagination\PaginationFacade;
use App\Services\FilterAndSort\FilterAndSortFacade;

class CategoryBuyerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Category $category, Buyer $buyer)
    {
        $transactionsWithBuyers = $category->products()->whereHas('transactions')->with('transactions.buyer')->get();
        $transactions = $transactionsWithBuyers->pluck('transactions')->collapse();
        
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
