<?php

namespace App\Http\Requests\Company;

use App\Http\Requests\DefaultRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class CompanyIndexRequest extends DefaultRequest
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
                Rule::in(['id', 'name', 'slug', 'tax_id', 'address', 'city', 'zip', 'created_at', 'updated_at']),
            ],
            'order.dir' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ];
    }
}
