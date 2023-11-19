<?php

namespace LaravelDev\Utils;

class IpHelper
{
    /**
     * @return mixed|string|null
     */
    public static function GetIp(): mixed
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            $ip = request()->getClientIp();
        }
        return $ip;
    }
}