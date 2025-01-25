<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller
{
    /**
     * Number of the database transactions attempts
     */
    protected const int DB_ATTEMPTS = 5;

    /**
     * Default paginator items per page
     *
     * @var int
     */
    public static int $take = 10;

    /**
     * Model default order query parameters
     *
     * @var array
     */
    public static array $order = [
        'by'  => 'id',
        'dir' => 'asc',
    ];

    /**
     * Extract and process pagination and ordering parameters from the request.
     *
     * This method retrieves the `take` and `order` parameters from the request,
     * ensuring default values are applied if the parameters are missing or invalid.
     * It returns an array containing the number of items per page (`take`),
     * the field to order by (`order_by`), and the sorting direction (`order_dir`).
     *
     * @param FormRequest $request The incoming request instance.
     * @return array<string, mixed> An associative array with keys: 'take', 'order_by', 'order_dir'.
     */
    protected function getIndexRequestParams(FormRequest $request): array
    {
        // Extract 'take' and 'order' parameters from the request.
        $args = $request->only(['take', 'order']);

        // Return an array with the processed parameters.
        return [
            // Determine the number of items per page (default: static::$take).
            'take' => isset($args['take']) && $args['take'] >= 0 ? $args['take'] : static::$take,
            // Define ordering field and direction, with defaults from static::$order.
            'order_by' => $args['order']['by'] ?? static::$order['by'],
            'order_dir' => $args['order']['dir'] ?? static::$order['dir'],
        ];
    }

    /**
     * Generate a standardized error response.
     *
     * @param \Exception $exception The error instance
     * @return JsonResponse A JSON response containing the error message.
     */
    protected function errorResponse(\Exception $exception): JsonResponse
    {
        Log::error($exception->getMessage(), $exception->getTrace());
        return response()->json(['error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
