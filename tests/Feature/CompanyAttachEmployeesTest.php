<?php

namespace Feature;

use App\Models\{Company, Employee};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CompanyAttachEmployeesTest extends TestCase
{
    // Automatically rolls back the database after each test.
    use RefreshDatabase;

    /**
     * Test attaching employees to a company.
     *
     * This test verifies that employees can be successfully attached to a company
     * via the API endpoint and that the database reflects the changes.
     */
    public function testAttachEmployees(): void
    {
        // Create a company and some employees.
        $company = Company::factory()->create();
        $employees = Employee::factory()->count(3)->create();

        // Employee IDs to attach.
        $employee_ids = $employees->pluck('id')->toArray();

        $this
            // Send a POST request to attach employees.
            ->patchJson(route('api.companies.attach', $company->id), [
                'list' => $employee_ids,
            ])
            // Assert the response status is 200 (OK).
            ->assertStatus(Response::HTTP_OK)
            // Assert the response contains the list of attached employees.
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                ],
            ]);

        // Assert the employees were attached to the company.
        foreach ($employee_ids as $employeeId) {
            $this->assertDatabaseHas('company_employees', [
                'company_id' => $company->id,
                'employee_id' => $employeeId,
            ]);
        }
    }

    /**
     * Test handling invalid employee IDs when attaching employees to a company.
     *
     * This test verifies that the API returns a 422 Unprocessable Entity status
     * when provided with invalid or non-existent employee IDs.
     */
    public function testAttachEmployeesHandlesInvalidIds(): void
    {
        // Create a company.
        $company = Company::factory()->create();

        // Send a POST request with invalid employee IDs.
        $this
            ->patchJson(route('api.companies.attach', $company->id), [
                'list' => [99999, 0], // Non-existent employee IDs.
            ])
            // Assert the response status is 422 (Unprocessable Entity).
            ->assertStatus(422);
    }

    /**
     * Test that attaching employees does not detach existing employees.
     *
     * This test ensures that new employees can be added to a company without
     * detaching employees that are already attached.
     */
    public function testAttachEmployeesDoesNotDetachExistingEmployees(): void
    {
        // Create a company and two sets of employees.
        $company = Company::factory()->create();
        $existing_employees = Employee::factory()->count(2)->create();
        $new_employees = Employee::factory()->count(3)->create();

        // Attach the existing employees first.
        $company->employees()->attach($existing_employees);

        // Attach the new employees without detaching the existing ones.
        $new_employee_ids = $new_employees->pluck('id')->toArray();

        $this
            // Send a PATCH request to attach new employees without detaching existing ones.
            ->patchJson(route('api.companies.attach', $company->id), [
                'list' => $new_employee_ids,
            ])
            // Assert the response status is 200 (OK).
            ->assertStatus(200);

        // Verify that existing employees are still attached to the company.
        foreach ($existing_employees as $employee) {
            $this->assertDatabaseHas('company_employees', [
                'company_id'  => $company->id,
                'employee_id' => $employee->id,
            ]);
        }

        // Verify that the new employees are also attached to the company.
        foreach ($new_employee_ids as $employee_id) {
            $this->assertDatabaseHas('company_employees', [
                'company_id' => $company->id,
                'employee_id' => $employee_id,
            ]);
        }
    }
}
