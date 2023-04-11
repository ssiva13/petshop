<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\RequestErrors;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'order_status_uuid' => 'required|exists:order_statuses,uuid',
            'payment_uuid' => 'nullable|string|exists:payments,uuid',
            'delivery_fee' => 'numeric',
            'products' => 'required',
            'address' => 'required',
        ];
    }

}
