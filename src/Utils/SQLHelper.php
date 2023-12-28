<?php

namespace LaravelDev\Utils;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SQLHelper
{
    /**
     * 记录sql日志
     * @return void
     */
    public static function logSql(): void
    {
        if (config('app.debug')) {
            DB::listen(function ($query) {
                $tmp = str_replace('?', '"' . '%s' . '"', $query->sql);
                $qBindings = [];
                if (!empty($query->bindings)) {
                    foreach ($query->bindings as $key => $value) {
                        if (is_numeric($key)) {
                            $qBindings[] = $value;
                        } else {
                            $tmp = str_replace(':' . $key, '"' . $value . '"', $tmp);
                        }
                    }
                    $tmp = vsprintf($tmp, $qBindings);
                    $tmp = str_replace("\\", "", $tmp);
                    Log::info(' execution time: ' . $query->time . 'ms; ' . $tmp . "\n\n\t");
                }
            });
        }
    }

    /**
     * Fix unique key is too long
     * @return void
     */
    public static function Schema(): void
    {
        Schema::defaultStringLength(191);
    }
}
