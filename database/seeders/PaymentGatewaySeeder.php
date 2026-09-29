<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentGateway;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            ['slug' => 'manual', 'name' => 'Manual / Cash on Delivery', 'description' => 'Accept payments manually via bank transfer or cash on delivery', 'icon' => 'fa-money-bill', 'is_available' => 1, 'is_active' => 0],
            ['slug' => 'upi', 'name' => 'UPI Payment Gateway', 'description' => 'Accept payments via UPI apps like Google Pay, PhonePe, Paytm', 'icon' => 'fa-mobile-alt', 'is_available' => 1, 'is_active' => 0],
        ];

        foreach ($gateways as $g) {
            PaymentGateway::updateOrCreate(['slug' => $g['slug']], $g);
        }
    }
}