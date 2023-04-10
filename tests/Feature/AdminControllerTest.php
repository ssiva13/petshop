<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Lcobucci\JWT\Token\Parser;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_create_admin(): void
    {
        $adminTestUser = [
            'first_name' => 'Simon',
            'last_name' => 'Mulwa',
            'is_admin' => 1,
            'address' => 'PO BOX 254-00100',
            'phone_number' => '254707898345',
            'is_marketing' => 1,
            'email' => 'marketing_admin@buckhill.co.uk',
            'password' => 'marketing_admin',
            'password_confirmation' => 'marketing_admin',
        ];

        $response = $this->postJson(route('admin.create'), $adminTestUser);

        $response->assertStatus(Response::HTTP_CREATED);

        Cache::put('jwt_token', $response['data']['token'], 60);

        $this->assertDatabaseHas('users', [
            'email' => 'marketing_admin@buckhill.co.uk',
        ]);

        $this->assertNotNull($response['data']['token']);

        // Modify the cached value
    }

    /**
     * @depends test_create_admin
     */
    public function test_user_listing_admin(): void
    {

        // Make a request to the endpoint without the JWT
        $response = $this->get(route('admin.user-listing'));
        $response->assertStatus(401);

        $token = Cache::get('jwt_token');

        // Make a request to the endpoint with the JWT
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get(route('admin.user-listing'));

        // Assert that the response status code is 200 (success)
        $response->assertStatus(200);
    }

//    /**
//     * @depends test_create_admin
//     */
//    public function test_admin_edit_user(): void
//    {
//        $adminTestUser = [
//            'first_name' => 'Mulwa',
//            'last_name' => 'Simon',
//            'is_admin' => 1,
//            'address' => 'PO',
//            'phone_number' => '254707898345',
//            'is_marketing' => 1,
//            'email' => 'marketing_admin_fix@buckhill.co.uk',
//            'password' => 'marketing_admin',
//            'password_confirmation' => 'marketing_admin',
//        ];
//        $token = Cache::get('jwt_token');
//
//        $authToken = app(Parser::class, ['token' => $token]);
//        $uuid = $authToken->claims()->get('user_uuid') ;
//
//        $response = $this->withHeaders([
//            'Authorization' => 'Bearer ' . $token,
//        ])->putJson(route('admin.user-edit',  $uuid ), $adminTestUser);
//
//        $response->assertStatus(Response::HTTP_OK);
//
//        $this->assertDatabaseHas('users', [
//            'email' => 'marketing_admin_fix@buckhill.co.uk',
//        ]);
//    }

}
