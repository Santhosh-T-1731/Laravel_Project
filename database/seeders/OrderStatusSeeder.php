<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OrderStatus::insert([
            [
            'name'=>'Pending',
            'description'=>'Order placed but not yet processed.',
            ],
            [
            'name'=>'Confirmed',
            'description'=>'Payment received, and order confirmed.',
            ],
            [
            'name'=>'Processing',
            'description'=>'Order is being prepared for shipment',
            ],
            [
            'name'=>'Shipped',
            'description'=>'Order has been sent to the delivery service.',
            ],
            [
            'name'=>'Delivered',
            'description'=>'Order successfully delivered to the customer.',
            ],
            [
            'name'=>'Cancelled',
            'description'=>'Order cancelled before processing.',
            ],
            [
            'name'=>'Refunded',
            'description'=>'Payment refunded after a return or cancellation.',
            ],
        ]);
    }
}
