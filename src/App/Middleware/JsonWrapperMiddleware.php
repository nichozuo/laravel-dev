<?php

namespace LaravelDev\App\Middleware;

use Closure;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JsonWrapperMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);
        $base = ['success' => true];
        switch (get_class($response)) {
            case Response::class:
                // 处理未返回内容的情况
                return $response->getContent() == "" ? response()->json($base) : $response;

            case JsonResponse::class:
                $data = $response->getData();
                $type = gettype($data);
                if ($type == 'object') {
                    // exception
                    if (property_exists($data, 'success') && !$data->success) {
                        return $response;
                    }

                    // additions
                    if (property_exists($data, 'statistics')) {
                        $base['statistics'] = $data->statistics;
                        unset($data->statistics);
                    }

                    // pagination
                    if (property_exists($data, 'data') && property_exists($data, 'current_page')) {
                        $base['data'] = $data->data;
                        $base['meta'] = [
                            'total' => $data->total ?? 0,
                            'per_page' => (int)$data->per_page ?? 0,
                            'current_page' => $data->current_page ?? 0,
                            'last_page' => $data->last_page ?? 0
                        ];
                    } else {
                        $base['data'] = $data;
                    }
                } else {
                    if ($data != '' && $data != null) {
                        $base['data'] = $data;
                    }
                }
                return $response->setData($base);

            case BinaryFileResponse::class:
            case StreamedResponse::class:
            case Exception::class:
            default:
                return $response;
        }
    }
}
