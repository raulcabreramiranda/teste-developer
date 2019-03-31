<?php

namespace App\Repositories;

use App\Order;
use Illuminate\Database\Eloquent\Collection;

class EloquentOrdersRepository implements OrdersRepository
{
    public function search(string $query = ""): Collection
    {
        return Order::where('user_id', 'like', "%{$query}%")
        ->orWhere('item_description', 'like', "%{$query}%")
        ->orWhere('item_quantity', 'like', "%{$query}%")
        ->orWhere('item_price', 'like', "%{$query}%")
            ->get();
    }
}
