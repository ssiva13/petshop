<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class OrderStatusControllerTest extends TestCase
{
    use DatabaseTransactions;

//    public function test_create_order_status(): void
//    {
//        $data = [
//            'title' => 'Test Order Status',
//            'slug' => 'This-status.'
//        ];
//
//        $response = $this->postJson(route('order-status.create'), $data);
//
//        // Assert that the response status code is 401 (unauthorized)
//        $response->assertStatus(401);
//
//        $response->assertStatus(Response::HTTP_OK);
//        $this->assertDatabaseHas('order_statuses', $data);

//    }

    public function test_get_all_order_status(): void
    {

        $response = $this->get(route('order-statuses'));
        $response->assertStatus(Response::HTTP_OK);

    }

}
