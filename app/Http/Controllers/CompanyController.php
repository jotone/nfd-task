<?php

namespace App\Http\Controllers;

use App\Http\Requests\{CompanyEmployeesRequest, CompanyIndexRequest, CompanyStoreRequest, CompanyUpdateRequest};
use App\Http\Resources\{CompanyResource, EmployeeResource};
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies with optional pagination and sorting.
     *
     * @param CompanyIndexRequest $request The current HTTP request instance with validated input for pagination and sorting.
     * @return AnonymousResourceCollection A JSON response containing a paginated list of companies or all companies if pagination is disabled.
     */
    public function index(CompanyIndexRequest $request): AnonymousResourceCollection
    {
        // Extract 'take' and 'order' parameters from the request.
        $args = $request->only(['take', 'order']);

        // Determine the number of items per page (default: static::$take).
        $take = isset($args['take']) && $args['take'] >= 0 ? $args['take'] : static::$take;

        // Define ordering field and direction, with defaults from static::$order.
        $order_by  = $args['order']['by']  ?? static::$order['by'];
        $order_dir = $args['order']['dir'] ?? static::$order['dir'];

        // Create a base query with specified ordering.
        $collection = Company::orderBy($order_by, $order_dir);

        // Return paginated data if static::$take is > 0, otherwise retrieve all.
        return CompanyResource::collection($take > 0 ? $collection->paginate($take) : $collection->get());
    }

    /**
     * Retrieve and display a single company by ID or slug.
     *
     * @param int|string $id_or_slug The unique identifier or slug of the company.
     * @return JsonResponse A JSON response containing the company data.
     */
    public function show(int|string $id_or_slug): JsonResponse
    {
        // Find the company by ID or slug, or fail if not found.
        $company = Company::where('id', $id_or_slug)
            ->orWhere('slug', $id_or_slug)
            ->firstOrFail();

        return response()->json(
            data: new CompanyResource($company),
            status: Response::HTTP_OK
        );
    }

    /**
     * Create and store a new company.
     *
     * @param CompanyStoreRequest $request The validated request containing company creation data.
     * @return JsonResponse A JSON response containing the created company's data.
     */
    public function store(CompanyStoreRequest $request): JsonResponse
    {
        // Extract validated data from the request.
        $args = $request->validated();

        try {
            // Use a database transaction to create the company.
            $company = DB::transaction(fn () => Company::create($args), self::DB_ATTEMPTS);
        } catch (\Exception $e) {
            // Return an error response in case of failure.
            return $this->errorResponse($e->getMessage());
        }

        // Return the newly created company resource.
        return response()->json(
            data: new CompanyResource($company),
            status: Response::HTTP_CREATED
        );
    }

    /**
     * Update an existing company.
     *
     * @param Company $company The company instance to be updated.
     * @param CompanyUpdateRequest $request The validated request containing the updated company data.
     * @return JsonResponse A JSON response containing the updated company's data.
     */
    public function update(Company $company, CompanyUpdateRequest $request): JsonResponse
    {
        // Extract validated data from the request.
        $args = $request->validated();

        try {
            // Use a database transaction to update the company.
            DB::transaction(fn () => $company->fill($args)->save(), self::DB_ATTEMPTS);
        } catch (\Exception $e) {
            // Return an error response in case of failure.
            return $this->errorResponse($e->getMessage());
        }

        // Return the updated company resource.
        return response()->json(
            data: new CompanyResource($company),
            status: Response::HTTP_OK
        );
    }

    /**
     * Attach employees to a company.
     *
     * @param Company $company The company instance to which employees will be attached.
     * @param CompanyEmployeesRequest $request The validated request containing the employee IDs to attach.
     * @return JsonResponse A JSON response containing the updated list of employees.
     */
    public function attachEmployees(Company $company, CompanyEmployeesRequest $request): JsonResponse
    {
        // Attach the specified employees without detaching existing ones.
        $company->employees()->sync($request->validated()['list'], false);

        // Return the updated list of employees.
        return response()->json(EmployeeResource::collection($company->employees), Response::HTTP_OK);
    }

    /**
     * Detach employees from a company.
     *
     * @param Company $company The company instance from which employees will be detached.
     * @param CompanyEmployeesRequest $request The validated request containing the employee IDs to detach.
     * @return JsonResponse A JSON response containing the updated list of employees.
     */
    public function detachEmployees(Company $company, CompanyEmployeesRequest $request): JsonResponse
    {
        // Detach the specified employees from the company.
        $company->employees()->detach($request->validated()['list']);

        // Return the updated list of employees.
        return response()->json(status: Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a company.
     *
     * @param Company $company The company instance to delete.
     * @return JsonResponse An empty response with a 204 No Content status.
     */
    public function destroy(Company $company): JsonResponse
    {
        // Delete the company instance.
        $company->delete();

        // Return an empty response with HTTP 204 status.
        return response()->json(status: Response::HTTP_NO_CONTENT);
    }

    /**
     * Generate a standardized error response.
     *
     * @param string $message The error message.
     * @return JsonResponse A JSON response containing the error message.
     */
    private function errorResponse(string $message): JsonResponse
    {
        return response()->json(['error' => $message], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
