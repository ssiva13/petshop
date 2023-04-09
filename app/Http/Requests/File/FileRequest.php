<?php

namespace App\Http\Requests\File;

use App\Http\Requests\RequestErrors;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class FileRequest extends FormRequest
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
            'file' => 'required|max:10000|mimes:doc,docx,png,jpeg,jpg,pdf',
        ];
    }

    protected function prepareForValidation()
    {
        $file = $this->file('file');
        $this->merge([
            'name' => $file->getClientOriginalName(),
            'type' => $file->getMimeType(),
            'file' => $file,
            'size' => $file->getSize(),
            'path' => 'public/pet-shop',
            // 'path' => storage_path('app/files'). "/{$file->getClientOriginalName()}",
        ]);
    }
}
