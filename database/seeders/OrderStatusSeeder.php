<?php

namespace Database\Seeders;

use App\Http\Requests\OrderStatus\OrderStatusRequest;
use App\Models\OrderStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OrderStatus::factory(1)->create();
        $paymentTypeRequest = new OrderStatusRequest();
        $paymentTypes = [
            ['title' => 'Refunded'],
            ['title' => 'Cancelled'],
            ['title' => 'On hold'],
            ['title' => 'Completed'],
            ['title' => 'Processing'],
            ['title' => 'Pending'],
            ['title' => 'Failed'],
            ['title' => 'Pending payment'],
            ['title' => 'Checkout draft '],
        ];
        foreach ($paymentTypes as $paymentType) {
            $paymentType['slug'] = Str::slug($paymentType['title']);
            $validator = Validator::make($paymentType, $paymentTypeRequest->rules());
            if ($validator->passes()) {
                OrderStatus::create($paymentType);
            }
        }
    }
}
