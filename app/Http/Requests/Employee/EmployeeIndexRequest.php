<?php

namespace App\Http\Requests\Employee;

use App\Http\Requests\DefaultRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class EmployeeIndexRequest extends DefaultRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'take' => ['sometimes', 'numeric', 'min:0'],
            'order.by' => [
                'sometimes',
                'string',
                Rule::in(['id', 'first_name', 'last_name', 'email', 'phone', 'created_at', 'updated_at']),
            ],
            'order.dir' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'companies' => ['sometimes', 'array'],
            'companies.*' => ['sometimes', 'numeric', 'exists:companies,id'],
        ];
    }
}
