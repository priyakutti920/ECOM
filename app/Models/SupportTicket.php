<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'subject', 'category', 'order_code',
        'status', 'last_message_at', 'closed_by', 'closed_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'closed_at'       => 'datetime',
    ];

    public const STATUSES = [
        'open'                => 'Open',
        'awaiting_customer'   => 'Awaiting customer',
        'awaiting_admin'      => 'Awaiting admin',
        'closed'              => 'Closed',
    ];

    public const CATEGORIES = [
        'general'  => 'General question',
        'order'    => 'Order issue',
        'payment'  => 'Payment / refund',
        'return'   => 'Return / refund',
        'other'    => 'Other',
    ];

    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function messages() { return $this->hasMany(SupportMessage::class, 'ticket_id')->orderBy('created_at'); }
    public function lastMessage() { return $this->hasOne(SupportMessage::class, 'ticket_id')->latestOfMany(); }
    public function closedByUser() { return $this->belongsTo(User::class, 'closed_by'); }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category ?? 'general');
    }
    public function isClosed(): bool { return $this->status === 'closed'; }
}
