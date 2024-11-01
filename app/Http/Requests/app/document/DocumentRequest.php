<?php

namespace App\Http\Requests\app\document;

use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow the request; change this based on your needs.
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
            'document_number' => 'required|string|max:32',
            'summary' => 'required|string',
            'muqam_statement' => 'required|string',
            'qaid_warida_number' => 'required|string',
            'document_date' => 'required|date',
            'qaid_warida_date' => 'required|date',
            'type_id' => 'required|exists:types,id',
            'status_id' => 'required|exists:statuses,id',
            'urgency_id' => 'required|exists:urgencies,id',
            'source_id' => 'required|exists:sources,id',
            'scan_file' => 'required|file',
            'reciever_user_id' => 'required|exists:users,id',
        ];
    }


   
}
