<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterOrdersRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Resources\OrderResource;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderRepository;
    protected $productRepository;

    public function __construct(OrderRepository $orderRepository, ProductRepository $productRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(FilterOrdersRequest $request)
    {
        $filters = [
            'status' => $request->query('status'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date')
        ];
        $orders = $this->orderRepository->getOrdersWithFilters(1,$filters);
        if ($orders->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'No orders found',
            ], 200);
        }
        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders),
            'message' => 'Orders retrieved successfully',
        ], 200);
    }
    public function store(StoreOrderRequest $request)
    {
        $product = $this->productRepository->find($request->product_id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $totalPrice = $product->price * $request->quantity;

        $order = $this->orderRepository->create([
//            'user_id' => auth()->id(),
            'user_id' => 1,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'total_price' => round($totalPrice, 2),
            'status' => 'Pending',
        ]);

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
            'message' => 'Order created successfully',
        ], 201);
    }

    public function cancel(Order $order)
    {
        return $this->orderRepository->cancel($order);
    }
}
