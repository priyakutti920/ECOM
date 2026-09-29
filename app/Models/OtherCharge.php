<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherCharge extends Model
{
    protected $fillable = ['name', 'type', 'value', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'integer', 'value' => 'float', 'sort_order' => 'integer'];
    }
}