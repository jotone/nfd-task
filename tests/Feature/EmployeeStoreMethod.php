<?php

namespace Feature;

use App\Models\{Company, Employee};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class EmployeeStoreMethod extends TestCase
{
    // Ensures a clean database state for each test.
    use RefreshDatabase;

    /**
     * Test successful employee creation with basic data.
     *
     * This test verifies that an employee can be created using the minimum
     * required data and is properly stored in the database.
     *
     * @return void
     */
    public function testStoreEmployee(): void
    {
        // Generate a new employee instance without saving it to the database.
        $employee = Employee::factory()->make();

        // Prepare request payload with required fields.
        $data = [
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'email' => $employee->email,
        ];

        // Send a POST request to the store endpoint and validate the response.
        $this
            ->postJson(route('api.employees.store'), $data)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson($data);

        // Verify the employee exists in the database.
        $this->assertDatabaseHas('employees', $data);
    }

    /**
     * Test successful employee creation with phone and companies.
     *
     * This test verifies that an employee can be created with additional
     * optional data, including a phone number and associated companies.
     *
     * @return void
     */
    public function testStoreEmployeeWithPhoneAndCompanies(): void
    {
        // Generate a new employee instance without saving it to the database.
        $employee = Employee::factory()->make();

        // Create 1-3 random companies for association.
        $companies = Company::factory(mt_rand(1, 3))->create();

        // Prepare request payload with optional fields.
        $data = [
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'email' => $employee->email,
            'phone' => $employee->phone,
            'companies' => $companies->pluck('id')->toArray(),
        ];

        // Send a POST request and validate the response.
        $response = $this
            ->postJson(route('api.employees.store'), $data)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'email' => $employee->email,
                'phone' => $employee->phone,
            ]);

        // Verify the employee exists in the database with the phone number.
        $this->assertDatabaseHas('employees', [
            'email' => $employee->email,
            'phone' => $employee->phone,
        ]);
        // Extract the employee ID from the response.
        $employee_id = $response->json('id');

        // Verify the company associations are correctly stored in the pivot table.
        foreach ($companies as $company) {
            $this->assertDatabaseHas('company_employees', [
                'employee_id' => $employee_id,
                'company_id' => $company->id,
            ]);
        }
    }

    /**
     * Test employee creation with invalid data.
     *
     * This test ensures that validation errors are returned when the
     * request payload contains invalid or missing data.
     *
     * @return void
     */
    public function testStoreEmployeeWithInvalidData(): void
    {
        // Invalid payload data
        $data = [
            'first_name' => '', // Missing required field
            'last_name' => 'Doe',
            'email' => 'not-an-email', // Invalid email
            'companies' => [0], // Invalid company ID
        ];

        // Send POST request to the store endpoint
        $this->postJson(route('api.employees.store'), $data)
            // Assert HTTP status is 422 Unprocessable Entity
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            // Assert validation errors are present
            ->assertJsonValidationErrors([
                'first_name',
                'email',
                'companies.0',
            ]);
    }

    /**
     * Test employee creation with a duplicate email.
     *
     * This test ensures that an error is returned when attempting to
     * create an employee with an email that already exists in the database.
     *
     * @return void
     */
    public function testStoreEmployeeWithDuplicateEmail(): void
    {
        // Create an employee with a specific email in the database.
        $employee = Employee::factory()->create();

        // Prepare a payload with the duplicate email.
        $data = [
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'email' => $employee->email, // Duplicate email.
        ];

        // Send POST request to the store endpoint
        $this->postJson(route('api.employees.store'), $data)
            // Assert HTTP status is 422 Unprocessable Entity
            ->assertStatus(422)
            // Assert validation error for duplicate email
            ->assertJsonValidationErrors(['email']);
    }
}
