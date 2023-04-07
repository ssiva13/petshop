<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\RequestErrors;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Lcobucci\JWT\Token\Parser;

class OrderRequest extends FormRequest
{
    use RequestErrors;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'order_status_uuid'  => 'required|exists:order_statuses,uuid',
            'payment_uuid'  => 'string|exists:payments,uuid',
            'delivery_fee' => 'numeric',
            'products' => 'required|json',
            'address' => 'required|json',
        ];
    }

    protected function prepareForValidation()
    {
        $products = $this->get('products');
        $address = $this->get('address');
        $this->merge([
           'products' => $products ? json_encode($products) : null,
           'address' => $address ? json_encode($address) : null,
        ]);
    }

}
