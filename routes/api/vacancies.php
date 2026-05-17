<?php

use App\Http\Controllers\Api\VacancyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('vacancies')->group(function () {
    Route::post('/', [VacancyController::class, 'store']);
});
