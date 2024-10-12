<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{

    public function view(User $user, Order $order): bool
    {
        return  $user->id === $order->user_id;
    }

    public function pay(User $user, Order $order): bool
    {
        return $user->id === $order->id && $order->status === 'Pending';
    }

}
