<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = ['slug', 'name', 'description', 'icon', 'is_available', 'is_active', 'method_name', 'credentials'];

    protected function casts(): array
    {
        return [
            'is_available' => 'integer',
            'is_active'    => 'integer',
            'credentials'  => 'array',
        ];
    }

    public function getCredential(string $key, $default = null)
    {
        return $this->credentials[$key] ?? $default;
    }

    public function setCredential(string $key, $value): void
    {
        $creds = $this->credentials ?? [];
        $creds[$key] = $value;
        $this->credentials = $creds;
    }
}