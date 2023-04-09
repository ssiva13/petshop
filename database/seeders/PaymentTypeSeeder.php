<?php

namespace Database\Seeders;

use App\Http\Requests\PaymentType\PaymentTypeRequest;
use App\Models\PaymentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;

class PaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentTypeRequest = new PaymentTypeRequest();
        $paymentTypes = [
            [
                'slug' => 'credit_card',
                'title' => 'Credit Card',
            ],
            [
                'slug' => 'cash_on_delivery',
                'title' => 'Cash On Delivery',
            ],
            [
                'slug' => 'bank_transfer',
                'title' => 'Bank Transfer',
            ],
        ];
        foreach ($paymentTypes as $paymentType) {
            $validator = Validator::make($paymentType, $paymentTypeRequest->rules());
            if ($validator->passes()) {
                PaymentType::create($paymentType);
            }
        }
    }
}
