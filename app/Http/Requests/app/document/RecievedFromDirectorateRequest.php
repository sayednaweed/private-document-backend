<?php

namespace App\Http\Requests\app\document;

use Illuminate\Foundation\Http\FormRequest;

class RecievedFromDirectorateRequest extends FormRequest
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
            'destination_id' => 'required',
            'feedback' => 'required|string',
            'feedback_date' => 'required',
            'document' => 'required|file',
            'savedFile' => 'required_if:last,true',
            'qaidSadiraNumber' => 'required_if:last,true',
            'qaidSadiraDate' => 'required_if:last,true',
            'last' => 'required|in:true,false' // Ensure 'keep' is one of the string values: "true" or "false"
        ];
    }
}
