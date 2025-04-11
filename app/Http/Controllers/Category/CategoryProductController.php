<?php

namespace App\Http\Controllers\Category;

use App\Models\Product;
use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Services\Pagination\PaginationFacade;
use App\Http\Resources\Product\ProductCollection;
use App\Services\FilterAndSort\FilterAndSortFacade;

class CategoryProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Category $category, Product $product)
    {
        $products = $category->products;

        $filteredAndSortedProducts = FilterAndSortFacade::apply($products, $product);
        $paginatedProducts = PaginationFacade::apply($filteredAndSortedProducts);

        return ProductCollection::collection($paginatedProducts);
    }
}
