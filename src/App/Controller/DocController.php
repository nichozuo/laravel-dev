<?php

namespace LaravelDev\App\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use LaravelDev\App\Exceptions\Err;
use LaravelDev\Services\DocToolsServices;
use ReflectionException;

class DocController extends Controller
{
    /**
     * @return JsonResponse
     * @throws ReflectionException
     * @throws Err
     */
    public function getOpenApi(): JsonResponse
    {
        return response()->json(DocToolsServices::GenOpenApiV3Doc());
    }
}