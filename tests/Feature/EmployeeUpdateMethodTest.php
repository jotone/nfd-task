<?php

namespace Feature;

use App\Models\{Company, Employee};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class EmployeeUpdateMethodTest extends TestCase
{
    // Ensures a clean database state for each test.
    use RefreshDatabase;

    /**
     * Test updating an employee's basic details.
     *
     * This test verifies that an employee's details can be updated
     * successfully with valid input and that the changes are reflected
     * in the database.
     *
     * @return void
     */
    public function testUpdateEmployeeBasicDetails(): void
    {
        // Create an employee in the database.
        $employee = Employee::factory()->create();

        // Prepare updated data.
        $new_data = [
            'first_name' => 'UpdatedFirstName',
            'last_name' => 'UpdatedLastName',
            'email' => 'updated.email@example.com',
        ];

        $this
            // Send a PUT request to update the employee.
            ->putJson(route('api.employees.update', $employee->id), $new_data)
            // Assert HTTP 200 status.
            ->assertStatus(Response::HTTP_OK)
            // Assert the response contains the updated data.
            ->assertJsonFragment($new_data);

        // Assert the database contains the updated data.
        $this->assertDatabaseHas('employees', array_merge($new_data, ['id' => $employee->id]));
    }

    /**
     * Test updating an employee's details with associated companies.
     *
     * This test ensures that an employee's details can be updated along with
     * their associated companies and that the relationship is correctly
     * reflected in the database.
     *
     * @return void
     */
    public function testUpdateEmployeeWithCompanies(): void
    {
        // Create an employee and companies.
        $employee = Employee::factory()->create();
        $companies = Company::factory(mt_rand(1, 3))->create();

        // Prepare updated data with company associations.
        $employee_new_data = Employee::factory()->make();
        $new_data = [
            'first_name' => $employee_new_data->first_name,
            'last_name' => $employee_new_data->last_name,
            'email' => $employee_new_data->email,
            'companies' => $companies->pluck('id')->toArray(),
        ];

        $expected_response = [
            'id' => $employee->id,
            'first_name' => $employee_new_data->first_name,
            'last_name' => $employee_new_data->last_name,
            'email' => $employee_new_data->email,
        ];

        $this
            // Send a PUT request to update the employee.
            ->putJson(route('api.employees.update', $employee->id), $new_data)
            // Assert HTTP 200 status.
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment($expected_response);

        // Assert the employee's details are updated in the database.
        $this->assertDatabaseHas('employees', $expected_response);

        // Assert the companies are associated with the employee.
        foreach ($companies as $company) {
            $this->assertDatabaseHas('company_employees', [
                'employee_id' => $employee->id,
                'company_id' => $company->id,
            ]);
        }
    }

    /**
     * Test updating an employee with invalid data.
     *
     * This test verifies that validation errors are returned when the
     * request contains invalid data, such as missing required fields
     * or incorrect data types.
     *
     * @return void
     */
    public function testUpdateEmployeeWithInvalidData(): void
    {
        // Create an employee in the database.
        $employee = Employee::factory()->create();

        // Prepare invalid data.
        $invalidData = [
            'first_name' => '', // Required field missing.
            'email' => 'invalid-email', // Invalid email format.
            'companies' => [0], // Non-existent company ID.
        ];

        $this
            // Send a PUT request to update the employee with invalid data.
            ->putJson(route('api.employees.update', $employee->id), $invalidData)
            // Assert HTTP 422 status.
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            // Assert specific validation errors.
            ->assertJsonValidationErrors([
                'first_name',
                'email',
                'companies.0',
            ]);
    }
}
