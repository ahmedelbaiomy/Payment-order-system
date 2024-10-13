<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentProcessTest extends TestCase
{

    public function test_authenticated_user_can_make_a_payment_successfully()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
              'product_id' => 9,
              'quantity' => 1,
              'user_id' => $user->id,
            'total_price' => 100.00,
          ]);

        $this->actingAs($user);

        Http::fake([
            'api.stripe.com/v1/payment_intents' => Http::sequence()
                ->push(['id' => 'pi_test_payment', 'status' => 'succeeded'])
        ]);

        $response = $this->postJson('/api/payments/' . $order->id, [
            'payment_method_id' => 'pm_card_visa',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Payment successful',
            'order' => [
                'id' => $order->id,
                'status' => 'Paid'
            ]
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'Paid',
        ]);
    }
}
