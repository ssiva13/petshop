<?php

namespace App\Http\Requests\Auth;


use Illuminate\Contracts\Validation\Rule;

class ResetPasswordRequest extends UserRequest
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
            'token' => 'required|string',
            'email' => 'required|email|max:50',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

}
