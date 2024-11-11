<?php

namespace App\Http\Requests\template\user;

use Illuminate\Foundation\Http\FormRequest;

class UserRegisterRequest extends FormRequest
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
            "fullName" => ['required', "string", "min:3", "max:45"],
            "username" => ['required', "string", "min:3", "max:45"],
            "email" => ["required", "email"],
            "password" => ["required", "string", "min:8", "max:25"],
            "role" => ["required", "string"],
            "job" => ["required", "string"],
            "destination" => ["required", "string"],
            "status" => ["required"],
            "grant" => ["string", "required"],
        ];
    }
}
