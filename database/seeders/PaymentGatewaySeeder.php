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
            ['slug' => 'razorpay', 'name' => 'Razorpay Gateway', 'description' => 'Accept Credit/Debit Cards, Netbanking, UPI, and Wallets via Razorpay', 'icon' => 'fa-credit-card', 'is_available' => 1, 'is_active' => 0],
            ['slug' => 'cashfree', 'name' => 'Cashfree Payments', 'description' => 'Accept Cards, UPI, Netbanking, and Pay Later via Cashfree Payment Gateway', 'icon' => 'fa-wallet', 'is_available' => 1, 'is_active' => 0],
        ];

        foreach ($gateways as $g) {
            PaymentGateway::updateOrCreate(['slug' => $g['slug']], $g);
        }
    }
}