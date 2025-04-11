<?php

namespace App\Http\Controllers\Seller;

use App\Models\Seller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Pagination\PaginationFacade;
use App\Services\FilterAndSort\FilterAndSortFacade;
use App\Http\Resources\Transaction\TransactionCollection;

class SellerTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Seller $seller, Transaction $transaction)
    {
        $productWithTransactions = $seller->products()->whereHas('transactions')->with('transactions')->get();
        $transactions = $productWithTransactions->pluck('transactions')->collapse();

        $filteredAndSortedTransactions = FilterAndSortFacade::apply($transactions, $transaction);
        $paginatedTransactions = PaginationFacade::apply($filteredAndSortedTransactions);

        return TransactionCollection::collection($paginatedTransactions);
    }
}
