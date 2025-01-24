<?php

namespace Feature;

use App\Models\{Company, Employee};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CompanyDetachEmployeesTest extends TestCase
{
    // Ensures a clean database state for each test.
    use RefreshDatabase;

    /**
     * Test detaching employees from a company successfully.
     *
     * This test verifies that employees can be successfully detached from a company
     * and that the database is updated accordingly.
     */
    public function testDetachEmployeesSuccessfully(): void
    {
        // Create a company and some employees.
        $company = Company::factory()->create();
        $employees = Employee::factory()->count(3)->create();

        // Attach employees to the company.
        $company->employees()->attach($employees);

        // Employee IDs to detach.
        $employee_ids = $employees->pluck('id')->toArray();

        $this
            // Send a DELETE request to detach employees.
            ->deleteJson(route('api.companies.detach', $company->id), [
                'list' => $employee_ids,
            ])
            // Assert the response status is 204 (No Content).
            ->assertStatus(Response::HTTP_NO_CONTENT);

        // Assert the employees are detached from the company.
        foreach ($employee_ids as $employee_id) {
            $this->assertDatabaseMissing('company_employees', [
                'company_id' => $company->id,
                'employee_id' => $employee_id,
            ]);
        }
    }

    /**
     * Test handling invalid employee IDs when detaching employees.
     *
     * This test ensures that the API returns a 422 Unprocessable Entity status
     * when provided with invalid or non-existent employee IDs.
     */
    public function testDetachEmployeesHandlesInvalidIds(): void
    {
        // Create a company and some employees.
        $company = Company::factory()->create();

        // Attach some employees to the company.
        $employees = Employee::factory()->count(3)->create();
        $company->employees()->attach($employees);

        // Send a DELETE request with non-existent employee IDs.
        $response = $this->deleteJson(route('api.companies.detach', $company->id), [
            'employees' => [99999, 0], // Non-existent IDs
        ]);

        // Assert the response status is 422 (Unprocessable Entity).
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // Assert the response contains a validation error for the employees field.
        $response->assertJsonValidationErrors(['list']);
    }

    /**
     * Test handling a non-existent company when detaching employees.
     *
     * This test ensures that the API returns a 404 Not Found status
     * when attempting to detach employees from a company that does not exist.
     */
    public function testDetachEmployeesHandlesNonExistentCompany()
    {
        // Send a DELETE request to detach employees from a non-existent company.
        $response = $this->deleteJson(route('api.companies.detach', 0), [
            'employees' => [1, 2, 3],
        ]);

        // Assert the response status is 404 (Not Found).
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
