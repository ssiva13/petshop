<?php

namespace App\Http\Requests\Auth;


use Illuminate\Contracts\Validation\Rule;

class ForgotPasswordRequest extends UserRequest
{
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
            'email' => 'required|email|max:50|exists:users,email',
        ];
    }

}
