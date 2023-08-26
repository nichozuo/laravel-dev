<?php

namespace LaravelDev\App\Exceptions;

enum ErrTypeEnum: int
{
    case SILENT = 0;
    case WARN_MESSAGE = 1;
    case ERROR_MESSAGE = 2;
    case NOTIFICATION = 3;
    case REDIRECT = 9;
}