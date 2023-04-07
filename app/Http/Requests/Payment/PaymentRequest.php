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

    public function messages(): array
    {
        return [
            'type.exists' => 'The selected payment type is not allowed!',
        ];
    }

}
