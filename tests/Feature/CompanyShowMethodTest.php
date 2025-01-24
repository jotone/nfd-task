<?php

namespace Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CompanyShowMethodTest extends TestCase
{
    // Ensures a clean database state for each test.
    use RefreshDatabase;

    /**
     * Test retrieving a company by ID and slug.
     *
     * This test verifies that a company's details can be retrieved using its ID or slug,
     * and that the response contains the expected data.
     */
    public function testShowCompany(): void
    {
        // Create a company in the database.
        $company = Company::factory()->create();

        // Expected response data.
        $response_data = [
            'id'         => $company->id,
            'name'       => $company->name,
            'slug'       => $company->slug,
            'tax_id'     => $company->tax_id,
            'address'    => $company->address,
            'city'       => $company->city,
            'zip'        => $company->zip,
            'created_at' => $company->created_at->format('j/M/Y H:i'),
            'updated_at' => $company->updated_at->format('j/M/Y H:i'),
        ];

        $this
            // Send a GET request to the show endpoint with the company's ID.
            ->getJson(route('api.companies.show', $company->id))
            // Assert that the response status is 200 (OK).
            ->assertStatus(Response::HTTP_OK)
            // Assert the response contains the correct company data.
            ->assertJson($response_data);

        $this
            // Send a GET request to the show endpoint with the company's slug.
            ->getJson(route('api.companies.show', $company->slug))
            // Assert that the response status is 200 (OK).
            ->assertStatus(Response::HTTP_OK)
            // Assert the response contains the correct company data.
            ->assertJson($response_data);
    }

    /**
     * Test handling of non-existent company records.
     *
     * This test ensures that the API returns a 404 Not Found status
     * when attempting to retrieve a company by a non-existent ID or slug.
     */
    public function testShowReturnsNotFound(): void
    {
        $this
            // Send a GET request to the show endpoint with a non-existent ID.
            ->getJson(route('api.companies.show', 0))
            // Assert that the response status is 404 (Not Found).
            ->assertStatus(Response::HTTP_NOT_FOUND);

        // Send a GET request to the show endpoint with a non-existent slug.
        $this->getJson(route('api.companies.show', Str::random(200)))
            // Assert that the response status is 404 (Not Found).
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
