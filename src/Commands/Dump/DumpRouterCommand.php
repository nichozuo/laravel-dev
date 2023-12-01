<?php

namespace LaravelDev\Commands\Dump;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use LaravelDev\Services\RouterModelServices;

class DumpRouterCommand extends Command
{
    protected $signature = 'dr {controller}';
    protected $description = 'dump controller';

    /**
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        $name = $this->argument('controller');
        $controllers = RouterModelServices::GenRoutersModels();

        $fullName = Str::of($name)->explode('/')->map(function ($part) {
                return Str::of($part)->camel()->ucfirst();
            })->prepend('App\\Modules')->implode('\\') . "Controller";

        dump($fullName, $controllers[$fullName] ?? null);

        return self::SUCCESS;
    }
}

