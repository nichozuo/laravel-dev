<?php

namespace LaravelDev\App\Libraries;


use App\Models\Payments;
use Vinkla\Hashids\Facades\Hashids;

class HashidsHelper
{
    /**
     * @param mixed $obj
     * @return string
     */
    public static function Encode(mixed $obj): string
    {
        list($prefix, $connection) = self::getPrefix($obj);
        return $prefix . Hashids::connection($connection)->encode($obj->id);
    }

    /**
     * @param mixed $obj
     * @param string $code
     * @return int
     */
    public static function Decode(mixed $obj, string $code): int
    {
        list($prefix, $connection) = self::getPrefix($obj);
        return Hashids::connection($connection)->decode(substr($code, strlen($prefix)))[0];
    }

    /**
     * @param mixed $obj
     * @return string[]
     */
    private static function getPrefix(mixed $obj): array
    {
        return match (get_class($obj)) {
            Payments::class => ['PM', 'Payments'],
        };
    }
}