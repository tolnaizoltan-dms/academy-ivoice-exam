<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for validating invoice submission data.
 */
class SubmitInvoiceRequest extends FormRequest
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
            'invoiceNumber' => ['required', 'string', 'regex:/^INV-\d{4}-\d{4}$/'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'submitterId' => ['required', 'string', 'uuid'],
            'supervisorId' => ['required', 'string', 'uuid'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'invoiceNumber.regex' => 'Invoice number must be in format INV-YYYY-XXXX (e.g., INV-2025-0001).',
            'amount.gt' => 'Amount must be greater than zero.',
            'submitterId.uuid' => 'Submitter ID must be a valid UUID.',
            'supervisorId.uuid' => 'Supervisor ID must be a valid UUID.',
        ];
    }
}
