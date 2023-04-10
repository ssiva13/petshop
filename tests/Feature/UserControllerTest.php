<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_create_user(): void
    {
        $marketingUser = [
            'first_name' => 'Simon',
            'last_name' => 'Mulwa',
            'is_admin' => 0,
            'address' => 'PO BOX 254-00100',
            'phone_number' => '254707898345',
            'is_marketing' => 1,
            'email' => 'marketing_team@buckhill.co.uk',
            'password' => 'marketing_team',
            'password_confirmation' => 'marketing_team',
        ];

        $response = $this->postJson(route('user.create'), $marketingUser);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('users', [
            'email' => 'marketing_team@buckhill.co.uk',
        ]);

        $this->assertNotNull($response['data']['token']);

    }
}
