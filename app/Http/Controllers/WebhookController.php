<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {

            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try{
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);

        }catch (\UnexpectedValueException $e){
            \Log::error('Invalid Payload ' . $e->getMessage());
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            \Log::error('Invalid Signature' . $e->getMessage());
        }


        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            $order = Order::where('id', $paymentIntent->metadata->order_id)->first();
            \Log::info('Metadata' . $paymentIntent->metadata);

            if ($order) {
                $order->update(['status' => 'Paid']);
            } else {
                \Log::error('Order not found for the given order_id');
            }
        } elseif ($event->type === 'payment_intent.payment_failed') {
            $paymentIntent = $event->data->object;
            $order = Order::where('id', $paymentIntent->metadata->order_id)->first();
            if ($order) {
                $order->update(['status' => 'Canceled']);

                Payment::create([
                    'order_id' => $order->id,
                    'payment_status' => 'failed',
                    'transaction_id' => $paymentIntent->id,
                ]);

                \Log::info('Order canceled and payment logged as failed.', [
                    'order_id' => $order->id,
                    'payment_intent_id' => $paymentIntent->id,
                ]);
            } else {
                \Log::warning('Order not found for the payment failure', ['order_id' => $paymentIntent->metadata->order_id]);
            }
        }
        return response()->json(['status' => 'success'], 200);


    }
}
