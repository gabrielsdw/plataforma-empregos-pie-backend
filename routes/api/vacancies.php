<?php

use App\Http\Controllers\Api\VacancyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('vacancies')->group(function () {
    Route::get('/published', [VacancyController::class, 'published']);
    Route::get('/', [VacancyController::class, 'index']);
    Route::post('/', [VacancyController::class, 'store']);
});
