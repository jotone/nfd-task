<?php

namespace App\Http\Requests\Employee;

use App\Http\Requests\DefaultRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class EmployeeStoreRequest extends DefaultRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:employees,email'],
            'phone' => ['nullable', 'string', 'max:31'],
            'companies' => ['nullable', 'array'],
            'companies.*' => ['required', 'exists:companies,id']
        ];
    }
}
