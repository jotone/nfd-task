<?php

namespace Feature;

use App\Models\{Company, Employee};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CompanyDestroyMethodTest extends TestCase
{
    // Ensures a clean database state for each test.
    use RefreshDatabase;

    /**
     * Test destroying a company.
     *
     * This test verifies that a company can be successfully deleted via the API endpoint
     * and that it is removed from the database.
     */
    public function testDestroyCompany(): void
    {
        // Create a company in the database.
        $company = Company::factory()->create();

        // Send a DELETE request to the destroy endpoint.
        $response = $this->deleteJson(route('api.companies.destroy', $company->id));

        // Assert the response status is 204 (No Content).
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        // Assert the company is no longer in the database.
        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    /**
     * Test that destroying a company does not delete its employees.
     *
     * This test ensures that when a company is deleted, the associated employees remain
     * in the database and only the pivot table entries are removed.
     */
    public function testDestroyDoesNotDeleteEmployees(): void
    {
        // Create a company and some employees.
        $company = Company::factory()->create();
        $employees = Employee::factory(3)->create();

        // Attach employees to the company.
        $company->employees()->attach($employees);

        // Send a DELETE request to destroy the company.
        $response = $this->deleteJson(route('api.companies.destroy', $company->id));

        // Assert the response status is 204 (No Content).
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        // Assert the company is no longer in the database.
        $this->assertDatabaseMissing('companies', ['id' => $company->id]);

        foreach ($employees as $employee) {
            // Assert the employees are still in the database.
            $this->assertDatabaseHas('employees', ['id' => $employee->id]);

            // Assert the pivot table entries are removed.
            $this->assertDatabaseMissing('company_employees', [
                'company_id' => $company->id,
                'employee_id' => $employee->id,
            ]);
        }
    }
}
