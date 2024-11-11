<?php

namespace App\Http\Requests\template\user;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilePasswordRequest extends FormRequest
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
            "oldPassword" => ["required", "min:8", "max:45"],
            "newPassword" => ["required", "different:oldPassword", "min:8", "max:45"],
            "confirmPassword" => ["required", "same:newPassword", "min:8", "max:45"],
        ];
    }
}
