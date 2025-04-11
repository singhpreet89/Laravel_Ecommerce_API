<?php

namespace App\Http\Controllers\Seller;

use App\Models\Seller;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Seller\SellerResource;
use App\Services\Pagination\PaginationFacade;
use App\Http\Resources\Seller\SellerCollection;
use App\Services\FilterAndSort\FilterAndSortFacade;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SellerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function index(Seller $seller) : AnonymousResourceCollection
    {
        // If the User has atleast one Product then he is a Seller
        // $sellers = Seller::has('products')->paginate(20);
        
        // ? Seller::has('products') is being handled in the SellerScope 
        $sellers = $seller->all();

        $filteredAndSortedSellers = FilterAndSortFacade::apply($sellers, $seller);
        $paginatedSellers = PaginationFacade::apply($filteredAndSortedSellers);

        return SellerCollection::collection($paginatedSellers);
    }

    /**
     * Display the specified resource.
     *
     * @param  Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function show(Seller $seller) : SellerResource
    {
        // $seller = Seller::has('products')->findOrFail($id);

        // ? Seller::has('products') is being handled in the SellerScope
        return new SellerResource($seller);
    }
}
