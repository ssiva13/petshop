<?php

namespace App\Http\Requests\PaymentType;

use App\Http\Requests\RequestErrors;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class PaymentTypeRequest extends FormRequest
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
            'title' => 'required|string|max:50',
            'slug' => 'required|string|max:50|unique:payment_types',
        ];
    }
}
