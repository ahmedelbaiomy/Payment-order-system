<?php

namespace App\Repositories;

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
}
