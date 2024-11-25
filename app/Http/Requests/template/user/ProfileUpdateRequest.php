<?php

namespace App\Http\Requests\template\user;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:45'],
            'email' => ['required', 'lowercase', 'email', 'max:45'],
            'name' => ['required', 'string', 'max:45'],
            'id' => ['required', 'string'],
        ];
    }
}
