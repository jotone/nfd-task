<?php

namespace App\Http\Controllers;

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
        'dir' => 'asc'
    ];
}
