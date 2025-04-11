<?php

namespace App\Http\Controllers\Buyer;

use App\Models\Buyer;
use App\Models\Seller;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use App\Services\Pagination\PaginationFacade;
use App\Http\Resources\Seller\SellerCollection;
use App\Services\FilterAndSort\FilterAndSortFacade;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BuyerSellerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Buyer $buyer, Seller $seller) : AnonymousResourceCollection
    {
        /**
         * ! EAGER LOAGING
         * ? SQL Query: 
         *      select s.* from users u 
         *          left join transactions t on u.id = t.buyer_id
         *          left join products p on t.product_id = p.id
         *          left join users s on p.seller_id = s.id
         *      where u.id = 5;
         * The result of $buyer->transactions is a collection as a Buyer has many Transactions
         * And each Transaction belogs to a Product
         * Further each Product belongs to a Seller
         * ? The Buyer must have purchased multiple items from a Seller, so we need to ding the unique sellers
         * ? This leaves a null object in the collection, so values() recreates the collection index and removes the null index
         */
        $transactionsWithProductsAndSellers = $buyer->transactions()->with('product.seller')->get();

        $sellers = "";
        if(Request::query('unique') === "true") {
            $sellers = $transactionsWithProductsAndSellers->pluck('product.seller')->unique('id')->values();
        } else {
            $sellers = $transactionsWithProductsAndSellers->pluck('product.seller');
        }
        
        $filteredAndSortedSellers = FilterAndSortFacade::apply($sellers, $seller);
        $paginatedSellers = PaginationFacade::apply($filteredAndSortedSellers);
        
        return SellerCollection::collection($paginatedSellers);
    }
}
