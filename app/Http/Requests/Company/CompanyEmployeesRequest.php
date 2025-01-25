<?php

namespace App\Http\Requests\Company;

use App\Http\Requests\DefaultRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class CompanyEmployeesRequest extends DefaultRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'list'   => ['required', 'array'],
            'list.*' => ['required', 'exists:employees,id']
        ];
    }
}
