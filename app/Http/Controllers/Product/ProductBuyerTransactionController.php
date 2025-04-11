<?php

namespace App\Http\Controllers\Product;

use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Events\CheckProductAvailabilityEvent;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Transaction\TransactionResource;
use App\Http\Requests\Product\ProductBuyerTransactionRequest;

class ProductBuyerTransactionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductBuyerTransactionRequest $request, Product $product, User $buyer)
    {
        // Make sure than the Seller is different from Buyer
        if ($buyer->id == $product->seller_id) {
            return response([
                'message' => 'Conflict.',
                'errors' => [
                    'buyer' => [
                        'The Buyer must be different from the Seller.'
                    ]
                ]
            ], Response::HTTP_CONFLICT);
        }

        if (!$buyer->isVerified()) {
            return response([
                'message' => 'Conflict.',
                'errors' => [
                    'buyer' => [
                        'The Buyer must be a verified user.'
                    ]
                ]
            ], Response::HTTP_CONFLICT);
        }

        if (!$product->seller->isVerified()) {
            return response([
                'message' => 'Conflict.',
                'errors' => [
                    'seller' => [
                        'The Seller must be a verified user.'
                    ]
                ]
            ], Response::HTTP_CONFLICT);
        }

        if (!$product->isAvailable()) {
            return response([
                'message' => 'Conflict.',
                'errors' => [
                    'product' => [
                        'The product is not available.'
                    ]
                ]
            ], Response::HTTP_CONFLICT);
        }

        if ($product->quantity < $request->quantity) {
            return response([
                'message' => 'Conflict.',
                'errors' => [
                    'product' => [
                        'The provided does not have anough units available for this transaction.'
                    ]
                ]
            ], Response::HTTP_CONFLICT);
        }

        return DB::transaction(function () use($request, $product, $buyer) {
            $product->quantity -= $request->quantity;
            $product->save();

            $transaction = Transaction::create([
                'quantity' => $request->quantity,
                'buyer_id' => $buyer->id,
                'product_id' => $product->id,
            ]);

            return new TransactionResource($transaction);
        });
    }
}
