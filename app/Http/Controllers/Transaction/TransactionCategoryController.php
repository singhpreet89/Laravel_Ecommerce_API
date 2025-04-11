<?php

namespace App\Http\Controllers\Transaction;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Pagination\PaginationFacade;
use App\Http\Resources\Category\CategoryCollection;
use App\Services\FilterAndSort\FilterAndSortFacade;

class TransactionCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Transaction $transaction, Category $category)
    {
        $categories = $transaction->product->categories;

        $filteredAndSortedCategories = FilterAndSortFacade::apply($categories, $category);
        $paginatedCategories = PaginationFacade::apply($filteredAndSortedCategories);
        
        return CategoryCollection::collection($paginatedCategories);
    }
}
