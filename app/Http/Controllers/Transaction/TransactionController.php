<?php

namespace App\Http\Controllers\Transaction;

use App\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Pagination\PaginationFacade;
use App\Services\FilterAndSort\FilterAndSortFacade;
use App\Http\Resources\Transaction\TransactionResource;
use App\Http\Resources\Transaction\TransactionCollection;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Transaction $transaction)
    {
        $transactions = $transaction->all();

        $filteredAndSortedTransactions = FilterAndSortFacade::apply($transactions, $transaction);
        $paginatedTransactions = PaginationFacade::apply($filteredAndSortedTransactions);
        
        return TransactionCollection::collection($paginatedTransactions);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        return new TransactionResource($transaction);
    }
}
