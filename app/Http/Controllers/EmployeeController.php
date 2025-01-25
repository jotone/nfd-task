<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employee\{EmployeeIndexRequest, EmployeeStoreRequest, EmployeeUpdateRequest};
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EmployeeController extends Controller
{
    /**
     * Listing of employees with optional pagination and sorting.
     *
     * @param EmployeeIndexRequest $request The current HTTP request instance with validated input for pagination and sorting.
     * @return AnonymousResourceCollection A JSON response containing a paginated list of employees or all employees if pagination is disabled.
     */
    public function index(EmployeeIndexRequest $request): AnonymousResourceCollection
    {
        // Retrieve $take, $order_by and $order_dir variables from the request
        ['take' => $take, 'order_by' => $order_by, 'order_dir' => $order_dir] = $this->getIndexRequestParams($request);

        // Create a query for employees, optionally filtering by companies if provided in the request.
        $collection = Employee::when(
            value: $request->has('companies'),
            callback: fn ($query) => $query->whereHas(
                relation: 'companies',
                callback: fn ($q) => $q->whereIn('companies.id', $request->get('companies', []))
            )
        )->orderBy($order_by, $order_dir);

        // Return paginated data if static::$take is > 0, otherwise retrieve all.
        return EmployeeResource::collection($take > 0 ? $collection->paginate($take) : $collection->get());
    }

    /**
     * Display the specified employee's details.
     *
     * This method retrieves a specific employee's data, formats it using
     * the `EmployeeResource` for consistent API responses, and returns it
     * as a JSON response with an HTTP 200 status.
     *
     * @param Employee $employee The employee instance provided via route model binding.
     * @return JsonResponse The JSON response containing the employee's details.
     */
    public function show(Employee $employee): JsonResponse
    {
        return response()->json(
            data: new EmployeeResource($employee),
            status: Response::HTTP_OK
        );
    }

    /**
     * Store a newly created employee in the database.
     *
     * This method handles the creation of a new employee and associates it with companies
     * if provided. The process is wrapped in a database transaction to ensure data integrity.
     *
     * @param EmployeeStoreRequest $request The validated request containing employee data.
     * @return JsonResponse The JSON response with the created employee resource.
     */
    public function store(EmployeeStoreRequest $request): JsonResponse
    {
        // Extract validated data from the request.
        $args = $request->validated();

        try {
            // Use a database transaction to create the company.
            $employee = DB::transaction(
                callback: function () use ($args) {
                    // Create a new employee using the validated data.
                    $employee = Employee::create($args);
                    // If the 'companies' field is present, attach the companies to the employee.
                    if (!empty($args['companies'])) {
                        $employee->companies()->attach($args['companies']);
                    }
                    // Return the created employee instance.
                    return $employee;
                },
                attempts: self::DB_ATTEMPTS
            );
        } catch (\Exception $e) {
            // Return an error response in case of failure.
            return $this->errorResponse($e);
        }

        // Return the newly created company resource.
        return response()->json(
            data: new EmployeeResource($employee),
            status: Response::HTTP_CREATED
        );
    }

    /**
     * Update the specified employee in the database.
     *
     * This method updates the employee's details and synchronizes associated companies if provided.
     * The process is wrapped in a database transaction to ensure data integrity.
     *
     * @param Employee $employee The employee instance to be updated.
     * @param EmployeeUpdateRequest $request The validated request containing employee data.
     * @return JsonResponse The JSON response with the updated employee resource.
     */
    public function update(Employee $employee, EmployeeUpdateRequest $request): JsonResponse
    {
        // Extract validated data from the request.
        $args = $request->validated();

        try {
            // Use a database transaction to ensure data consistency.
            DB::transaction(
                callback: function () use ($args, $employee) {
                    // Update the employee's basic details.
                    $employee->update($args);

                    // Synchronize the companies if provided.
                    if (!empty($args['companies'] ?? [])) {
                        $employee->companies()->sync($args['companies']);
                    }
                },
                attempts: self::DB_ATTEMPTS
            );
        } catch (\Exception $e) {
            // Return an error response in case of failure.
            return $this->errorResponse($e);
        }

        // Return a JSON response with the updated employee resource.
        return response()->json(
            data: new EmployeeResource($employee->fresh()), // Fetch the latest data after update.
            status: Response::HTTP_OK
        );
    }

    /**
     * Delete an employee.
     *
     * @param Employee $employee The employee instance to delete.
     * @return JsonResponse An empty response with a 204 No Content status.
     */
    public function destroy(Employee $employee): JsonResponse
    {
        try {
            // Delete the employee instance.
            DB::transaction(
                callback: fn () => $employee->delete(),
                attempts: self::DB_ATTEMPTS
            );
        } catch (\Exception $e) {
            // Return an error response in case of failure.
            return $this->errorResponse($e);
        }

        // Return an empty response with HTTP 204 status.
        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
