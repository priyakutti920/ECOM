<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id', 'sender_id', 'sender_role', 'message', 'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function ticket()  { return $this->belongsTo(SupportTicket::class, 'ticket_id'); }
    public function sender()  { return $this->belongsTo(User::class, 'sender_id'); }
}
