<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
//    use RefreshDatabase;

    /** @test */
    public function unauthorized_user_cannot_update_other_users_order()
    {
       $user1=  User::factory()->create();
       $user2=  User::factory()->create();

        $product = Product::factory()->create();
        $order = Order::factory()->create([
           'user_id'=>$user1->id,
            'product_id'=>$product->id,
        ]);

        $response = $this->actingAs($user2)->postJson('/api/orders/'.$order->id.'/cancel', []);

        $response->assertStatus(403);
    }


    /** @test */
    public function authorized_user_can_update_their_own_order()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $order = Order::factory()->create([
            'product_id' => 9,
            'quantity' => 1,
            'user_id' => $user->id,
        ]);


        $response = $this->postJson('/api/orders/'.$order->id.'/cancel', []);
        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals('Canceled', $order->status);
    }
}
