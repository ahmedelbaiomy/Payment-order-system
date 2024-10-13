<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderTest extends TestCase
{
    public function test_authenticated_user_can_create_order()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'product_id' => 2,
            'quantity' => 2,
            'user_id'=>$user->id
        ];

        $response = $this->postJson('/api/orders', $data);
        $response->assertStatus(201);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'product_id' => 2,
            'quantity' => 2,
            'status' => 'Pending',
        ]);
    }

    public function test_authenticated_user_can_access_order_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $order = Order::factory()->create([
            'product_id' => 9,
            'quantity' => 1,
            'user_id' => $user->id,
        ]);

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200);

    }
}
