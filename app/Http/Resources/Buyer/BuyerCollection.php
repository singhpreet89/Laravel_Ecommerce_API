<?php

namespace App\Http\Resources\Buyer;

use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;

class BuyerCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'verified' => (int) $this->verified,
            'created_at' => isset($this->created_at) ? (string) $this->created_at : null,
            'updated_at' => isset($this->updated_at) ? (string) $this->updated_at : null,
            'deleted_at' => isset($this->deleted_at) ? (string) $this->deleted_at : null,
            'link' => [
                'rel' => 'self',
                'href' => route('buyers.show', $this->id),
            ],
        ];
    }
}
