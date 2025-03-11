<?php

namespace App\Http\Requests\V1\Request;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'act_type' => ['required', 'string', 'max:255'],
            'recipient_office' => ['required', 'string', 'max:255'],
            'submission_date' => ['required', 'date'],
            'document' => ['required', 'file', 'max:2048'],
            'sign' => ['required', 'file', 'max:2048'],
        ];
    }
}
