<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request) : array
    {
        return [
            'id' => $this->id,
            'seller_id' => $this->seller_id,
            'name' => $this->name,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'status' => $this->status,
            'image' => url("img/{$this->image}"),
            'created_at' => isset($this->created_at) ? (string) $this->created_at : null,
            'updated_at' => isset($this->updated_at) ? (string) $this->updated_at : null,
            'deleted_at' => isset($this->deleted_at) ? (string) $this->deleted_at : null,
        ];
    }
}
