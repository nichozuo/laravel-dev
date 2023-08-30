<?php

namespace LaravelDev\App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ExceptionRender
{
    /**
     * @param Throwable $e
     * @return JsonResponse
     */
    public static function Render(Throwable $e): JsonResponse
    {
        $exceptionClass = get_class($e);
        $request = request();

        list($code, $message, $description, $showType, $httpStatus) = self::getResponseInfo($e, $exceptionClass);

        $isDebug = config('app.debug');

        $debugInfo = [
            'request' => [
                'client' => $request->getClientIps()[0],
                'method' => $request->getMethod(),
                'uri' => $request->getPathInfo(),
                'params' => $request->all(),
            ],
            'exception' => [
                'http_status' => $httpStatus,
                'class' => $exceptionClass,
                'trace' => self::getTrace($e)
            ]
        ];

        $skipLog = in_array($httpStatus, [401, 404, 405]);
        if (!$skipLog)
            Log::error($e->getMessage(), $debugInfo);

        $resp = [
            'success' => false,
            'errorCode' => $code,
            'errorMessage' => $message,
            'showType' => $showType,
        ];

        if ($description)
            $resp['description'] = $description;
        if ($isDebug) {
            $resp['debug'] = $debugInfo;
        }
        return response()->json($resp, 200);
    }

    /**
     * @param Throwable|Err $e
     * @param string $exceptionClass
     * @return array
     */
    private static function getResponseInfo(Throwable|Err $e, string $exceptionClass): array
    {
        $isMyErr = $exceptionClass == Err::class;

        $code = $e->getCode();
        $message = $e->getMessage();
        $description = $isMyErr ? $e->getDescription() : null;
        $showType = $isMyErr ? $e->getShowType() : 2;
        $httpStatus = $isMyErr ? $e->getHttpStatus() : HttpStatusEnum::InternalServerError->value;

        switch ($exceptionClass) {
            case AuthenticationException::class:
                $code = 10000;
                $message = '用户未登录';
                $httpStatus = 401;
                break;
            case MethodNotAllowedHttpException::class:
                $httpStatus = 405;
                break;
            case NotFoundHttpException::class:
                $httpStatus = 404;
                break;
            case ValidationException::class:
                $keys = implode(",", array_keys($e->errors()));
                $message = "您提交的信息不完整：请查看【{$keys}】字段";
                $httpStatus = 400;
                break;
            case ErrConst::class:
            default:
                break;
        }

        return [$code, $message, $description, $showType, $httpStatus];
    }

    /**
     * @param Throwable $e
     * @return array
     */
    private static function getTrace(Throwable $e): array
    {
        $arr = $e->getTrace();
        $file = array_column($arr, 'file');
        $line = array_column($arr, 'line');
        $trace = [];
        for ($i = 0; $i < count($file); $i++) {
            if (!strpos($file[$i], '/vendor/'))
                $trace[] = [
                    $i => "$file[$i]($line[$i])"
                ];
        }
        return $trace;
    }
}
