<?php

namespace App\Models;

use App\Models\Product;
use App\Scopes\SellerScope;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends User
{
    const ENABLE_FILTER_AND_SORT_ON_COLUMNS = [
        'id',
        'name',
        'email',
        'verified',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * To eliminate the error 'sellers table not found' while seeding the database. 
     * And to use the 'users' table to add a new Seller through API POST request. 
     * Because the 'sellers' table does not exist 
     * */ 
    protected $table = 'users';

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted() : void
    {   
        static::addGlobalScope(new SellerScope);
    }

    public function products() : HasMany 
    {
        return $this->hasMany(Product::class);
    }
}
