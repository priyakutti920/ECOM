<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'order_id', 'user_id', 'template_id', 'customer_name', 'invoice_number',
        'invoice_date', 'customer_email', 'customer_phone', 'customer_address', 'customer_gst',
        'invoice_json', 'subtotal', 'discount_amount', 'tax_amount', 'other_charges', 'total_amount', 'status',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'other_charges'   => 'decimal:2',
            'total_amount'    => 'decimal:2',
            'status'          => 'integer',
            'user_id'         => 'integer',
            'template_id'     => 'integer',
            'order_id'        => 'integer',
            'invoice_date'    => 'date',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function template()
    {
        return $this->belongsTo(BillingTemplate::class, 'template_id');
    }

    public function getInvoiceDataAttribute(): array
    {
        $data = json_decode((string) $this->invoice_json, true);
        return is_array($data) ? $data : [];
    }

    public function getIsInterstateAttribute(): bool
    {
        return (bool) ($this->invoice_data['is_interstate'] ?? false);
    }

    public function getCgstAttribute(): float
    {
        if ($this->is_interstate) {
            return 0.0;
        }
        return round(((float) $this->tax_amount) / 2, 2);
    }

    public function getSgstAttribute(): float
    {
        if ($this->is_interstate) {
            return 0.0;
        }
        return round(((float) $this->tax_amount) / 2, 2);
    }

    public function getIgstAttribute(): float
    {
        if (!$this->is_interstate) {
            return 0.0;
        }
        return round((float) $this->tax_amount, 2);
    }
}