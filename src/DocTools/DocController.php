<?php

namespace LaravelDev\DocTools;

use cebe\openapi\exceptions\TypeErrorException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use ReflectionException;

class DocController extends Controller
{
    /**
     * @return JsonResponse
     * @throws ReflectionException
     * @throws TypeErrorException
     */
    public function getOpenApi(): JsonResponse
    {
        return response()->json(DocToolsServices::GenOpenApiV3Doc());
    }
}