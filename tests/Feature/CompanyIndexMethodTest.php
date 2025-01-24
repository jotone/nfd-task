<?php

namespace Feature;

use App\Http\Controllers\CompanyController;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CompanyIndexMethodTest extends TestCase
{
    // Ensures a clean database state for each test.
    use RefreshDatabase;

    /**
     * Test the default pagination of the index method.
     *
     * This test verifies that the response contains the default number of companies
     * as defined in the controller and that the structure and order of the response
     * match expectations.
     */
    public function testIndexDefaultPagination(): void
    {
        // Create 30 companies in the database.
        $companies = Company::factory()->count(30)->create();

        // Send a GET request to the index endpoint.
        $response = $this
            ->getJson(route('api.companies.index'))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'tax_id', 'address', 'city', 'zip', 'created_at', 'updated_at'],
                ],
                'meta',
                'links',
            ]);

        // Assert the number of companies matches the default pagination value.
        $this->assertCount(CompanyController::$take, $response->json('data'));

        // Verify the order of the returned data matches the expected order.
        $data = $response->json('data');
        $last_index = CompanyController::$take - 1;
        $this->assertEquals($companies[0]->id, $data[0]['id']);
        $this->assertEquals($companies[$last_index]->id, $data[$last_index]['id']);
    }

    /**
     * Test the index method returns all companies if 'take' is set to 0.
     *
     * This test ensures that passing a 'take' parameter of 0 results in returning
     * all companies in the database.
     */
    public function testIndexReturnsAll(): void
    {
        // Create 20 companies in the database.
        Company::factory()->count(20)->create();

        // Send a GET request to the index endpoint with 'take' = 0.
        $response = $this->getJson(route('api.companies.index', ['take' => 0]));

        // Assert that the response status is 200 (OK).
        $response->assertStatus(Response::HTTP_OK);

        // Assert the response contains all 20 companies.
        $this->assertCount(20, $response->json('data'));
    }

    /**
     * Test the 'take' parameter of the index method.
     *
     * This test verifies that the response contains the number of companies specified
     * by the 'take' parameter.
     */
    public function testIndexTakeParameter(): void
    {
        // Create 30 companies in the database.
        Company::factory()->count(30)->create();

        // Send a GET request with 'take' = 25.
        $response = $this->getJson(route('api.companies.index', ['take' => 25]));

        // Assert the response status is 200 (OK).
        $response->assertStatus(Response::HTTP_OK);

        // Assert the response contains 25 companies.
        $this->assertCount(25, $response->json('data'));
    }

    /**
     * Test the 'order' parameter of the index method.
     *
     * This test ensures that the 'order' parameter correctly sorts the companies
     * by the specified field and direction.
     */
    public function testIndexOrderParameter(): void
    {
        // Create 3 companies in the database.
        $companies = Company::factory()->count(3)->create();

        // Send a GET request with 'order' parameter for sorting by name in descending order.
        $response = $this->getJson(route('api.companies.index', [
            'order' => [
                'by' => 'name',
                'dir' => 'desc',
            ],
        ]));

        // Assert the response status is 200 (OK).
        $response->assertStatus(Response::HTTP_OK);
        // Verify the order of the returned data matches the expected descending order by name.
        $data = $response->json('data');
        $this->assertEquals(
            $companies->sortByDesc('name')->first()->name,
            $data[0]['name']
        );
    }
}
