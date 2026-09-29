<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingTemplate extends Model
{
    protected $fillable = [
        'user_id', 'template_name', 'template_json', 'preview_image', 'status',
    ];

    protected function casts(): array
    {
        return ['status' => 'integer', 'user_id' => 'integer'];
    }
}