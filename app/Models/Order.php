<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['order_number', 'name', 'phone', 'email', 'district', 'area', 'address', 'payment_method', 'payment_phone', 'transaction_id', 'subtotal', 'coupon_code', 'discount', 'delivery_charge', 'total', 'status', 'is_fake', 'source_ip', 'risk_score', 'risk_reasons', 'delivery_name', 'delivery_phone', 'courier_tracking_url', 'note'];

    protected $casts = ['subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'delivery_charge' => 'decimal:2', 'total' => 'decimal:2', 'is_fake' => 'boolean', 'risk_score' => 'integer', 'risk_reasons' => 'array', 'status_history' => 'array'];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            $order->status ??= 'অর্ডার গ্রহণ';
            $order->status_history = [['status' => $order->status, 'at' => ($order->created_at ?? now())->toIso8601String()]];
        });
        static::updating(function (Order $order): void {
            if ($order->isDirty('status')) {
                $history = $order->status_history ?? [];
                $history[] = ['status' => $order->status, 'at' => now()->toIso8601String()];
                $order->status_history = $history;
            }
        });
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
