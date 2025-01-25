<?php

use App\Http\Controllers\{CompanyController, EmployeeController};
use Illuminate\Support\Facades\Route;

Route::group(['as' => 'companies.', 'prefix' => 'companies'], function () {
    Route::patch('/{company}/attach-employees', [CompanyController::class, 'attachEmployees'])->name('attach-employees');
    Route::delete('/{company}/detach-employees', [CompanyController::class, 'detachEmployees'])->name('detach-employees');
});
Route::apiResource('/companies', CompanyController::class);

Route::apiResource('/employees', EmployeeController::class);
