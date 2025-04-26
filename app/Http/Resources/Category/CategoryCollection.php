<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request) : array
    {
        return [
            'id' => $this->encrypted_id,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => isset($this->created_at) ? (string) $this->created_at : null,
            'updated_at' => isset($this->updated_at) ? (string) $this->updated_at : null,
            'deleted_at' => isset($this->deleted_at) ? (string) $this->deleted_at : null,
            'links' => [
                [
                    'rel' => 'self',
                    'href' => route('categories.show', $this->encrypted_id),
                ],
                [
                    'rel' => 'category.buyers(Unique)',
                    'href' => route('categories.buyers.index', $this->encrypted_id) . '?unique=true',
                ],
                [
                    'rel' => 'category.buyers',
                    'href' => route('categories.buyers.index', $this->encrypted_id),
                ],
                [
                    'rel' => 'category.products',
                    'href' => route('categories.products.index', $this->encrypted_id),
                ],
                [
                    'rel' => 'category.seller(Unique)',
                    'href' => route('categories.sellers.index', $this->encrypted_id) . '?unique=true',
                ],
                [
                    'rel' => 'category.seller',
                    'href' => route('categories.sellers.index', $this->encrypted_id),
                ],
                [
                    'rel' => 'category.transactions',
                    'href' => route('categories.transactions.index', $this->encrypted_id),
                ],
            ],
        ];
    }
}
