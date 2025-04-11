<?php

namespace App\Http\Controllers\Seller;

use App\Models\Seller;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use App\Services\Pagination\PaginationFacade;
use App\Http\Resources\Category\CategoryCollection;
use App\Services\FilterAndSort\FilterAndSortFacade;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SellerCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Seller $seller, Category $category) : AnonymousResourceCollection
    {
        $productsWithcategories = $seller->products()->whereHas('categories')->with('categories')->get();

        $categories = '';
        if(Request::query('unique') === "true") {
            $categories = $productsWithcategories->pluck('categories')->collapse()->unique('id')->values();
        } else {
            $categories = $productsWithcategories->pluck('categories')->collapse();
        }
        
        $filteredAndSortedCategories = FilterAndSortFacade::apply($categories, $category);
        $paginatedCategories = PaginationFacade::apply($filteredAndSortedCategories);
        
        return CategoryCollection::collection($paginatedCategories);
    }
}
