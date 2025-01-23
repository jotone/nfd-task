<?php

namespace Feature;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeModelTest extends TestCase
{
    // Automatically rolls back the database after each test.
    use RefreshDatabase;

    /**
     * Test creating a new Employee model.
     *
     * This test verifies that an Employee instance can be created successfully
     * and persists in the database.
     */
    public function testCreateModel(): void
    {
        // Create a new employee instance.
        $employee = Employee::factory()->create();
        // Assert the model exists in the database.
        $this->assertModelExists($employee);
    }

    /**
     * Test attempting to create a duplicate Employee model.
     *
     * This test ensures that trying to create a duplicate Employee instance
     * throws a database integrity exception due to unique constraints.
     */
    public function testAttemptToDuplicate(): void
    {
        // Create the original employee.
        $employee = Employee::factory()->create();

        try {
            // Attempt to duplicate the employee with the same data.
            Employee::create($employee->toArray());
        } catch (\Exception $e) {
            // Assert that the exception is related to a unique constraint violation.
            $this->assertStringStartsWith(
                'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry',
                $e->getMessage()
            );
        }
    }

    /**
     * Test updating an existing Employee model.
     *
     * This test verifies that updating an Employee instance updates the data
     * in the database correctly and removes the old record.
     */
    public function testUpdateModel(): void
    {
        // Create an employee instance.
        $employee = Employee::factory()->create();
        // Verify the model exists.
        $this->assertModelExists($employee);
        // Store the original data.
        $employee_data = $employee->toArray();

        // Generate new data for update.
        $new_employee_data = Employee::factory()->make()->toArray();
        // Update the employee.
        $employee->update($new_employee_data);

        $this
            // Verify old data is removed.
            ->assertDatabaseMissing('employees', $employee_data)
            // Verify the new data exists in the database.
            ->assertDatabaseHas('employees', array_merge($new_employee_data, [
                'id' => $employee_data['id'],
            ]));
    }

    /**
     * Test the Company relationship with the Employee model.
     *
     * This test verifies that companies can be associated with an employee
     * and the relationship is correctly stored in the database.
     */
    public function testCompanyRelation(): void
    {
        // Create an employee instance.
        $employee = Employee::factory()->create();
        // Create a company instance.
        $company = Company::factory()->create();
        // Attach the company to the employee.
        $employee->companies()->attach($company);

        // Verify the relationship is stored in the pivot table.
        $this->assertDatabaseHas('company_employees', [
            'company_id' => $company->id,
            'employee_id' => $employee->id,
        ]);

        // Verify the employee model's `companies` relationship contains the company.
        $this->assertTrue($employee->companies->contains($company));
    }

    /**
     * Test deleting an Employee model.
     *
     * This test ensures that deleting an Employee instance removes the associated
     * records from the database, including any pivot table relationships with companies.
     */
    public function testDeleteModel(): void
    {
        // Create an employee instance.
        $employee = Employee::factory()->create();
        // Create a company instance.
        $company = Company::factory()->create();
        // Attach the company to the employee.
        $employee->companies()->attach($company);

        // Delete the employee.
        $employee->delete();

        $this
            // Assert that the employee no longer exists in the database.
            ->assertModelMissing($employee)
            // Assert that the relationship in the pivot table is also removed.
            ->assertDatabaseMissing('company_employees', [
                'company_id' => $company->id,
                'employee_id' => $employee->id,
            ]);
    }
}
