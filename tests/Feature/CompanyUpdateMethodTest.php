<?php

namespace Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CompanyUpdateMethodTest extends TestCase
{
    // Ensures a clean database state for each test.
    use RefreshDatabase;

    /**
     * Test successfully updating a company's information.
     *
     * This test verifies that a company's details can be updated via the API endpoint,
     * and that the updated data is accurately stored in the database.
     */
    public function testUpdateCompany(): void
    {
        // Create a company in the database.
        $company = Company::factory()->create();

        // Data for updating the company.
        $new_company_data = Company::factory()->make();

        // Prepare the request data with updated information.
        $request_data = [
            'name' => $new_company_data->name,
            'tax_id' => $new_company_data->tax_id,
            'address' => $new_company_data->address,
            'city' => $new_company_data->city,
            'zip' => $new_company_data->zip,
        ];

        $this
            // Send a PUT request to the update endpoint.
            ->putJson(route('api.companies.update', $company->id), $request_data)
            // Assert the response status is 200 (OK).
            ->assertStatus(Response::HTTP_OK)
            // Assert the response contains the updated company data.
            ->assertJson(array_merge($request_data, [
                'id' => $company->id,
            ]));

        // Assert the database contains the updated company data.
        $this->assertDatabaseHas('companies', array_merge($request_data, [
            'slug' => mb_strtolower(Str::slug(Str::ascii($request_data['name']))),
            'id' => $company->id,
        ]));
    }
}
