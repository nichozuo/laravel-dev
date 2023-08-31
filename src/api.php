<?php

use Illuminate\Support\Facades\Route;
use LaravelDev\App\Controller\DocController;

if (config('common.showDoc')) {
    Route::middleware(['api'])->prefix('/api/docs')->get('openapi', [DocController::class, 'getOpenApi']);
}
