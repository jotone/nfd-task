<?php

namespace Feature;

use App\Models\{Company, Employee};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class EmployeeDestroyMethodTest extends TestCase
{
    // Ensures a clean database state for each test.
    use RefreshDatabase;

    /**
     * Test destroying an employee.
     *
     * This test verifies that an employee can be successfully deleted via the API endpoint
     * and that it is removed from the database.
     */
    public function testDestroyEmployee(): void
    {
        // Create an employee in the database.
        $employee = Employee::factory()->create();

        // Send a DELETE request to the destroy endpoint.
        $response = $this->deleteJson(route('api.employees.destroy', $employee->id));

        // Assert the response status is 204 (No Content).
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        // Assert the employee is no longer in the database.
        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }

    /**
     * Test that destroying an employee does not delete its companies.
     *
     * This test ensures that when a company is deleted, the associated employees remain
     * in the database and only the pivot table entries are removed.
     */
    public function testDestroyDoesNotDeleteEmployees(): void
    {
        // Create an employee and some companies.
        $employee = Employee::factory()->create();
        $companies = Company::factory(mt_rand(1, 3))->create();

        // Attach companies to the employee.
        $employee->companies()->attach($companies->pluck('id')->toArray());

        // Send a DELETE request to destroy the company.
        $response = $this->deleteJson(route('api.employees.destroy', $employee->id));

        // Assert the response status is 204 (No Content).
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        // Assert the employee is no longer in the database.
        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);

        foreach ($companies as $company) {
            // Assert the companies are still in the database.
            $this->assertDatabaseHas('companies', ['id' => $company->id]);

            // Assert the pivot table entries are removed.
            $this->assertDatabaseMissing('company_employees', [
                'company_id' => $company->id,
                'employee_id' => $employee->id,
            ]);
        }
    }
}
