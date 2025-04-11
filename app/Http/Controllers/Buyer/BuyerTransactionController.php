<?php

namespace App\Http\Controllers\Buyer;

use App\Models\Buyer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Pagination\PaginationFacade;
use App\Services\FilterAndSort\FilterAndSortFacade;
use App\Http\Resources\Transaction\TransactionCollection;

class BuyerTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Buyer $buyer, Transaction $transaction)
    {
        $transactions = $buyer->transactions;

        $filteredAndSortedTransactions = FilterAndSortFacade::apply($transactions, $transaction);
        $paginatedTransactions = PaginationFacade::apply($filteredAndSortedTransactions);

        return TransactionCollection::collection($paginatedTransactions);
    }
}
