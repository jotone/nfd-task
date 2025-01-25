<?php

namespace Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class EmployeeShowMethod extends TestCase
{
    // Ensures a clean database state for each test.
    use RefreshDatabase;

    /**
     * Test retrieving an employee by ID
     *
     * This test verifies that an employee's details can be retrieved using its ID or slug,
     * and that the response contains the expected data.
     */
    public function testShowEmployee(): void
    {
        // Create an employee in the database.
        $employee = Employee::factory()->create();

        // Expected response data.
        $response_data = [
            'id' => $employee->id,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'email' => $employee->email,
            'phone' => $employee->phone,
            'created_at' => $employee->created_at->format('j/M/Y H:i'),
            'updated_at' => $employee->updated_at->format('j/M/Y H:i'),
        ];

        $this
            // Send a GET request to the show endpoint with the employee's ID.
            ->getJson(route('api.employees.show', $employee->id))
            // Assert that the response status is 200 (OK).
            ->assertStatus(Response::HTTP_OK)
            // Assert the response contains the correct employee data.
            ->assertJson($response_data);
    }

    /**
     * Test handling of non-existent employee records.
     *
     * This test ensures that the API returns a 404 Not Found status
     * when attempting to retrieve an employee by a non-existent ID or slug.
     */
    public function testShowReturnsNotFound(): void
    {
        $this
            // Send a GET request to the show endpoint with a non-existent ID.
            ->getJson(route('api.employees.show', 0))
            // Assert that the response status is 404 (Not Found).
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
