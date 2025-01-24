<?php

use App\Http\Controllers\{CompanyController, EmployeeController};
use Illuminate\Support\Facades\Route;

Route::group(['as' => 'companies.', 'prefix' => 'companies'], function () {
    Route::patch('/{company}/attach', [CompanyController::class, 'attachEmployees'])->name('attach');
    Route::delete('/{company}/detach', [CompanyController::class, 'detachEmployees'])->name('detach');
});
Route::apiResource('/companies', CompanyController::class);

Route::apiResource('/employees', EmployeeController::class);
