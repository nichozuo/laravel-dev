<?php

namespace LaravelDev\App\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use LaravelDev\Services\DocToolsServices;
use ReflectionException;

class DocController extends Controller
{
    /**
     * @return JsonResponse
     * @throws ReflectionException
     */
    public function getOpenApi(): JsonResponse
    {
        return response()->json(DocToolsServices::GenOpenApiV3Doc());
    }
}