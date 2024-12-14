<?php

namespace App\Http\Requests\template\user;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPasswordRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "id" => ["required"],
            "newPassword" => ["required", "min:8", "max:45"],
            "confirmPassword" => ["required", "same:newPassword", "min:8", "max:45"],
        ];
    }
}
