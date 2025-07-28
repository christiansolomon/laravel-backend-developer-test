<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        // Eager load necessary relationships
        $orders = Order::with([
            'customer:id,name',
            'items.product:id,name', // assuming product name is relevant
            'cartItems' => function ($query) {
                $query->orderByDesc('created_at');
            },
        ])->get();

        $orderData = $orders->map(function ($order) {
            $totalAmount = $order->items->sum(function ($item) {
                return $item->price * $item->quantity;
            });

            $itemsCount = $order->items->count();

            $lastAddedToCart = optional($order->cartItems->first())->created_at;

            // already have status, no need for query
            $completedOrderExists = $order->status === 'completed';

            return [
                'order_id' => $order->id,
                'customer_name' => optional($order->customer)->name,
                'total_amount' => $totalAmount,
                'items_count' => $itemsCount,
                'last_added_to_cart' => $lastAddedToCart,
                'completed_order_exists' => $completedOrderExists,
                'completed_at' => $order->status === 'completed' ? $order->completed_at : null,
                'created_at' => $order->created_at,
            ];
        });

        // Sort using completed_at
        $sorted = $orderData->sortByDesc('completed_at')->values();

        return view('orders.index', ['orders' => $sorted]);
    }
}

/* 
Assumptions: To make this refactor work efficiently, 
the below relationships must be defined in the Order model

public function customer()
{
    return $this->belongsTo(Customer::class);
}

public function items()
{
    return $this->hasMany(OrderItem::class);
}

public function cartItems()
{
    return $this->hasMany(CartItem::class);
}

*/