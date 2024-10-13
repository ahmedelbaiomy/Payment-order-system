<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{


    public function cancel(User $user, Order $order)
    {
        return $user->id === $order->user_id && $order->status === 'Pending';
    }



}
