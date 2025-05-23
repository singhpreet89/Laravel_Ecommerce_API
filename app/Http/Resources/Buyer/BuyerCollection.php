<?php

namespace App\Http\Resources\Buyer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuyerCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request) : array
    {
        return [
            'id' => $this->encrypted_id,
            'name' => $this->name,
            'email' => $this->email,
            'verified' => (int) $this->verified,
            'created_at' => isset($this->created_at) ? (string) $this->created_at : null,
            'updated_at' => isset($this->updated_at) ? (string) $this->updated_at : null,
            'deleted_at' => isset($this->deleted_at) ? (string) $this->deleted_at : null,
            'links' => [
                [
                    'rel' => 'self',
                    'href' => route('buyers.show', $this->encrypted_id),
                ],
                [
                    'rel' => 'buyer.categories',
                    'href' => route('buyers.categories.index', $this->encrypted_id),
                ],
                [
                    'rel' => 'buyer.products',
                    'href' => route('buyers.products.index', $this->encrypted_id),
                ],
                [
                    'rel' => 'buyer.sellers(Unique)',
                    'href' => route('buyers.sellers.index', $this->encrypted_id) . '?unique=true',
                ],
                [
                    'rel' => 'buyer.sellers',
                    'href' => route('buyers.sellers.index', $this->encrypted_id),
                ],
                [
                    'rel' => 'buyer.transactions',
                    'href' => route('buyers.transactions.index', $this->encrypted_id),
                ],
            ],
        ];
    }
}
