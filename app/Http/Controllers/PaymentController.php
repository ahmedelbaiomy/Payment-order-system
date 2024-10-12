<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessPaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function pay(ProcessPaymentRequest $request, Order $order)
    {
        try {
            $this->authorize('pay', $order);

            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            $paymentIntent =$stripe->paymentIntents->create([
                'payment_method' => $request->payment_method_id,
                'amount' => $order->total_price *100,
                'currency' => 'usd',
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
                'metadata' => [
                    'order_id' => $order->id,
                    'user_id' => auth()->id(),
                ],
            ]);
            if ($paymentIntent->status === 'succeeded') {
                $order->update(['status' => 'Paid']);
                Payment::create([
                    'order_id' => $order->id,
                    'payment_status' => 'success',
                    'transaction_id' => $paymentIntent->id,
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful',
                    'order' => $order,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not successful. Current status: ' . $paymentIntent->status,
                ], 400);
            }
        }catch (\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error'=>'Payment Failed'
            ],500);
        }
    }
}
