<?php

namespace App\Http\Requests\Payment;

use App\Http\Requests\RequestErrors;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|exists:payment_types,slug',
            'details' => 'required|json',
        ];
    }

    public function messages(): array
    {
        return [
            'type.exists' => 'The selected payment type is not allowed!',
        ];
    }

    protected function prepareForValidation()
    {
        $details = $this->get('details');
        $this->merge([
            'details' => $details ? json_encode($details) : null
        ]);
    }

}
