<?php

namespace App\Http\Resources\Seller;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request) : array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'verified' => (int) $this->verified,
            'created_at' => isset($this->created_at) ? (string) $this->created_at : null,
            'updated_at' => isset($this->updated_at) ? (string) $this->updated_at : null,
            'deleted_at' => isset($this->deleted_at) ? (string) $this->deleted_at : null,
            'links' => [
                [
                    'rel' => 'self',
                    'href' => route('sellers.show', $this->id),
                ],
                [
                    'rel' => 'seller.buyers(Unique)',
                    'href' => route('sellers.buyers.index', $this->id) . '?unique=true',
                ],
                [
                    'rel' => 'seller.buyers',
                    'href' => route('sellers.buyers.index', $this->id),
                ],
                [
                    'rel' => 'seller.categories(Unique)',
                    'href' => route('sellers.categories.index', $this->id) . '?unique=true',
                ],
                [
                    'rel' => 'seller.categories',
                    'href' => route('sellers.categories.index', $this->id),
                ],
                [
                    'rel' => 'seller.products',
                    'href' => route('sellers.products.index', $this->id),
                ],
                [
                    'rel' => 'seller.transactions',
                    'href' => route('sellers.transactions.index', $this->id),
                ],
            ],
        ];
    }
}
