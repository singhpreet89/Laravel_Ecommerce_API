<?php

namespace App\Http\Controllers\Category;

use App\Models\Category;
use App\Models\Transaction;
use App\Http\Controllers\Controller;
use App\Services\Pagination\PaginationFacade;
use App\Services\FilterAndSort\FilterAndSortFacade;
use App\Http\Resources\Transaction\TransactionCollection;

class CategoryTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Category $category, Transaction $transaction)
    {
        $transactions = $category->products()->whereHas('transactions')->with('transactions')->get()->pluck('transactions')->collapse();
        
        $filteredAndSortedTransactions = FilterAndSortFacade::apply($transactions, $transaction);
        $paginatedTransactions = PaginationFacade::apply($filteredAndSortedTransactions);
        
        return TransactionCollection::collection($paginatedTransactions);
    }
}
