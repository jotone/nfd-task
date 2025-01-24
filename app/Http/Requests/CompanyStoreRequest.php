<?php

namespace App\Http\Requests;

use App\Rules\NipVerificationRule;
use Illuminate\Contracts\Validation\ValidationRule;

class CompanyStoreRequest extends DefaultRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:255'],
            'tax_id'  => ['required', 'string', 'max:255', new NipVerificationRule()],
            'address' => ['required', 'string', 'max:255'],
            'city'    => ['required', 'string', 'max:255'],
            'zip'     => ['required', 'string', 'max:255'],
        ];
    }
}
