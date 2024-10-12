<?php

namespace App\Repositories;

use App\Http\Resources\OrderResource;
use App\Models\Order;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function getOrdersWithFilters($userId, $filters)
    {
//        $query = $this->model->where('user_id', $userId);
        $query = $this->model->where('user_id', 1);

        if (isset($filters['status'])) {
            $query->status($filters['status']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->dateRange($filters['start_date'], $filters['end_date']);
        }

        return $query->get();
    }

    public function cancel(Order $order)
    {
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to cancel this order',
            ], 403);
        }
        if ($order->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be canceled as it is not in a cancellable state',
            ], 403);
        }

        $order->status = 'Canceled';
        $order->save();
        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
            'message' => 'Order has been canceled successfully.',
        ], 200);
    }
}
