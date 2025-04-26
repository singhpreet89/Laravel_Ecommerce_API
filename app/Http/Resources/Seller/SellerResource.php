<?php

namespace App\Http\Resources\Seller;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
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
        ];
    }
}
