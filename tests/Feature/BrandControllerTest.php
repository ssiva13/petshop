<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class BrandControllerTest extends TestCase
{
    use RefreshDatabase;


    public function test_create_brand(): void
    {
        $brand = [
            'title' => 'Apple iPhone'
        ];
        $response = $this->postJson(route('brand.create'), $brand);
        $response->assertStatus(401);

        $token = Cache::get('jwt_token');

        // Make a request to the endpoint with the JWT
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson(route('brand.create'), $brand);

        // Assert that the response status code is 200 (success)
        $response->assertStatus(Response::HTTP_CREATED);
    }


    public function test_list_brands(): void
    {
        $token = Cache::get('jwt_token');

        // Make a request to the endpoint with the JWT
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get(route('brands'));

        // Assert that the response status code is 200 (success)
        $response->assertStatus(Response::HTTP_OK);
    }


}
