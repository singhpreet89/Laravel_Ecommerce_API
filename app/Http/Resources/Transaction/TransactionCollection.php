<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request) : array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'buyer_id' => $this->buyer_id,
            'product_id' => $this->product_id,
            'created_at' => isset($this->created_at) ? (string) $this->created_at : null,
            'updated_at' => isset($this->updated_at) ? (string) $this->updated_at : null,
            'deleted_at' => isset($this->deleted_at) ? (string) $this->deleted_at : null,
            'links' => [
                [
                    'rel' => 'self',
                    'href' => route('transactions.show', $this->id),
                ],
                [
                    'rel' => 'transaction.buyer',
                    'href' => route('buyers.show', $this->buyer_id),
                ],
                [
                    'rel' => 'transaction.categories',
                    'href' => route('transactions.categories.index', $this->id),
                ],
                [
                    'rel' => 'transaction.seller',
                    'href' => route('transactions.sellers.index', $this->id),
                ],
                [
                    'rel' => 'transaction.product',
                    'href' => route('products.show', $this->product_id),
                ],
            ],
        ];
    }
}
