<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_product(): void
    {
        $brand = Brand::factory()->create([
            'slug' => 'Nokia',
            'title' => 'nokia',
        ]);
        $category = Category::factory()->create([
            'slug' => 'phone',
            'title' => 'Phone',
        ]);
        $product = [
            'price' => 250000,
            'category_uuid' => $category->uuid,
            'brand_uuid' => $brand->uuid,
            'description' => 'Dell XPS 15 Touchscreen Laptop',
            'metadata' => [
                'screen' => '6.1 inch',
                'storage' => '256 GB',
            ],
            'title' => 'Dell XPS 15',
        ];
        $response = $this->postJson(route('product.create'), $product);
        $response->assertStatus(401);

        $token = Cache::get('jwt_token');

        // Make a request to the endpoint with the JWT
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson(route('product.create'), $product);

        // Assert that the response status code is 200 (success)
        $response->assertStatus(Response::HTTP_CREATED);
    }


    public function test_list_products(): void
    {
        $token = Cache::get('jwt_token');

        // Make a request to the endpoint with the JWT
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get(route('categories'));

        // Assert that the response status code is 200 (success)
        $response->assertStatus(Response::HTTP_OK);
    }


}
