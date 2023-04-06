<?php

namespace App\Http\Requests\Payment;

use App\Http\Requests\RequestErrors;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class PaymentRequest extends FormRequest
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
            'type'  => 'required|exists:payment_types,slug',
            'details' => 'required|json',
        ];
    }

    protected function prepareForValidation()
    {
        $details = $this->get('details');
        $this->merge([
           'details' => $details ? json_encode($details) : null
        ]);
    }

    protected function passedValidation()
    {
        $this->merge([
           'details' => json_encode($this->get('details'))
        ]);
    }

    public function messages(): array
    {
        return [
            'category_uuid.exists' => 'The selected category uuid is not in petshop.categories!',
            'brand_uuid.exists' => 'The selected brand uuid is not in found in petshop.brands!',
        ];
    }

}
