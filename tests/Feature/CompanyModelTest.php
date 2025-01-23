<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyModelTest extends TestCase
{
    // Automatically rolls back the database after each test.
    use RefreshDatabase;

    /**
     * Test creating a new Company model.
     *
     * This test verifies that a new Company instance can be successfully created
     * and persists in the database.
     */
    public function testCreateModel(): void
    {
        // Create a new company instance.
        $company = Company::factory()->create();
        // Assert the model exists in the database.
        $this->assertModelExists($company);
    }

    /**
     * Test attempting to create a duplicate Company model.
     *
     * This test ensures that a duplicate Company instance cannot be created
     * with the same slug and validates that duplicate handling is enforced.
     */
    public function testAttemptToDuplicate(): void
    {
        // Create the original company.
        $company = Company::factory()->create();
        // Attempt to duplicate the company.
        $company_duplication = Company::create($company->toArray());
        // Assert that the duplicate company has a different slug than the original
        $this->assertFalse($company->slug === $company_duplication->slug);
    }

    /**
     * Test updating an existing Company model.
     *
     * This test ensures that updating a company instance reflects changes in the database
     * while removing old data.
     */
    public function testUpdateModel(): void
    {
        // Create a company instance.
        $company = Company::factory()->create();
        // Verify the model exists.
        $this->assertModelExists($company);
        // Store the original data.
        $company_data = $company->toArray();

        // Generate new data for update.
        $new_company_data = Company::factory()->make()->toArray();
        // Update the company.
        $company->update($new_company_data);

        $this
            // Verify old data is removed.
            ->assertDatabaseMissing('companies', $company_data)
            // Verify new data exists in the database.
            ->assertDatabaseHas('companies', array_merge($new_company_data, [
                'id' => $company_data['id'],
            ]));
    }

    /**
     * Test the Employee relationship with the Company model.
     *
     * This test verifies that employees can be associated with a company
     * and the relationship is correctly stored in the database.
     */
    public function testEmployeeRelation(): void
    {
        // Create a company instance.
        $company = Company::factory()->create();
        // Create an employee instance.
        $employee = Employee::factory()->create();
        // Attach the employee to the company.
        $company->employees()->attach($employee);
        // Verify the relationship is stored in the pivot table.
        $this->assertDatabaseHas('company_employees', [
            'company_id' => $company->id,
            'employee_id' => $employee->id,
        ]);
        // Verify the company model's `employees` relationship contains the employee.
        $this->assertTrue($company->employees->contains($employee));
    }

    /**
     * Test deleting a Company model.
     *
     * This test ensures that a company instance can be deleted
     * and is no longer present in the database.
     */
    public function testDeleteModel(): void
    {
        // Create a company instance.
        $company = Company::factory()->create();
        // Create an employee instance.
        $employee = Employee::factory()->create();

        // Attach the employee to the company (establish relationship).
        $company->employees()->attach($employee);

        // Delete the company.
        $company->delete();

        $this
            // Assert that the company no longer exists in the database.
            ->assertModelMissing($company)
            // Assert that the relationship in the pivot table is also removed.
            ->assertDatabaseMissing('company_employees', [
                'company_id' => $company->id,
                'employee_id' => $employee->id,
            ]);
    }
}
