<?php

namespace App\Http\Controllers\Buyer;

use App\Models\Buyer;
use App\Http\Controllers\Controller;
use App\Http\Resources\Buyer\BuyerResource;
use App\Http\Resources\Buyer\BuyerCollection;
use App\Services\Pagination\PaginationFacade;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Services\FilterAndSort\FilterAndSortFacade;

class BuyerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Buyer $buyer): ResourceCollection
    {
        // ! If the User has atleast one Transaction then he is a Buyer
        // $buyers = Buyer::has('transactions')->get();

        // ? Buyer::has('transactions') is being handled in the BuyerScope
        $buyers = $buyer->all();
        $filteredAndSortedBuyers = FilterAndSortFacade::apply($buyers, $buyer);
        $paginatedBuyers = PaginationFacade::apply($filteredAndSortedBuyers);

        return BuyerCollection::collection($paginatedBuyers);
    }

    /**
     * Display the specified resource.
     */
    public function show(Buyer $buyer): BuyerResource
    {
        // $buyer = Buyer::has('transactions')->findOrFail($id);

        // ? Buyer::has('transactions') is being handled in the BuyerScope
        return new BuyerResource($buyer);
    }
}
