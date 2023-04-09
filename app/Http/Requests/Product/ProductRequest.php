<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\RequestErrors;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'category_uuid' => 'required|exists:categories,uuid',
            'brand_uuid' => 'required|exists:brands,uuid',
            'title' => 'required|string',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'metadata' => 'required|json',
        ];
    }

    public function messages(): array
    {
        return [
            'category_uuid.exists' => 'The selected category uuid is not in petshop.categories!',
            'brand_uuid.exists' => 'The selected brand uuid is not in found in petshop.brands!',
        ];
    }

    protected function prepareForValidation()
    {
        $metadata = $this->get('metadata');
        $this->merge([
            'metadata' => $metadata ? json_encode($metadata) : null
        ]);
    }

    protected function passedValidation()
    {
        $this->merge([
            'metadata' => json_encode($this->get('metadata'))
        ]);
    }

}
