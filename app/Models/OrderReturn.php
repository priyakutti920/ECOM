<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReturn extends Model
{
    use HasFactory;

    protected $table = 'order_returns';

    protected $fillable = [
        'order_id', 'order_item_id', 'product_id', 'customer_id',
        'reason', 'description', 'image_path',
        'status', 'admin_note',
        'quantity', 'restocked_at',
        'requested_at', 'resolved_at',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'restocked_at' => 'datetime',
        'requested_at' => 'datetime',
        'resolved_at'  => 'datetime',
    ];

    public const STATUSES = [
        'requested'        => 'Requested',
        'accepted'         => 'Accepted',
        'pickup_scheduled' => 'Pickup Scheduled',
        'picked_up'        => 'Picked Up',
        'completed'        => 'Completed',
        'rejected'         => 'Rejected',
    ];

    public function order()    { return $this->belongsTo(Order::class); }
    public function item()     { return $this->belongsTo(OrderItem::class, 'order_item_id'); }
    public function product()  { return $this->belongsTo(Product::class); }
    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }
}
