<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Provider extends Model
{
    protected $fillable = ['name', 'mobile', 'address', 'city'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_provider')
            ->withTimestamps();
    }
}