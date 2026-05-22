<?php

use App\Http\Controllers\Api\VacancyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('vacancies')->group(function () {
    Route::get('/published', [VacancyController::class, 'published']);
    Route::get('/applicants', [VacancyController::class, 'applicants']);
    Route::get('/applications/me', [VacancyController::class, 'myApplications']);
    Route::get('/', [VacancyController::class, 'index']);
    Route::post('/', [VacancyController::class, 'store']);
    Route::get('/{vacancyId}', [VacancyController::class, 'show']);
    Route::put('/{vacancyId}', [VacancyController::class, 'update']);
    Route::post('/{vacancyId}/apply', [VacancyController::class, 'apply']);
    Route::patch('/{vacancyId}/close', [VacancyController::class, 'close']);
});
