<?php

namespace App\Rules;

use App\Services\NipService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class NipVerificationRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_numeric($value) || !NipService::make()->isValid($value)) {
            $fail('The ' . $attribute . ' must be a valid NIP number.');
        }
    }
}
