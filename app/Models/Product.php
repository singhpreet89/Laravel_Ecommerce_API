<?php

namespace App\Models;

use App\Models\Seller;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use App\Events\CheckProductAvailabilityEvent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    const AVAILABLE_PRODUCT = 'available';
    const UNAVAILABLE_PRODUCT = 'unavailable';

    protected $fillable = [
        'name', 'description', 'quantity', 'status', 'image', 'seller_id',
    ];

    protected $hidden = [
        'pivot',
    ];

    // ! Event emitter to change the product 'status' to 'unavailable' when the 'quantity' is reduced to 0 after performing a 'Transaction'
    protected $dispatchesEvents = [
        'updated' => CheckProductAvailabilityEvent::class,
    ];

    public function isAvailable() {
        // ! This returns a true or false
        return $this->status == Product::AVAILABLE_PRODUCT;
        // Or
        // return $this->status == self::AVAILABLE_PRODUCT;
    }

    public function seller() {
        return $this->belongsTo(Seller::class);
    }

    public function categories() {
        return $this->belongsToMany(Category::class);
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }
}
