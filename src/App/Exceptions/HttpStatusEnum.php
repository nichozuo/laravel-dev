<?php

namespace LaravelDev\App\Exceptions;

enum HttpStatusEnum: int
{
    case OK = 200;
    case BadRequest = 400;
    case Unauthorized = 401;
    case Forbidden = 403;
    case NotFound = 404;
    case MethodNotAllowed = 405;
    case InternalServerError = 500;
    case ServiceUnavailable = 503;
}