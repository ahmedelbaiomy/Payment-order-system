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
                'status' => 'success',
                'data' => [],
                'message' => 'No orders found',
            ], 200);
        }
        return response()->json([
            'status' => 'success',
            'data' => OrderResource::collection($orders),
            'message' => 'Orders retrieved successfully',
        ], 200);
    }
    public function store(StoreOrderRequest $request)
    {
        $product = $this->productRepository->find($request->product_id);
        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found.',
            ], 404);
        }

        $totalPrice = $product->price * $request->quantity;
        $formattedTotalPrice = number_format($totalPrice, 2, '.', ',');

        $order = $this->orderRepository->create([
//            'user_id' => auth()->id(),
            'user_id' => 1,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'total_price' => $formattedTotalPrice,
            'status' => 'Pending',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => new OrderResource($order),
            'message' => 'Order created successfully',
        ], 201);
    }

    public function cancel(Order $order)
    {
        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found',
            ], 404);
        }
        if ($order->user_id !== auth()->id()) {
                return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to cancel this order',
            ], 403);
        }
        if ($order->status !== 'Pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Order cannot be canceled as it is not in a cancellable state',
            ], 403);
        }

        $order->status = 'Canceled';
        $order->save();
        return response()->json([
            'status' => 'success',
            'data' => new OrderResource($order),
            'message' => 'Order has been canceled successfully.',
        ], 200);
    }
}
