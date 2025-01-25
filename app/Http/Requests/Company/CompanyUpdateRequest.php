<?php

namespace App\Http\Requests\Company;

use App\Http\Requests\DefaultRequest;
use App\Rules\NipVerificationRule;
use Illuminate\Contracts\Validation\ValidationRule;

class CompanyUpdateRequest extends DefaultRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name'    => ['nullable', 'string', 'max:255'],
            'tax_id'  => ['nullable', 'string', 'max:255', new NipVerificationRule()],
            'address' => ['nullable', 'string', 'max:255'],
            'city'    => ['nullable', 'string', 'max:255'],
            'zip'     => ['nullable', 'string', 'max:255'],
        ];
    }
}
