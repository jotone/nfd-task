<?php

namespace Feature;

use App\Http\Controllers\EmployeeController;
use App\Models\{Company, Employee};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class EmployeeIndexMethodTest extends TestCase
{
    // Ensures a clean database state for each test.
    use RefreshDatabase;

    /**
     * Test the default pagination of the index method.
     *
     * This test verifies that the response contains the default number of employees
     * as defined in the controller and that the structure and order of the response
     * match expectations.
     */
    public function testIndexDefaultPagination(): void
    {
        // Create 30 employees in the database.
        $employees = Employee::factory()->count(30)->create();

        // Send a GET request to the index endpoint.
        $response = $this
            ->getJson(route('api.employees.index'))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'first_name', 'last_name', 'email', 'phone', 'created_at', 'updated_at'],
                ],
                'meta',
                'links',
            ]);

        // Assert the number of employees matches the default pagination value.
        $this->assertCount(EmployeeController::$take, $response->json('data'));

        // Verify the order of the returned data matches the expected order.
        $data = $response->json('data');
        $last_index = EmployeeController::$take - 1;
        $this->assertEquals($employees[0]->id, $data[0]['id']);
        $this->assertEquals($employees[$last_index]->id, $data[$last_index]['id']);
    }

    /**
     * Test the index method returns all employees if 'take' is set to 0.
     *
     * This test ensures that passing a 'take' parameter of 0 results in returning
     * all employees in the database.
     */
    public function testIndexReturnsAll(): void
    {
        // Create 20 employees in the database.
        Employee::factory()->count(20)->create();

        // Send a GET request to the index endpoint with 'take' = 0.
        $response = $this
            ->getJson(route('api.employees.index', ['take' => 0]))
            // Assert that the response status is 200 (OK).
            ->assertStatus(Response::HTTP_OK);

        // Assert the response contains all 20 employees.
        $this->assertCount(20, $response->json('data'));
    }

    /**
     * Test the 'take' parameter of the index method.
     *
     * This test verifies that the response contains the number of employees specified
     * by the 'take' parameter.
     */
    public function testIndexTakeParameter(): void
    {
        // Create 30 employees in the database.
        Employee::factory()->count(30)->create();

        // Send a GET request with 'take' = 25.
        $response = $this
            ->getJson(route('api.employees.index', ['take' => 25]))
            // Assert the response status is 200 (OK).
            ->assertStatus(Response::HTTP_OK);

        // Assert the response contains 25 employees.
        $this->assertCount(25, $response->json('data'));
    }

    /**
     * Test the API endpoint for fetching employees with pagination.
     *
     * This test ensures that the `api.employees.index` endpoint correctly handles
     * the `take` and `page` query parameters, returning the expected data subset
     * in the proper order.
     */
    public function testIndexPageParameter(): void
    {
        // Number of items to take per page.
        $take = 5;
        // The page number to fetch.
        $page = 2;

        // Create 30 employees in the database.
        $employees = Employee::factory()->count(30)->create();

        // Send a GET request with 'take' = 25.
        $response = $this
            ->getJson(route('api.employees.index', ['take' => $take, 'page' => $page]))
            // Assert the response status is 200 (OK).
            ->assertStatus(Response::HTTP_OK);

        // Extract the 'data' key from the JSON response.
        $data = $response->json('data');
        // Verify that the first item in the response matches the expected employee
        $this->assertEquals($employees[$take]->id, $data[0]['id']);
        // Verify that the last item in the response matches the expected employee.
        $this->assertEquals($employees[$take * $page - 1]->id, $data[4]['id']);
    }

    /**
     * Test the 'order' parameter of the index method.
     *
     * This test ensures that the 'order' parameter correctly sorts the employees
     * by the specified field and direction.
     */
    public function testIndexOrderParameter(): void
    {
        // Create 3 employees in the database.
        $employees = Employee::factory()->count(3)->create();

        // Send a GET request with 'order' parameter for sorting by email in descending order.
        $response = $this->getJson(route('api.employees.index', [
            'order' => [
                'by' => 'email',
                'dir' => 'desc',
            ],
        ]));

        // Assert the response status is 200 (OK).
        $response->assertStatus(Response::HTTP_OK);
        // Verify the order of the returned data matches the expected descending order by email.
        $data = $response->json('data');
        $this->assertEquals(
            $employees->sortByDesc('email')->first()->email,
            $data[0]['email']
        );
    }

    /**
     * Test filtering employees by company_id through the pivot table.
     *
     * This test ensures that the `index` endpoint correctly filters employees
     * based on the provided `company_id` in the request and excludes employees
     * not associated with the specified company.
     *
     * @return void
     */
    public function testIndexFilterByCompanyId(): void
    {
        // Create two companies using the factory
        $companies = Company::factory(2)->create();

        // Create a random number of employees between 11 and 20. Chunk the employees into two groups (10 per group)
        $employees = Employee::factory(mt_rand(11, 20))->create()->chunk(10);

        // Attach the first group of employees to the first company
        $companies[0]->employees()->attach($employees[0]);
        // Attach the second group of employees to the second company
        $companies[1]->employees()->attach($employees[1]);

        $response = $this
            // Send a GET request to the `index` endpoint with a filter for the first company's ID
            ->json('GET', route('api.employees.index'), [
                'companies' => [$companies[0]->id],
            ])
            // Assert the response is successful
            ->assertStatus(Response::HTTP_OK);

        // Assert that all employees from the first company are included in the response
        foreach ($employees[0] as $employee) {
            $response->assertJsonFragment([
                'id' => $employee->id,
            ]);
        }
        // Assert that no employees from the second company are included in the response
        foreach ($employees[1] as $employee) {
            $response->assertJsonMissing([
                'id' => $employee->id,
            ]);
        }
    }
}
