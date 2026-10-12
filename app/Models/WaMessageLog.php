<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaMessageLog extends Model
{
    protected $table = 'wa_message_logs';

    protected $fillable = [
        'telephone',
        'template_name',
        'order_id',
        'invoice_id',
        'status',
        'source',
        'response_body',
    ];

    public static function createLog(array $data): self
    {
        return static::create([
            'telephone' => $data['telephone'] ?? '',
            'template_name' => $data['template_name'] ?? 'order_notify',
            'order_id' => $data['order_id'] ?? null,
            'invoice_id' => $data['invoice_id'] ?? null,
            'status' => $data['status'] ?? 'failed',
            'source' => $data['source'] ?? 'system',
            'response_body' => is_string($data['response_body'] ?? '') ? substr($data['response_body'], 0, 4000) : json_encode($data['response_body'] ?? []),
        ]);
    }

    public static function recent(int $limit = 20)
    {
        return static::orderByDesc('id')->take($limit)->get();
    }

    public static function successCount(): int
    {
        return static::where('status', 'success')->count();
    }
}
