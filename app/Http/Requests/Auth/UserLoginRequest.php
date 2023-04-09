<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\RequestErrors;

class UserLoginRequest extends UserRequest
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
     * @return array
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255|exists:users',
            'password' => 'required|string',
        ];
    }
}
