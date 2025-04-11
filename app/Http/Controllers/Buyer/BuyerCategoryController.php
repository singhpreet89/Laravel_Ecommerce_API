<?php

namespace App\Http\Controllers\Buyer;

use App\Models\Buyer;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use App\Http\Resources\Category\CategoryCollection;
use App\Services\FilterAndSort\FilterAndSortFacade;
use App\Services\Pagination\PaginationFacade;

class BuyerCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Buyer $buyer, Category $category)
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
         * Further each Product belongs to many Categories
         * ? The Buyer must have purchased multiple items and those items belong to multiple categories, This creates a collection with in another collection
         * ? Returning without collapse() will give a collection of collection, but the collapse() method creates a Unique collection from many collections
         * ? To remove a null object from the collection, as the output of unique(), values() recreates the collection index and removes the null index
         */
        $transactionsWithProductsAndCategories = $buyer->transactions()->with('product.categories')->get();
        $categories = $transactionsWithProductsAndCategories->pluck('product.categories')->collapse()->unique('id')->values();

        $filteredAndSortedCategories = FilterAndSortFacade::apply($categories, $category);
        $paginatedCategories = PaginationFacade::apply($filteredAndSortedCategories);

        return CategoryCollection::collection($paginatedCategories);
    }
}
