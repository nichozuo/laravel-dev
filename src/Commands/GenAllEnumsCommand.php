<?php

namespace LaravelDev\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use LaravelDev\Services\EnumModelServices;
use ReflectionException;

class GenAllEnumsCommand extends Command
{
    protected $signature = 'gae';
    protected $description = 'Gen enums to ts file';

    /**
     * @return void
     * @throws ReflectionException
     */
    public function handle(): void
    {
        $str = EnumModelServices::ToString();
        File::put(storage_path('enums.ts'), $str);
    }
}
