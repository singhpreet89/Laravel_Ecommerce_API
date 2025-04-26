<?php

namespace App\Models;

use App\Models\Seller;
use App\Models\Category;
use App\Models\Transaction;
use App\Traits\ResolveTrait;
use Illuminate\Database\Eloquent\Model;
use App\Events\CheckProductAvailabilityEvent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    use ResolveTrait;

    public const AVAILABLE_PRODUCT = 'available';
    public const UNAVAILABLE_PRODUCT = 'unavailable';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'quantity',
        'status',
        'image',
        'seller_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'pivot',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'updated' => CheckProductAvailabilityEvent::class,
    ];

    /**
     * Check if the product is available
     */
    public function isAvailable(): bool
    {
        return $this->status === self::AVAILABLE_PRODUCT;
    }

    /**
     * Define product-seller relationship
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Define product-category relationship
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Define product-transaction relationship
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
