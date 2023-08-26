<?php

use Illuminate\Support\Facades\Route;
use LaravelDev\DocTools\DocController;

if (config('common.showDoc')) {
//    Route::middleware(['api'])->prefix('/api/docs')->name('api.docs.')->group(function ($router) {
//        $router->get('openapi', [DocController::class, 'getOpenApi']);
//    });
    Route::middleware(['api'])->prefix('/api/docs')->get('openapi', [DocController::class, 'getOpenApi']);
}
