<?php

namespace App\Http\Controllers\Category;

use App\Models\Seller;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use App\Services\Pagination\PaginationFacade;
use App\Http\Resources\Seller\SellerCollection;
use App\Services\FilterAndSort\FilterAndSortFacade;

class CategorySellerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Category $category, Seller $seller)
    {
        $productsWithSeller = $category->products()->with('seller')->get();
        
        $sellers = "";
        if(Request::query('unique') === "true") {
            $sellers = $productsWithSeller->pluck('seller')->unique('id')->values();
        } else {
            $sellers = $productsWithSeller->pluck('seller');
        }

        $filteredAndSortedSellers = FilterAndSortFacade::apply($sellers, $seller);
        $paginatedSellers = PaginationFacade::apply($filteredAndSortedSellers);
        
        return SellerCollection::collection($paginatedSellers);
    }
}
