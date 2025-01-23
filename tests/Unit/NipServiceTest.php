<?php

namespace Tests\Unit;

use App\Services\NipService;
use PHPUnit\Framework\TestCase;

class NipServiceTest extends TestCase
{
    private NipService $nip_service;

    protected function setUp(): void
    {
        parent::setUp();
        // Initialize the NipService instance before each test
        $this->nip_service = new NipService();
    }

    /**
     * Test generate() and isValid() methods.
     *
     * @return void
     */
    public function testGenerateGeneratesValidNip(): void
    {
        // Generate a NIP.
        $nip = $this->nip_service->generate();

        // Assert the NIP is 10 characters long and numeric.
        $this->assertMatchesRegularExpression('/^\d{10}$/', $nip);

        // Assert the generated NIP is valid.
        $this->assertTrue($this->nip_service->isValid($nip));
    }

    /**
     * Test the isValid() method with an invalid NIP.
     *
     * @return void
     */
    public function testNipInvalidValues(): void
    {
        // Test case list
        $invalid_values = [
            '0000000000', // Test with "0000000000" (special case).
            '1234567890', // Test with an invalid NIP (e.g., incorrect checksum).
            'ABCDEFGHIJ'  // Test with a non-numeric input.
        ];

        foreach ($invalid_values as $value) {
            // Assert the NIP is invalid.
            $this->assertFalse($this->nip_service->isValid($value));
        }
    }
}
