<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\RequestErrors;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
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
            'title' => 'required|string',
            'slug' => 'required|string',
            'content' => 'required|string',
            'metadata' => 'required|json',
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
