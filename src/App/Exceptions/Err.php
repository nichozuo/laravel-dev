<?php

namespace LaravelDev\App\Exceptions;

use Exception;
use Throwable;

class Err extends Exception
{
    public ?string $description;
    public ?int $showType = 2;
    public ?int $httpStatus = 500;


    /**
     * @param string $message
     * @param int|null $code
     * @param string|null $description
     * @param int|null $showType
     * @param int|null $httpStatus
     * @return Err
     * @throws Err
     */
    public static function Throw(string $message = "", ?int $code = 999, ?string $description = null, ?int $showType = null, ?int $httpStatus = 500): Err
    {
        throw new static($message, $code, $description, $showType, $httpStatus);
    }

    /**
     * @param string $message
     * @param int|null $code
     * @param string|null $description
     * @param int|null $showType
     * @param int|null $httpStatus
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", ?int $code = 999, ?string $description = null, ?int $showType = null, ?int $httpStatus = 500, ?Throwable $previous = null)
    {
        $this->description = $description;
        $this->showType = $showType;
        $this->httpStatus = $httpStatus;
        parent::__construct($message, $code, $previous);
    }

    public function getDescription(): ?string
    {
        return $this->description ?? null;
    }

    public function getShowType(): ?int
    {
        return $this->showType;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }
}