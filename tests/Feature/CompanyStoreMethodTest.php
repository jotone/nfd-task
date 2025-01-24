<?php

namespace Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CompanyStoreMethodTest extends TestCase
{
    // Ensures a clean database state for each test.
    use RefreshDatabase;

    /**
     * Error message template for validation errors.
     */
    private const string VALIDATION_ERROR = 'The :attribute field is required.';

    /**
     * Test successfully storing a new company.
     *
     * This test verifies that a company can be created successfully via the API endpoint,
     * and that the created company is correctly stored in the database.
     */
    public function testStoreCompany(): void
    {
        // Create a company instance without persisting it to the database.
        $company = Company::factory()->make();

        // Prepare the input data for creating the company.
        $data = [
            'name' => $company->name,
            'tax_id' => $company->tax_id,
            'address' => $company->address,
            'city' => $company->city,
            'zip' => $company->zip,
        ];

        $this
            // Send a POST request to the store endpoint with the data.
            ->postJson(route('api.companies.store'), $data)
            // Assert the response status is 201 (Created).
            ->assertStatus(Response::HTTP_CREATED)
            // Assert the response contains the input data.
            ->assertJson($data);

        // Add the generated slug to the data for database assertion.
        $data['slug'] = mb_strtolower(Str::slug(Str::ascii($data['name'])));

        // Assert the company is present in the database with the correct data.
        $this->assertDatabaseHas('companies', $data);
    }

    /**
     * Test validation errors when storing a company.
     *
     * This test verifies that the API returns appropriate validation errors when
     * the input data for creating a company is invalid or incomplete.
     */
    public function testStoreCompanyValidation(): void
    {
        // Input data for creating a company.
        $company = Company::factory()->create([
            'tax_id' => '12345678',
        ]);

        $this
            // Send a POST request to the store endpoint.
            ->postJson(route('api.companies.store'), $company->toArray())
            // Assert the response status is 422 (Unprocessable Entity Error).
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            // Assert the response contains an error message.
            ->assertJson([
                'errors' => [
                    'tax_id' => [
                        'The tax_id must be a valid NIP number.',
                    ],
                ],
            ]);

        // Send a POST request with invalid data (missing required fields).
        $this->postJson(route('api.companies.store'))
            // Assert the response status is 422 (Unprocessable Entity).
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            // Assert the response contains validation error messages.
            ->assertJsonValidationErrors([
                'name' => [preg_replace('/:attribute/', 'name', self::VALIDATION_ERROR),],
                'tax_id' => [preg_replace('/:attribute/', 'tax id', self::VALIDATION_ERROR),],
                'address' => [preg_replace('/:attribute/', 'address', self::VALIDATION_ERROR),],
                'city' => [preg_replace('/:attribute/', 'city', self::VALIDATION_ERROR),],
                'zip' => [preg_replace('/:attribute/', 'zip', self::VALIDATION_ERROR),],
            ]);
    }
}
