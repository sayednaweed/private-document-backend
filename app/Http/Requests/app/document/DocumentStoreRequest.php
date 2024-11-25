<?php

namespace App\Http\Requests\app\document;

use Illuminate\Foundation\Http\FormRequest;

class DocumentStoreRequest extends FormRequest
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
            'documentType' => 'required',
            'urgency' => 'required',
            'source' => 'required',
            'documentDate' => 'required',
            'documentNumber' => 'required|string|max:64',
            'subject' => 'required',
            'qaidWarida' => 'required|string|max:64',
            'qaidWaridaDate' => 'required',
            'document' => 'required|file',
            'reference' => 'required',
        ];
    }
}
