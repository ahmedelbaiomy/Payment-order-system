<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class PaymentPolicy
{

    public function pay(User $user, Order $order): bool
    {
        return $user->id === $order->user_id && $order->status === 'Pending';
    }
}
