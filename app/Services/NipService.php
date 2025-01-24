<?php

namespace App\Services;

class NipService
{
    /**
     *  The modulo number used for the checksum algorithm.
     */
    protected const int ALGORITHM_CHECK_NUMBER = 11;

    /**
     * The control digit threshold for invalid NIP numbers.
     */
    protected const int CONTROL_DIGIT_NUMBER = 10;

    /**
     * Weight factors used for calculating the control digit
     *
     * @var array|int[]
     */
    protected array $weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];

    /**
     * Generate a fake NIP (tax identification number).
     *
     * This method generates an 8-digit base number, calculates its control digit,  and returns the full NIP
     * as a 10-digit string. If the control digit is invalid (i.e., equals 10), it regenerates the NIP recursively.
     *
     * @return string The generated NIP as a string.
     */
    public function generate(): string
    {
        // Generate an 8-digit base number, padded with leading zeros if necessary.
        $base = str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);
        // Initialize the sum used for calculating the control digit.
        $sum = 0;

        // Loop through each digit of the base number and calculate the weighted sum.
        for ($i = 0; $i < count($this->weights); $i++) {
            $number = (int)$base[$i];
            $sum += $number * $this->weights[$i];
        }

        // Calculate the control digit as the remainder of the sum divided by 11.
        $control = $sum % self::ALGORITHM_CHECK_NUMBER;
        // If the control digit equals 10, the NIP is invalid. Regenerate it recursively.
        if ($control === self::CONTROL_DIGIT_NUMBER) {
            return $this->generate(); // Regenerate if invalid
        }
        // Concatenate the base number with the valid control digit and return the result.
        return $base . $control;
    }

    /**
     * Validate if the provided NIP (tax identification number) is valid.
     *
     * This method checks if the provided NIP is a valid 10-digit numeric string. It calculates the checksum
     * based on the first 9 digits and verifies it against the 10th digit (control digit).
     *
     * @param string $value The NIP to validate.
     * @return bool True if the NIP is valid, false otherwise.
     */
    public function isValid(string $value): bool
    {
        // Check if the input is a 10-digit numeric string and is not "0000000000".
        if (!preg_match('/^[\d]{' . self::CONTROL_DIGIT_NUMBER . '}$/', $value) || '0000000000' == $value) {
            // Invalid format or special case of all zeros.
            return false;
        }

        // Split the NIP into individual characters (digits) for processing.
        $chars = str_split($value);

        // Calculate the weighted sum of the first 9 digits using the predefined weights.
        $sum = array_sum(
            array_map(
                // Multiply each digit by its corresponding weight.
                fn ($weight, $digit) => $weight * $digit,
                // Array of weights for validation.
                $this->weights,
                // Extract the first 9 digits from the NIP.
                array_slice($chars, 0, self::CONTROL_DIGIT_NUMBER - 1)
            )
        );

        // Calculate the checksum as the sum modulo 11.
        $checksum = $sum % self::ALGORITHM_CHECK_NUMBER;

        // Check if the calculated checksum matches the 10th digit (control digit) of the NIP.
        return $checksum == $chars[self::CONTROL_DIGIT_NUMBER - 1];
    }

    /**
     * Create a new service instance.
     *
     * @return static
     */
    public static function make(): static
    {
        return new static();
    }
}
